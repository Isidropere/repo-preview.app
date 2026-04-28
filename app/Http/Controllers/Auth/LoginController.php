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
}
