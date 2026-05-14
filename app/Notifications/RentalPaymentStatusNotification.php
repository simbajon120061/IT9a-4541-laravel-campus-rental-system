<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;

class RentalPaymentStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $rentalId,
        public int $itemId,
        public string $itemName,
        public string $type,
        public string $message,
        public float $remainingBalance,
        public string $dueDate,
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
            'title' => $this->title(),
            'message' => $this->message,
            'type' => $this->type,
            'encrypted_rental_id' => Crypt::encryptString((string) $this->rentalId),
            'encrypted_item_id' => Crypt::encryptString((string) $this->itemId),
            'item_name' => $this->itemName,
            'remaining_balance' => $this->remainingBalance,
            'due_date' => $this->dueDate,
            'url' => $this->type === 'payment_confirmed'
                ? route('renter.my-rentals', ['receipt' => $this->rentalId]).'#rental-'.$this->rentalId
                : route('renter.my-rentals').'#rental-'.$this->rentalId,
        ];
    }

    private function title(): string
    {
        return match ($this->type) {
            'upcoming_due' => 'Upcoming payment due',
            'overdue_payment' => 'Overdue payment reminder',
            'payment_confirmed' => 'Payment confirmed',
            default => 'Payment update',
        };
    }
}
