<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

class PasswordResetController extends Controller
{
    // Show the password reset request form
    public function request()
    {
        return view('password_email');
    }

    // Handle sending the reset link email
    public function email(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Create a key for rate limiting based on the email
        $key = 'password-reset:' . $request->email;

        // Check if the user has exceeded the rate limit (1 request per minute)
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            $errorMessage = 'Has solicitado demasiados enlaces de restablecimiento de contraseña. Por favor, espera ' . $seconds . ' segundos antes de intentarlo de nuevo.';

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ]);
            }

            return back()->withErrors([
                'email' => $errorMessage
            ]);
        }

        // Send the reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // If the reset link was sent successfully, increment the rate limiter
        if ($status === Password::RESET_LINK_SENT) {
            // Increment the rate limiter with a decay time of 60 seconds (1 minute)
            RateLimiter::hit($key, 60);

            $successMessage = 'Se ha enviado un link a su correo para cambiar su contraseña.';

            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage
                ]);
            }

            // For non-AJAX requests, continue with the current behavior
            // Check if user is authenticated
            if (auth()->check()) {
                // Redirect to micuenta with success message
                return redirect()->route('micuenta')->with([
                    'status' => $successMessage
                ]);
            } else {
                // Redirect to login with success message
                return redirect()->route('login')->with([
                    'status' => $successMessage
                ]);
            }
        }

        // If there was an error, return the appropriate response
        $errorMessage = __($status);

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ]);
        }

        return back()->withErrors(['email' => $errorMessage]);
    }

    // Show the password reset form
    public function reset(Request $request, $token = null)
    {
        $email = $request->input('email');
        if (!$email) {
            return redirect()->route('password.request')->withErrors(['email' => 'Se requiere un correo valido.']);
        }
        return view('password_reset', [
            'token' => $token,
            'email' => $request->email,
        ])->withErrors(['email' => 'The email address is required.']);

    }

    // Handle the password reset
    public function update(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = $password;
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
