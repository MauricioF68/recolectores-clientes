<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Exception;
use App\Models\User; // Importamos el modelo User
use Illuminate\Support\Facades\Log; // Importamos Log

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        try {
            // Inicializamos Firebase aquí, usando las credenciales del .env
            $serviceAccountPath = storage_path(env('FIREBASE_CREDENTIALS'));

            $factory = (new Factory)->withServiceAccount($serviceAccountPath);
            $this->messaging = $factory->createMessaging();
        } catch (Exception $e) {
            // Loguear error si no se puede inicializar Firebase
            Log::error('Error al inicializar Firebase Messaging: ' . $e->getMessage());
            // Opcional: lanzar una excepción o manejar de otra forma
        }
    }

    /**
     * Envía una notificación a un token FCM específico.
     *
     * @param string $token El token FCM del dispositivo.
     * @param string $title El título de la notificación.
     * @param string $body El cuerpo del mensaje.
     * @return bool True si tuvo éxito, false si falló.
     */
    public function sendNotification(string $token, string $title, string $body): bool
    {
        // Verificar si $messaging se inicializó correctamente
        if (!$this->messaging) {
            Log::error('Firebase Messaging no está inicializado. No se puede enviar notificación.');
            return false;
        }

        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);

            $this->messaging->send($message);

            return true;
        } catch (Exception $e) {
            // Logueamos el error específico del envío
            Log::error('Error al enviar notificación FCM al token ' . substr($token, 0, 10) . '...: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía una notificación a todos los usuarios con un rol específico (ej. 'Administrador').
     *
     * @param string $role El rol (ej. 'Administrador', 'Recolector')
     * @param string $title El título de la notificación.
     * @param string $body El cuerpo del mensaje.
     */
    public function sendToRole(string $role, string $title, string $body)
    {
        // Verificar si $messaging se inicializó correctamente
        if (!$this->messaging) {
            Log::error('Firebase Messaging no está inicializado. No se puede enviar notificación por rol.');
            return;
        }

        try {
            // 1. Buscamos todos los usuarios con ese rol que tengan token
            $users = User::where('role', $role)
                ->whereNotNull('fcm_token')
                ->get();

            foreach ($users as $user) {
                // 3. Enviamos la notificación a cada uno
                $this->sendNotification($user->fcm_token, $title, $body);
                // Opcional: Añadir un pequeño delay si son muchos usuarios
                // usleep(100000); // 100ms
            }
        } catch (Exception $e) {
            Log::error('Error general al enviar notificaciones por rol (' . $role . '): ' . $e->getMessage());
        }
    }
}
