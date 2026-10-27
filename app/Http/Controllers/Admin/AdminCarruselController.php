<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeCarousel;
use App\Models\HomeCarouselConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AdminCarruselController extends Controller
{
    /**
     * Muestra la vista de administración del carrusel de la página de inicio.
     */
    public function index()
    {
        $slides = HomeCarousel::orderBy('orden', 'asc')->orderBy('id', 'asc')->get();
        $velocidadSegundos = (int) HomeCarouselConfig::get('velocidad_segundos', 4);
        $isAutoplay = (bool) HomeCarouselConfig::get('is_autoplay', 1);

        return view('admin.carrusel.index', compact('slides', 'velocidadSegundos', 'isAutoplay'));
    }

    /**
     * Sube y registra una nueva imagen para el carrusel.
     */
    public function store(Request $request)
    {
        $hasCropped = $request->filled('imagen_recortada_base64');

        $request->validate([
            'imagen'                   => $hasCropped ? 'nullable' : 'required|image|mimes:jpeg,png,jpg,webp,svg|max:10240',
            'imagen_recortada_base64' => 'nullable|string',
            'titulo'                   => 'nullable|string|max:255',
            'subtitulo'                => 'nullable|string|max:255',
            'enlace'                   => 'nullable|string|max:500',
            'orden'                    => 'nullable|integer|min:0',
        ], [
            'imagen.required' => 'Debes seleccionar una imagen para el carrusel.',
            'imagen.image'    => 'El archivo seleccionado debe ser una imagen válida.',
            'imagen.max'      => 'La imagen no debe pesar más de 10 MB.',
        ]);

        $path = null;

        // 1. Si viene imagen recortada y ajustada desde el modal en base64
        if ($request->filled('imagen_recortada_base64')) {
            $base64 = $request->input('imagen_recortada_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $base64Data = substr($base64, strpos($base64, ',') + 1);
                $decoded = base64_decode($base64Data);
                if ($decoded !== false) {
                    $ext = strtolower($type[1]);
                    if ($ext === 'jpeg') $ext = 'jpg';
                    $fileName = 'carrusel_' . time() . '_' . Str::random(10) . '.' . $ext;
                    $path = 'carrusel/' . $fileName;
                    Storage::disk('public')->put($path, $decoded);
                }
            }
        }

        // 2. Si no vino base64, formatear y guardar el archivo subido
        if (!$path && $request->hasFile('imagen')) {
            $path = $this->formatearYGuardarImagen($request->file('imagen'));
        }

        if (!$path) {
            return back()->withErrors(['imagen' => 'No se pudo procesar la imagen seleccionada.'])->withInput();
        }

        HomeCarousel::create([
            'imagen'    => $path,
            'titulo'    => $request->titulo,
            'subtitulo' => $request->subtitulo,
            'enlace'    => $request->enlace,
            'orden'     => $request->orden ?? ((HomeCarousel::max('orden') ?? 0) + 1),
            'activo'    => true,
        ]);

        $this->limpiarCache();

        return back()->with('success', '¡Imagen formateada y agregada al carrusel exitosamente!');
    }

    /**
     * Actualiza la velocidad de transición y reproducción automática del carrusel.
     */
    public function updateVelocidad(Request $request)
    {
        $request->validate([
            'velocidad_segundos' => 'required|numeric|min:1|max:60',
        ], [
            'velocidad_segundos.required' => 'La velocidad en segundos es obligatoria.',
            'velocidad_segundos.min'      => 'La velocidad mínima es de 1 segundo.',
            'velocidad_segundos.max'      => 'La velocidad máxima es de 60 segundos.',
        ]);

        HomeCarouselConfig::set('velocidad_segundos', $request->velocidad_segundos);
        HomeCarouselConfig::set('is_autoplay', $request->has('is_autoplay') ? '1' : '0');

        $this->limpiarCache();

        return back()->with('success', '¡Configuración de velocidad del carrusel actualizada correctamente!');
    }

    /**
     * Alterna el estado activo / inactivo de una diapositiva.
     */
    public function toggle($id, Request $request)
    {
        $slide = HomeCarousel::findOrFail($id);
        $slide->activo = !$slide->activo;
        $slide->save();

        $this->limpiarCache();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'activo'  => $slide->activo,
                'message' => $slide->activo ? 'Diapositiva activada' : 'Diapositiva desactivada'
            ]);
        }

        return back()->with('success', 'Estado de la diapositiva actualizado.');
    }

    /**
     * Actualiza el orden o textos de una diapositiva existente.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo'    => 'nullable|string|max:255',
            'subtitulo' => 'nullable|string|max:255',
            'enlace'    => 'nullable|string|max:500',
            'orden'     => 'required|integer|min:0',
        ]);

        $slide = HomeCarousel::findOrFail($id);
        $slide->update([
            'titulo'    => $request->titulo,
            'subtitulo' => $request->subtitulo,
            'enlace'    => $request->enlace,
            'orden'     => $request->orden,
        ]);

        $this->limpiarCache();

        return back()->with('success', 'Diapositiva actualizada correctamente.');
    }

    /**
     * Elimina una diapositiva y su archivo físico si fue subido a storage.
     */
    public function destroy($id)
    {
        $slide = HomeCarousel::findOrFail($id);

        // Si no es una imagen por defecto del sistema (/imgs/...), borrar archivo de storage
        if (!str_starts_with($slide->imagen, '/imgs/') && !str_starts_with($slide->imagen, 'http')) {
            Storage::disk('public')->delete($slide->imagen);
        }

        $slide->delete();
        $this->limpiarCache();

        return back()->with('success', 'Imagen eliminada del carrusel.');
    }

    /**
     * Limpia la caché del home para reflejar los cambios de inmediato.
     */
    private function limpiarCache()
    {
        Cache::forget('home_carousel_slides');
        Cache::forget('home_carousel_speed');
        Cache::forget('home_carousel_autoplay');
    }

    /**
     * Formatea, recorta proporcionalmente y optimiza la imagen a 1200 x 530 px usando GD.
     */
    private function formatearYGuardarImagen($file): string
    {
        $targetWidth = 1200;
        $targetHeight = 530;

        $tempPath = $file->getRealPath();
        $mime = $file->getMimeType();

        // Si es SVG, se almacena directamente
        if ($mime === 'image/svg+xml') {
            return $file->store('carrusel', 'public');
        }

        if (function_exists('imagecreatefromstring') && function_exists('imagecreatetruecolor')) {
            try {
                $rawContent = file_get_contents($tempPath);
                $srcImg = @imagecreatefromstring($rawContent);

                if ($srcImg !== false) {
                    $srcW = imagesx($srcImg);
                    $srcH = imagesy($srcImg);

                    $targetRatio = $targetWidth / $targetHeight;
                    $srcRatio = $srcW / $srcH;

                    if ($srcRatio > $targetRatio) {
                        // Imagen más ancha: recorte proporcional centrado horizontal
                        $cropH = $srcH;
                        $cropW = (int) round($srcH * $targetRatio);
                        $cropX = (int) round(($srcW - $cropW) / 2);
                        $cropY = 0;
                    } else {
                        // Imagen más alta: recorte proporcional centrado vertical
                        $cropW = $srcW;
                        $cropH = (int) round($srcW / $targetRatio);
                        $cropX = 0;
                        $cropY = (int) round(($srcH - $cropH) / 2);
                    }

                    $destImg = imagecreatetruecolor($targetWidth, $targetHeight);

                    // Preservar canal alfa para transparencias
                    imagealphablending($destImg, false);
                    imagesavealpha($destImg, true);

                    imagecopyresampled(
                        $destImg, $srcImg,
                        0, 0, $cropX, $cropY,
                        $targetWidth, $targetHeight, $cropW, $cropH
                    );

                    imagedestroy($srcImg);

                    ob_start();
                    if (function_exists('imagewebp')) {
                        imagewebp($destImg, null, 90);
                        $ext = 'webp';
                    } else {
                        imagejpeg($destImg, null, 90);
                        $ext = 'jpg';
                    }
                    $formattedContent = ob_get_clean();
                    imagedestroy($destImg);

                    if ($formattedContent) {
                        $fileName = 'carrusel_' . time() . '_' . Str::random(10) . '.' . $ext;
                        $path = 'carrusel/' . $fileName;
                        Storage::disk('public')->put($path, $formattedContent);
                        return $path;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[AdminCarrusel] Falló el formateo automático GD, guardando original: ' . $e->getMessage());
            }
        }

        return $file->store('carrusel', 'public');
    }
}
