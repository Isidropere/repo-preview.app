<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImagenItem;
use App\Models\User;
use App\Services\ImageModerationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\CategoriaItem;
use App\Models\Message;
use App\Models\Notificacion;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminImagenesController extends Controller
{
    public function __construct(private ImageModerationService $service) {}

    public function index(): View
    {
        $imagenesItems = ImagenItem::with('item:id_item,item,id_user')
            ->where('estado', 'pendiente')
            ->orderBy('id_imagen', 'asc')
            ->get();

        $fotosUsuarios = User::where(function ($q) {
                $q->whereNotNull('foto_perfil')->orWhereNotNull('profile_photo_path');
            })
            ->where('foto_perfil_estado', 'pendiente')
            ->orderBy('id', 'asc')
            ->get();

        $productosPendientes = Item::with(['usuario', 'categoria'])
            ->where('estado_aprobacion', 'pendiente')
            ->orderBy('id_item', 'desc')
            ->get();

        $categorias = CategoriaItem::orderBy('categoria')->get();

        return view('admin.imagenes.index', compact('imagenesItems', 'fotosUsuarios', 'productosPendientes', 'categorias'));
    }

    public function aprobarItem(Request $request, int $id): RedirectResponse
    {
        try {
            $this->service->aprobarImagenItem($id);
            return back()->with('success', 'Imagen aprobada correctamente.');
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    public function rechazarItem(Request $request, int $id): RedirectResponse
    {
        $request->validate(['motivo_rechazo' => 'required|string|min:1']);

        try {
            $this->service->rechazarImagenItem($id, $request->motivo_rechazo);
            return back()->with('success', 'Imagen rechazada.');
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    public function aprobarPerfil(Request $request, int $id): RedirectResponse
    {
        try {
            $this->service->aprobarFotoPerfil($id);
            return back()->with('success', 'Foto de perfil aprobada.');
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    public function rechazarPerfil(Request $request, int $id): RedirectResponse
    {
        $request->validate(['motivo_rechazo' => 'required|string|min:1']);

        try {
            $this->service->rechazarFotoPerfil($id, $request->motivo_rechazo);
            return back()->with('success', 'Foto de perfil rechazada.');
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    public function aprobarTodosItems(Request $request): RedirectResponse
    {
        $count = $this->service->aprobarTodasImagenesItem();
        return back()->with('success', "Se aprobaron {$count} imagen(es) de artículos.");
    }

    public function aprobarTodosPerfiles(Request $request): RedirectResponse
    {
        $count = $this->service->aprobarTodasFotosPerfil();
        return back()->with('success', "Se aprobaron {$count} foto(s) de perfil.");
    }

    public function aprobarProducto(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'id_categoria_item' => 'required|exists:categorias_item,id_categoria_item',
            'peso_lbs'          => 'required|numeric|min:0',
            'alto_cm'           => 'required|numeric|min:0',
            'ancho_cm'          => 'required|numeric|min:0',
            'profundo_cm'       => 'required|numeric|min:0',
            'presentacion'      => 'required|string'
        ]);

        try {
            $item = Item::findOrFail($id);
            $item->id_categoria_item = $request->id_categoria_item;
            $item->peso_lbs = $request->peso_lbs;
            $item->alto_cm = $request->alto_cm;
            $item->ancho_cm = $request->ancho_cm;
            $item->profundo_cm = $request->profundo_cm;
            $item->presentacion = $request->presentacion;
            $item->estado_aprobacion = 'aprobado';
            $item->estatus = 1;
            $item->save();

            // Notificar al dueño
            $mensaje = "Tu producto '{$item->item}' ha sido aprobado y ya está visible en la plataforma.";
            Message::create([
                'id_receptor' => $item->id_user,
                'mensaje' => $mensaje,
                'leido' => false,
            ]);

            return back()->with('success', 'Producto aprobado correctamente.');
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function rechazarProducto(Request $request, int $id): RedirectResponse
    {
        $request->validate(['motivo_rechazo' => 'required|string|min:1']);

        try {
            $item = Item::findOrFail($id);
            $item->estado_aprobacion = 'rechazado';
            $item->estatus = 0;
            $item->save();

            // Notificar al dueño
            $mensaje = "Tu producto '{$item->item}' ha sido rechazado. Motivo: {$request->motivo_rechazo}";
            Message::create([
                'id_receptor' => $item->id_user,
                'mensaje' => $mensaje,
                'leido' => false,
            ]);

            return back()->with('success', 'Producto rechazado.');
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }
}
