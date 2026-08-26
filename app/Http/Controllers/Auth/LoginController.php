<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\Auth\Route;
//use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        // Si ya está autenticado, redirigir directamente
        if (Auth::check()) {
            return redirect()->route('home');
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Verifica si el usuario existe
        if (!$user) {
            return back()->withErrors([
                'credentials' => 'El correo no está registrado.',
            ])->withInput();
        }

        // Verificación de email desactivada en local — activar en producción
        // if (!$user->hasVerifiedEmail()) {
        //     return back()->withErrors([
        //         'credentials' => 'Verifica tu correo electrónico primero. Revisa tu bandeja de entrada.',
        //     ])->withInput();
        // }

        // Intento de autenticación
        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();

            session()->flash('gtag_event', ['name' => 'login_success', 'params' => ['method' => 'manual', 'platform' => 'web']]);

            // Verificar inventario de productos/servicios del usuario
            $this->verificarInventarioUsuario(Auth::user());

            return redirect()->route('home')->with('success', 'Bienvenido, ' . Auth::user()->nombres);
            // Verifica si la ruta 'home' existe antes de usarla
            //if (Route::has('home')) {
            //    return redirect()->intended(route('home'))->with('success', 'Usuario Logueado correctamente.');
            //} else {
            //    return redirect()->intended('/')->with('error', 'La ruta home no está definida.');
            //}
        }

        return back()->withErrors([
            'credentials' => 'Contraseña incorrecta.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }

    /**
     * Verifica inventario de items del usuario y envía notificaciones si hay stock bajo o agotado.
     */
    private function verificarInventarioUsuario(User $user): void
    {
        try {
            $items = \App\Models\Item::where('id_user', $user->id)
                ->where('estatus', 1)
                ->with('inventarios')
                ->get();

            if ($items->isEmpty()) {
                return; // No tiene items publicados, no notificar
            }

            $agotados = [];
            $stockBajo = [];

            foreach ($items as $item) {
                $cantidad = $item->inventarios?->cantidad ?? 0;
                $tipo = (int) $item->id_categoria_item === 29 ? 'servicio' : 'producto';

                if ($cantidad <= 0) {
                    $agotados[] = $item->item . " ({$tipo})";
                } elseif ($cantidad === 1) {
                    $stockBajo[] = $item->item . " ({$tipo}, queda 1)";
                }
            }

            // Notificar stock agotado
            if (!empty($agotados)) {
                $msg = '[Producto] Stock agotado: ' . implode(', ', array_slice($agotados, 0, 5));
                if (count($agotados) > 5) $msg .= ' y ' . (count($agotados) - 5) . ' mas';
                $this->enviarNotificacionInventario($user->id, $msg);
            }

            // Notificar stock bajo
            if (!empty($stockBajo)) {
                $msg = '[Producto] Stock bajo: ' . implode(', ', array_slice($stockBajo, 0, 5));
                if (count($stockBajo) > 5) $msg .= ' y ' . (count($stockBajo) - 5) . ' mas';
                $this->enviarNotificacionInventario($user->id, $msg);
            }
        } catch (\Throwable $e) {
            \Log::warning('Error verificando inventario al login', ['error' => $e->getMessage()]);
        }
    }

    private function enviarNotificacionInventario(int $userId, string $mensaje): void
    {
        // Evitar duplicados: no enviar si ya existe una notificación igual en las últimas 24h
        $existe = \App\Models\Message::whereNull('id_emisor')
            ->where('id_receptor', $userId)
            ->where('mensaje', $mensaje)
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        if ($existe) return;

        \App\Models\Message::create([
            'id_emisor'   => null,
            'id_receptor' => $userId,
            'mensaje'     => $mensaje,
            'leido'       => false,
        ]);
    }
    public function verificarCredenciales(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Verificar si las credenciales coinciden con las del usuario autenticado
        if ($request->email !== auth()->user()->email) {
            return response()->json([
                'success' => false,
                'message' => 'El correo electrónico no coincide con tu sesión actual.'
            ], 422);
        }

        if (Auth::guard('web')->validate($request->only('email', 'password'))) {
            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Contraseña incorrecta.'
        ], 422);
    }
}
