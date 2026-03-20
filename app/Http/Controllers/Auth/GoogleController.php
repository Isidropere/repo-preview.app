<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            // Separar nombre y apellido del nombre completo de Google
            $nameParts = explode(' ', $googleUser->getName(), 2);
            $nombres   = $nameParts[0] ?? $googleUser->getName();
            $apellidos = $nameParts[1] ?? '';

            $user = User::create([
                'nombres'         => $nombres,
                'apellidos'       => $apellidos,
                'email'           => $googleUser->getEmail(),
                'google_id'       => $googleUser->getId(),
                'nombre_usuario'  => strtolower(preg_replace('/\s+/', '', $googleUser->getName())) . '_' . Str::random(4),
                'password'        => bcrypt(Str::random(16)),
                'estatus'         => 1,
                'id_tipo_usuario' => 1,
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended('/home'); // Redirect to intended page after login
    }
}
