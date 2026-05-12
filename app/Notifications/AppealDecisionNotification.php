<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppealDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $appealId,
        public string $itemName,
        public string $status,
        public ?string $adminMessage = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = "Your appeal for {$this->itemName} was {$this->status}.";

        if ($this->adminMessage) {
            $message .= " Admin message: {$this->adminMessage}";
        }

        return [
            'title' => 'Appeal decision',
            'message' => $message,
            'appeal_id' => $this->appealId,
            'item_name' => $this->itemName,
            'status' => $this->status,
            'admin_message' => $this->adminMessage,
            'url' => route('my-listings'),
        ];
    }
}
