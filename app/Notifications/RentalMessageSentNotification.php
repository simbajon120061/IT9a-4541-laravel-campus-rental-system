<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;

class RentalMessageSentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $rentalId,
        public int $itemId,
        public string $itemName,
        public string $senderName,
        public string $messageBody
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
        return [
            'title' => 'New message',
            'message' => "{$this->senderName}: {$this->messageBody}",
            'type' => 'message',
            'encrypted_rental_id' => Crypt::encryptString((string) $this->rentalId),
            'encrypted_item_id' => Crypt::encryptString((string) $this->itemId),
            'item_name' => $this->itemName,
            'sender_name' => $this->senderName,
            'message_body' => $this->messageBody,
            'url' => route('rental-requests.show', $this->rentalId).'#messages',
        ];
    }
}
