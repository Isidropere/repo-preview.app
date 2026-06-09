<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\NuevaNotificacion;
use App\Models\Message;
use App\Models\User;
use App\Models\Item;
use App\Models\PagoCompra;
use App\Models\CategoriaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

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

    public function indexCategorias(Request $request)
    {
        $categorias = CategoriaItem::orderBy('categoria')->get();
        $idCategoria = $request->get('id_categoria_item');

        $query = Item::with(['usuario', 'inventarios', 'direccionPredeterminada.provincia', 'direccionPredeterminada.municipio']);

        if ($idCategoria) {
            $query->where('id_categoria_item', $idCategoria);
        }

        $items = $query->paginate(20)->withQueryString();

        return view('admin.notificaciones_categorias', compact('categorias', 'items', 'idCategoria'));
    }

    public function enviarDirecta(Request $request)
    {
        Log::info('AdminNotificaciones::enviarDirecta', $request->except('_token'));

        $rules = [
            'usuario_id' => 'required|integer|exists:users,id',
            'mensaje'    => 'required|string|max:500',
            'canales'    => 'required|array|min:1',
            'canales.*'  => 'string|in:web,email',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'usuario_id.required' => 'Debes seleccionar un usuario.',
            'mensaje.required'    => 'El mensaje es obligatorio.',
            'canales.required'    => 'Selecciona al menos una vía de envío.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $this->enviarNotificacionAUser($request->usuario_id, $request->mensaje, $request->canales);
        } catch (\Throwable $e) {
            Log::error('Error sending direct notification', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }

        return back()->with('success', 'Notificación enviada correctamente.');
    }

    public function enviar(Request $request)
    {
        Log::info('AdminNotificaciones::enviar', $request->except('_token'));

        $rules = [
            'tipos'     => 'required|array|min:1',
            'tipos.*'   => 'string|in:' . implode(',', array_keys(self::TIPOS)),
            'destino'   => 'required|string|in:usuario,todos_vendedores,todos_compradores,todos',
            'mensaje'   => 'required|string|max:500',
            'canales'   => 'nullable|array',
            'canales.*' => 'string|in:web,email',
        ];

        if ($request->destino === 'usuario') {
            $rules['usuario_id'] = 'required|integer|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'tipos.required'      => 'Selecciona al menos un tipo de notificacion.',
            'usuario_id.required' => 'Debes seleccionar un usuario.',
            'mensaje.required'    => 'El mensaje es obligatorio.',
        ]);

        $redirectTo = $request->get('redirect_to', '/admin/notificaciones');

        if ($validator->fails()) {
            return redirect($redirectTo)
                ->withErrors($validator)
                ->withInput();
        }

        $prefijos = array_map(fn($t) => self::TIPOS[$t] ?? '', $request->tipos);
        $textoFinal = implode(' ', $prefijos) . ' ' . $request->mensaje;

        $enviados = 0;
        $canales = $request->get('canales', ['web']);

        try {
            if ($request->destino === 'usuario') {
                $this->enviarNotificacionAUser($request->usuario_id, $textoFinal, $canales);
                $enviados = 1;
            } elseif ($request->destino === 'todos_vendedores') {
                $ids = Item::where('estatus', 1)->distinct()->pluck('id_user');
                foreach ($ids as $uid) { $this->enviarNotificacionAUser($uid, $textoFinal, $canales); $enviados++; }
            } elseif ($request->destino === 'todos_compradores') {
                $ids = PagoCompra::with('carrito')->get()->pluck('carrito.id_user')->filter()->unique();
                foreach ($ids as $uid) { $this->enviarNotificacionAUser($uid, $textoFinal, $canales); $enviados++; }
            } elseif ($request->destino === 'todos') {
                $ids = User::where('estatus', 1)->pluck('id');
                foreach ($ids as $uid) { $this->enviarNotificacionAUser($uid, $textoFinal, $canales); $enviados++; }
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando notificaciones', ['error' => $e->getMessage()]);
            return redirect($redirectTo)->with('error', 'Error: ' . $e->getMessage());
        }

        return redirect($redirectTo)->with('success', "Notificacion enviada a {$enviados} usuario(s).");
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

    private function enviarNotificacionAUser(int $userId, string $mensaje, array $canales = ['web']): void
    {
        $usuario = User::find($userId);
        if (!$usuario) return;

        if (in_array('web', $canales)) {
            $this->notificar($userId, $mensaje);
        }

        if (in_array('email', $canales) && $usuario->email) {
            try {
                Mail::raw($mensaje, function ($message) use ($usuario) {
                    $message->to($usuario->email)
                            ->subject('Notificación de Cambialord');
                });
            } catch (\Throwable $e) {
                Log::error("Error enviando correo a usuario {$userId}", ['error' => $e->getMessage()]);
            }
        }
    }
}
