<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Exception;

class NotificationController extends Controller
{
    /**
     * Guarda o actualiza el token FCM del usuario autenticado.
     */
    public function storeToken(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Actualizamos el token en la base de datos
        $user->update(['fcm_token' => $validated['token']]);

        // Devolvemos una respuesta JSON exitosa (no necesitamos redirigir)
        return response()->json(['message' => 'Token actualizado correctamente.']);
    }

    /**
     * Envía una notificación de prueba al usuario autenticado.
     */
    /**
     * Envía una notificación de prueba al usuario autenticado.
     */
    public function sendTestNotification()
    {
        try {
            // 1. Obtenemos el token FCM del usuario logueado
            $user = Auth::user();
            $token = $user->fcm_token;

            if (!$token) {
                // ¡Respuesta JSON de error!
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontró tu token FCM. ¿Ya diste permiso en el navegador?'
                ], 404);
            }

            // 2. Inicializamos Firebase
            $serviceAccountPath = storage_path(env('FIREBASE_CREDENTIALS'));

            $factory = (new Factory)->withServiceAccount($serviceAccountPath);
            $messaging = $factory->createMessaging();

            // 3. Creamos la notificación
            $notification = Notification::create(
                '¡Prueba desde Laravel! 🚀', // Título
                '¡Felicidades, ' . $user->name . '! Todo está conectado.' // Cuerpo
            );

            // 4. Creamos el mensaje dirigido a ESE token específico
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);

            // 5. ¡Enviamos el mensaje!
            $messaging->send($message);

            // 6. ¡Respuesta JSON de éxito!
            return response()->json([
                'status' => 'success',
                'message' => '¡Notificación de prueba enviada!'
            ]);
        } catch (Exception $e) {
            // ¡Respuesta JSON de error de excepción!
            return response()->json([
                'status' => 'error',
                'message' => 'Error al enviar la notificación: ' . $e->getMessage()
            ], 500);
        }
    }
}
