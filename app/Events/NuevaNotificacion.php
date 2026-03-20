<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevaNotificacion
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notificacion;
    public $userId;

    public function __construct($notificacion, $userId)
    {
        $this->notificacion = $notificacion;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new Channel('notificaciones.' . $this->userId);
    }
}
