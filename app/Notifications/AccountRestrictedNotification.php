<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountRestrictedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ?int $reportId = null,
        public ?string $reason = null,
        public ?string $adminMessage = null,
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
        $message = 'Your account has been restricted by an administrator.';

        if ($this->reason) {
            $message .= " Reason: {$this->reason}.";
        }

        if ($this->adminMessage) {
            $message .= " Admin message: {$this->adminMessage}";
        }

        return [
            'title' => 'Account restricted',
            'message' => $message,
            'report_id' => $this->reportId,
            'reason' => $this->reason,
            'admin_message' => $this->adminMessage,
            'url' => route('profile.show'),
        ];
    }
}
