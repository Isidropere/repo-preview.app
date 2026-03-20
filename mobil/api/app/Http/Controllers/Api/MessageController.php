<?php

/**
 * ============================================================
 * MessageController — Sistema de mensajería (API móvil)
 * ============================================================
 * Gestiona las conversaciones y mensajes entre usuarios.
 * Todas las rutas requieren autenticación (auth:sanctum).
 *
 * Rutas que usa:
 *   GET  /api/messages                  → conversations()  Lista de chats
 *   GET  /api/messages/unread/count     → unreadCount()    Badge de no leídos
 *   GET  /api/messages/{userId}         → messages()       Mensajes de un chat
 *   POST /api/messages                  → send()           Enviar mensaje
 * ============================================================
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Listar todas las conversaciones del usuario.
     *
     * Para cada conversación devuelve:
     *   - Datos del otro usuario (nombre, foto)
     *   - Último mensaje enviado/recibido
     *   - Cantidad de mensajes no leídos
     *
     * La lógica del CASE WHEN determina quién es "el otro"
     * dependiendo de si el usuario es sender o receiver.
     *
     * @param  Request $request
     * @return JsonResponse  array de conversaciones
     */
    public function conversations(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = DB::table('messages as m')
            // JOIN dinámico: si yo soy el sender, el otro es receiver y viceversa
            ->join('users as u', function ($join) use ($userId) {
                $join->on('u.id', '=', DB::raw(
                    "CASE WHEN m.sender_id = {$userId} THEN m.receiver_id ELSE m.sender_id END"
                ));
            })
            // Traer mensajes donde yo participo (como sender o receiver)
            ->where(function ($q) use ($userId) {
                $q->where('m.sender_id', $userId)
                  ->orWhere('m.receiver_id', $userId);
            })
            // Solo el último mensaje de cada conversación (MAX id)
            ->whereRaw('m.id = (
                SELECT MAX(m2.id) FROM messages m2
                WHERE (m2.sender_id = m.sender_id AND m2.receiver_id = m.receiver_id)
                   OR (m2.sender_id = m.receiver_id AND m2.receiver_id = m.sender_id)
            )')
            ->select(
                'u.id as other_user_id',
                'u.nombre_usuario',
                'u.foto_perfil',
                'm.message as last_message',
                'm.created_at as last_message_at',
                // Subquery para contar mensajes no leídos de ese usuario hacia mí
                DB::raw("(SELECT COUNT(*) FROM messages WHERE receiver_id = {$userId} AND sender_id = u.id AND read_at IS NULL) as unread_count")
            )
            ->orderByDesc('m.created_at') // Más recientes primero
            ->get();

        return response()->json($conversations);
    }

    /**
     * Obtener mensajes de una conversación específica.
     *
     * Devuelve todos los mensajes entre el usuario autenticado
     * y el usuario con $userId, ordenados cronológicamente.
     * Además marca como leídos los mensajes recibidos.
     *
     * @param  Request $request
     * @param  int     $userId  ID del otro usuario
     * @return JsonResponse  array de mensajes
     */
    public function messages(Request $request, $userId)
    {
        $myId = $request->user()->id;

        // Paso 1: Obtener mensajes en ambas direcciones (yo→él y él→yo)
        $messages = DB::table('messages')
            ->where(function ($q) use ($myId, $userId) {
                $q->where('sender_id', $myId)->where('receiver_id', $userId);
            })
            ->orWhere(function ($q) use ($myId, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $myId);
            })
            ->orderBy('created_at') // Orden cronológico para el chat
            ->get();

        // Paso 2: Marcar como leídos los mensajes que él me envió a mí
        DB::table('messages')
            ->where('sender_id', $userId)
            ->where('receiver_id', $myId)
            ->whereNull('read_at')           // Solo los que aún no se leyeron
            ->update(['read_at' => now()]);  // Registrar timestamp de lectura

        return response()->json($messages);
    }

    /**
     * Enviar un mensaje a otro usuario.
     *
     * Inserta el mensaje en la tabla y devuelve el registro
     * completo para que la app lo muestre inmediatamente.
     *
     * @param  Request $request  receiver_id, message
     * @return JsonResponse  mensaje creado  HTTP 201
     */
    public function send(Request $request)
    {
        // Paso 1: Validar destinatario y contenido del mensaje
        $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'message'     => 'required|string|max:1000',
        ]);

        // Paso 2: Insertar el mensaje
        $id = DB::table('messages')->insertGetId([
            'sender_id'   => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'message'     => $request->message,
            'created_at'  => now(),
            'updated_at'  => now(),
            // read_at queda NULL hasta que el receptor lo lea
        ]);

        // Paso 3: Devolver el mensaje recién creado
        $msg = DB::table('messages')->where('id', $id)->first();

        return response()->json($msg, 201);
    }

    /**
     * Contar mensajes no leídos del usuario autenticado.
     *
     * Usado para mostrar el badge rojo en el tab de Mensajes.
     *
     * @param  Request $request
     * @return JsonResponse  { count: int }
     */
    public function unreadCount(Request $request)
    {
        $count = DB::table('messages')
            ->where('receiver_id', $request->user()->id)
            ->whereNull('read_at') // No leídos = read_at es NULL
            ->count();

        return response()->json(['count' => $count]);
    }
}
