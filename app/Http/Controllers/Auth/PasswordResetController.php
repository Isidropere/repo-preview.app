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

        $email = strtolower(trim($request->email));
        $dailyKey = 'password-reset-daily:' . $email;
        $ipKey = 'password-reset-daily-ip:' . $request->ip();
        $minuteKey = 'password-reset-min:' . $email;

        // Check if daily limit (3 requests per 24h) exceeded for this email or IP
        if (RateLimiter::tooManyAttempts($dailyKey, 3) || RateLimiter::tooManyAttempts($ipKey, 6)) {
            $errorMessage = 'Has alcanzado el límite de 3 solicitudes de restablecimiento al día. Por seguridad de tu cuenta, intenta de nuevo mañana.';

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 429);
            }

            return back()->withErrors(['email' => $errorMessage]);
        }

        // Check minute limit (1 per 60 seconds)
        if (RateLimiter::tooManyAttempts($minuteKey, 1)) {
            $seconds = RateLimiter::availableIn($minuteKey);
            $errorMessage = 'Por favor espera ' . $seconds . ' segundos antes de solicitar otro enlace.';

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 429);
            }

            return back()->withErrors(['email' => $errorMessage]);
        }

        // Send the reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // If the reset link was sent successfully, increment rate limiters
        if ($status === Password::RESET_LINK_SENT) {
            RateLimiter::hit($dailyKey, 86400); // 24 hours
            RateLimiter::hit($ipKey, 86400);    // 24 hours
            RateLimiter::hit($minuteKey, 60);    // 1 minute

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
        ]);
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
