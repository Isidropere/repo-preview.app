<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImagenItem;
use App\Models\User;
use App\Services\ImageModerationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('admin.imagenes.index', compact('imagenesItems', 'fotosUsuarios'));
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
}
