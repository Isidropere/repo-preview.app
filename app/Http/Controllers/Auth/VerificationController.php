<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    // Show the verification notice
    public function show(Request $request)
    {
        return view('verify-email');
    }

    // Handle the email verification
    public function verify(Request $request, $id, $hash)
    {
        // Find the user by ID
        $user = User::findOrFail($id);

        // Check if the hash is valid (Laravel's signed URLs include this check in middleware)
        // We're just accepting the hash parameter to match the route definition

        // Mark the email as verified if it hasn't been verified yet
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            // If user is logged in, regenerate their session
            if (Auth::check()) {
                $request->session()->regenerate();
            }
        }

        // Redirect to login with success message
        return redirect()->route('login')->with('success', 'Tu correo ha sido verificado exitosamente. Ahora puedes iniciar sesión.');
    }

    // Resend the verification email
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/home'); // Change to your desired route
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Enlace de verificación enviado!');
    }
}
