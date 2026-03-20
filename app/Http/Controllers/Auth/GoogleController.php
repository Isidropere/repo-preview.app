<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $user = User::create([
                'nombres' => $googleUser->getName(),
                'apellidos' => '', // You may want to extract this from the Google user data if available
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => bcrypt(str_random(16)), // Generate a random password
                'status' => 'active',
                'id_tipo_usuario' => 'default', // Set a default user type
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended('/home'); // Redirect to intended page after login
    }
}
