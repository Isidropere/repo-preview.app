<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\NuevaNotificacion;
use App\Models\Message;
use App\Models\User;
use App\Models\Item;
use App\Models\PagoCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminNotificacionesController extends Controller
{
    const TIPOS = [
        'venta'        => '[Venta]',
        'compra'       => '[Compra]',
        'intercambio'  => '[Intercambio]',
        'producto'     => '[Producto]',
        'servicio'     => '[Servicio]',
        'general'      => '[General]',
    ];

    public function index()
    {
        return view('admin.notificaciones');
    }

    public function enviar(Request $request)
    {
        Log::info('AdminNotificaciones::enviar', $request->except('_token'));

        $rules = [
            'tipos'   => 'required|array|min:1',
            'tipos.*' => 'string|in:' . implode(',', array_keys(self::TIPOS)),
            'destino' => 'required|string|in:usuario,todos_vendedores,todos_compradores,todos',
            'mensaje' => 'required|string|max:500',
        ];

        if ($request->destino === 'usuario') {
            $rules['usuario_id'] = 'required|integer|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'tipos.required'      => 'Selecciona al menos un tipo de notificacion.',
            'usuario_id.required' => 'Debes seleccionar un usuario.',
            'mensaje.required'    => 'El mensaje es obligatorio.',
        ]);

        if ($validator->fails()) {
            return redirect('/admin/notificaciones')
                ->withErrors($validator)
                ->withInput();
        }

        $prefijos = array_map(fn($t) => self::TIPOS[$t] ?? '', $request->tipos);
        $textoFinal = implode(' ', $prefijos) . ' ' . $request->mensaje;

        $enviados = 0;

        try {
            if ($request->destino === 'usuario') {
                $this->notificar($request->usuario_id, $textoFinal);
                $enviados = 1;
            } elseif ($request->destino === 'todos_vendedores') {
                $ids = Item::where('estatus', 1)->distinct()->pluck('id_user');
                foreach ($ids as $uid) { $this->notificar($uid, $textoFinal); $enviados++; }
            } elseif ($request->destino === 'todos_compradores') {
                $ids = PagoCompra::with('carrito')->get()->pluck('carrito.id_user')->filter()->unique();
                foreach ($ids as $uid) { $this->notificar($uid, $textoFinal); $enviados++; }
            } elseif ($request->destino === 'todos') {
                $ids = User::where('estatus', 1)->pluck('id');
                foreach ($ids as $uid) { $this->notificar($uid, $textoFinal); $enviados++; }
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando notificaciones', ['error' => $e->getMessage()]);
            return redirect('/admin/notificaciones')->with('error', 'Error: ' . $e->getMessage());
        }

        return redirect('/admin/notificaciones')->with('success', "Notificacion enviada a {$enviados} usuario(s).");
    }

    public function buscarUsuarios(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) return response()->json([]);

        return User::where('estatus', 1)
            ->where(fn($query) => $query
                ->where('nombres', 'like', "%{$q}%")
                ->orWhere('apellidos', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%"))
            ->limit(10)
            ->get(['id', 'nombres', 'apellidos', 'email']);
    }

    private function notificar(int $userId, string $mensaje): void
    {
        Message::create([
            'id_emisor'   => null,
            'id_receptor' => $userId,
            'mensaje'     => $mensaje,
            'leido'       => false,
        ]);
        event(new NuevaNotificacion($mensaje, $userId));
    }
}
