<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportActionTakenNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $reportId,
        public string $audience,
        public string $action,
        public string $reason,
        public ?string $adminMessage = null,
        public ?string $itemName = null
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
        $actionLabel = str_replace('_', ' ', $this->action);
        $subject = $this->itemName ? " for {$this->itemName}" : '';
        $message = $this->audience === 'reporter'
            ? "Admin reviewed your report{$subject}. Action taken: {$actionLabel}."
            : "Admin reviewed a report about you{$subject}. Action taken: {$actionLabel}.";

        if ($this->adminMessage) {
            $message .= " Admin message: {$this->adminMessage}";
        }

        return [
            'title' => 'Report action update',
            'message' => $message,
            'report_id' => $this->reportId,
            'audience' => $this->audience,
            'action' => $this->action,
            'reason' => $this->reason,
            'admin_message' => $this->adminMessage,
            'item_name' => $this->itemName,
            'url' => $this->audience === 'target' ? route('my-listings') : route('my-rentals'),
        ];
    }
}
