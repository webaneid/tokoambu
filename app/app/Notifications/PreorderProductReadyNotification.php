<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PreorderProductReadyNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected int $orderId,
        protected string $orderNumber,
        protected ?string $deadline = null,
        protected ?string $actionUrl = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $title = 'Produk preorder siap';
        $message = $this->deadline
            ? "Produk preorder untuk {$this->orderNumber} sudah siap. Mohon lakukan pelunasan sebelum {$this->deadline}."
            : "Produk preorder untuk {$this->orderNumber} sudah siap. Mohon lakukan pelunasan.";

        return [
            'title' => $title,
            'message' => $message,
            'order_id' => $this->orderId,
            'order_number' => $this->orderNumber,
            'deadline' => $this->deadline,
            'action_url' => $this->actionUrl,
        ];
    }
}
