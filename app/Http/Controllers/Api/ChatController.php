<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMensajeRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Mensaje;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    // Obtener la conversación entre el usuario autenticado y otro usuario
    public function obtenerMensajes(Request $request, $destinatarioId)
    {
        $remitenteId = (string) Auth::id();
        $destinatarioId = (string) $destinatarioId;

        // Marcar como leídos los mensajes pendientes recibidos de este usuario
        Mensaje::where('remitente_id', $destinatarioId)
            ->where('destinatario_id', $remitenteId)
            ->where('leido', false)
            ->update(['leido' => true]);

        // Obtener la conversación completa ordenada cronológicamente
        $mensajes = Mensaje::where(function ($query) use ($remitenteId, $destinatarioId) {
                $query->where('remitente_id', $remitenteId)
                    ->where('destinatario_id', $destinatarioId);
            })
            ->orWhere(function ($query) use ($remitenteId, $destinatarioId) {
                $query->where('remitente_id', $destinatarioId)
                    ->where('destinatario_id', $remitenteId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'mensajes' => $mensajes,
        ]);
    }

    public function enviarMensaje(StoreMensajeRequest $request){
        $userId = (string) Auth::id();

        // Validar que no se envíe un mensaje a sí mismo
        if ($request->destinatario_id === $userId) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes enviarte mensajes a ti mismo.'
            ], 422);
        }

        $mensaje = Mensaje::create([
            'remitente_id'    => $userId,
            'destinatario_id' => $request->destinatario_id,
            'mensaje'         => $request->mensaje,
            'leido'           => false,
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => $mensaje
        ], 201);
    }

    // Obtener el historial de conversaciones recientes
    public function obtenerConversaciones(Request $request)
    {
        $userId = (string) Auth::id();

        // 1. Buscamos todos los mensajes donde el usuario participa y los ordenamos del más nuevo al más viejo
        $mensajes = Mensaje::where('remitente_id', $userId)
            ->orWhere('destinatario_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Agrupamos por el interlocutor
        $conversaciones = $mensajes->groupBy(function ($mensaje) use ($userId) {
            return $mensaje->remitente_id == $userId ? $mensaje->destinatario_id : $mensaje->remitente_id;
        })->map(function ($chat, $interlocutorId) use ($userId) {
            // Como $chat ya está ordenado descendentemente, el primer elemento es el último mensaje real
            $ultimoMensaje = $chat->first();
            $sinLeer = $chat->where('destinatario_id', $userId)->where('leido', false)->count();

            return [
                'interlocutor_id' => $interlocutorId,
                'ultimo_mensaje'  => $ultimoMensaje->mensaje,
                'fecha'           => $ultimoMensaje->created_at,
                'no_leidos'       => $sinLeer,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'conversaciones' => $conversaciones,
        ]);
    }
}