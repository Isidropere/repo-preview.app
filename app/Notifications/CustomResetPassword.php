<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPassword extends Notification
{
    // use Queueable;

    /**
     * Create a new notification instance.
     */

    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $this->email,
        ], false));

        return (new MailMessage)
            ->subject('Recupera tu contraseña - Cambialord')
            ->greeting('¡Hola!')
            ->line('Recibiste este correo porque solicitaste un restablecimiento de contraseña para tu cuenta en Cambialord.')
            ->action('Restablecer contraseña', $url)
            ->line('Si no solicitaste un restablecimiento de contraseña, puedes ignorar este correo sin ningún problema.')
            ->salutation("Atentamente,\nEl equipo de Cambialord");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
