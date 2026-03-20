<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * ============================================================
 * MessageController — Sistema de mensajería entre usuarios
 * ============================================================
 *
 * Permite a los usuarios enviarse mensajes directos sobre
 * artículos específicos. Incluye bandeja de recibidos/enviados,
 * marcado de lectura y contador de no leídos.
 *
 * Rutas:
 *   GET  /messages              → index (bandeja)
 *   GET  /messages/create/{item?} → create (nuevo mensaje)
 *   POST /messages              → store (enviar)
 *   GET  /messages/{id}         → show (ver mensaje)
 *   POST /messages/{id}/read    → markAsRead
 *   GET  /messages/unread/count → unreadCount (JSON)
 *
 * Middleware: auth
 * ============================================================
 */
class MessageController extends Controller
{
    /**
     * Display a listing of the messages for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();

        // Mensajes recibidos
        $receivedMessages = Message::with(['sender'])
            ->where('id_receptor', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Mensajes enviados
        $sentMessages = Message::with(['sender'])
            ->where('id_emisor', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('messages.index', compact('receivedMessages', 'sentMessages'));
    }

    /**
     * Show the form for creating a new message
     */
    public function create($itemId = null)
    {
        $item = null;
        $receiver = null;

        if ($itemId) {
            $item = Item::findOrFail($itemId);
            $receiver = $item->user;
        }

        return view('messages.create', compact('item', 'receiver'));
    }

    /**
     * Store a newly created message in storage
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'item_id' => 'required|exists:items,id_item',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $message = Message::create([
            'id_emisor' => Auth::id(),
            'id_receptor' => $request->receiver_id,
            'mensaje' => $request->message,
            'leido' => false,
        ]);

        return redirect()->route('messages.show', $message->id)
            ->with('success', 'Mensaje enviado correctamente');
    }

    /**
     * Display the specified message
     */
    public function show($id)
    {
        $message = Message::with(['sender'])
            ->where(function ($query) {
                $query->where('id_emisor', Auth::id())
                    ->orWhere('id_receptor', Auth::id());
            })
            ->findOrFail($id);

        // Marcar como leído si el receptor es el usuario actual
        if ($message->id_receptor == Auth::id() && !$message->leido) {
            $message->update(['leido' => true]);
        }

        return view('messages.show', compact('message'));
    }

    /**
     * Mark message as read
     */
    public function markAsRead($id)
    {
        $message = Message::where('id_receptor', Auth::id())
            ->findOrFail($id);

        $message->update(['leido' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Get unread messages count for notifications
     */
    public function unreadCount()
    {
        $count = Message::where('id_receptor', Auth::id())
            ->where('leido', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
