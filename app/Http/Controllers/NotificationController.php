<?php

namespace App\Http\Controllers;

use App\Logging\ErrorLoggerTrait;
use App\Models\User;
use App\Notifications\ApiNotification;
use App\Models\PredefinedMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Models\Message;

class NotificationController extends Controller
{
    use ErrorLoggerTrait;

    public function index()
    {
        try {
            $user = Auth::user();
            return response()->json($user->notifications);
        } catch (Throwable $e) {
            $this->logError($e, ['method' => 'index']);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function listar()
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            // Solo notificaciones NO leídas
            $mensajes = Message::where('id_receptor', $userId)
                ->where('leido', 0)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'mensajes' => $mensajes,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en listar()', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
            ]);

            return response()->json(['error' => 'Error interno al listar mensajes'], 500);
        }
    }

    /**
     * Página completa de notificaciones del usuario
     */
    public function misNotificaciones()
    {
        $mensajes = Message::where('id_receptor', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('notificaciones.index', compact('mensajes'));
    }

    public function marcarTodasLeidas()
    {
        Message::where('id_receptor', Auth::id())
            ->where('leido', 0)
            ->update(['leido' => 1]);

        return back()->with('success', 'Todas las notificaciones marcadas como leídas.');
    }


    public function marcarLeido(Request $request, $id)
    {
        $mensaje = Message::find($id);
        if ($mensaje && $mensaje->id_receptor === Auth::id()) {
            $mensaje->leido = 1;
            $mensaje->save();
        }

        // Si es AJAX retornar JSON, si es form normal redirigir
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back();
    }


    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            $notification = $user->notifications()->where('id', $id)->first();

            if ($notification) {
                $notification->markAsRead();
                return response()->json(['message' => 'Notificación marcada como leída']);
            }

            return response()->json(['message' => 'Notificación no encontrada'], 404);
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'markAsRead',
                'notification_id' => $id,
                'user_id' => Auth::id()
            ]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string',
                'user_id' => 'required|exists:users,id',
                'subject' => 'required_if:send_email,true|string|nullable',
                'send_email' => 'boolean',
                'action_text' => 'nullable|string',
                'action_url' => 'nullable|url',
            ]);

            // S3 Fix: Solo admins pueden enviar notificaciones a otros usuarios
            $sender = Auth::user();
            if (!$sender->isAdmin && !$sender->isSuperAdmin) {
                return response()->json([
                    'error' => 'No tienes permisos para enviar notificaciones',
                ], 403);
            }

            $user = User::find($validated['user_id']);

            $notificationData = [
                'message' => $validated['message'],
                'subject' => $validated['subject'] ?? null,
                'action_text' => $validated['action_text'] ?? null,
                'action_url' => $validated['action_url'] ?? null,
                'sender_id' => $sender->id,
                'send_email' => $validated['send_email'] ?? false,
            ];

            $user->notify(new ApiNotification($notificationData));

            return response()->json([
                'message' => 'Notificación enviada con éxito',
                'email_sent' => (bool) ($validated['send_email'] ?? false),
            ]);
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'store',
                'validation_errors' => $e instanceof \Illuminate\Validation\ValidationException
                    ? $e->errors()
                    : null
            ]);

            return response()->json([
                'error' => 'Error al enviar notificación',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $user->notifications()->where('id', $id)->delete();

            return response()->json(['message' => 'Notificación eliminada']);
        } catch (Throwable $e) {
            $this->logError($e, [
                'method' => 'destroy',
                'notification_id' => $id,
                'user_id' => Auth::id()
            ]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}
