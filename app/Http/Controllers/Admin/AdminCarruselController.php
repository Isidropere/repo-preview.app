<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeCarousel;
use App\Models\HomeCarouselConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

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
        $request->validate([
            'imagen'    => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:10240',
            'titulo'    => 'nullable|string|max:255',
            'subtitulo' => 'nullable|string|max:255',
            'enlace'    => 'nullable|string|max:500',
            'orden'     => 'nullable|integer|min:0',
        ], [
            'imagen.required' => 'Debes seleccionar una imagen para el carrusel.',
            'imagen.image'    => 'El archivo seleccionado debe ser una imagen válida.',
            'imagen.max'      => 'La imagen no debe pesar más de 10 MB.',
        ]);

        $path = $request->file('imagen')->store('carrusel', 'public');

        HomeCarousel::create([
            'imagen'    => $path,
            'titulo'    => $request->titulo,
            'subtitulo' => $request->subtitulo,
            'enlace'    => $request->enlace,
            'orden'     => $request->orden ?? (HomeCarousel::max('orden') + 1),
            'activo'    => $request->has('activo') ? true : true,
        ]);

        $this->limpiarCache();

        return back()->with('success', '¡Imagen agregada al carrusel exitosamente!');
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
}
