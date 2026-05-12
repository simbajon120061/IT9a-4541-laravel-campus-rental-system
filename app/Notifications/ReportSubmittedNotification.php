<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $reportId,
        public string $reportType,
        public string $reason,
        public string $reporterName,
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
        $subject = $this->itemName
            ? "your listing {$this->itemName}"
            : 'your account';

        return [
            'title' => 'Report received',
            'message' => "{$this->reporterName} submitted a report about {$subject}. Reason: {$this->reason}.",
            'report_id' => $this->reportId,
            'report_type' => $this->reportType,
            'reason' => $this->reason,
            'item_name' => $this->itemName,
            'url' => route('my-listings'),
        ];
    }
}
