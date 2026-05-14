<?php

namespace App\Livewire;

use App\Models\Rental;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;

class NotificationsDropdown extends Component
{
    public function openNotification(string $notificationId): mixed
    {
        abort_unless(Auth::check(), 403);

        $notification = Auth::user()->notifications()->find($notificationId);

        if (! $notification) {
            return redirect()->route('my-listings');
        }

        $notificationData = $notification->data;
        $notification->delete();
        $notificationData = $this->decodeNotificationData($notificationData);
        $notificationData['notification_class'] = $notification->type;

        $rentalId = $notificationData['rental_id'] ?? $this->resolveRentalIdFromNotificationData($notificationData);

        if ($rentalId) {
            if ($this->currentUserOwnsRentalRequest($rentalId)) {
                return redirect()->to($this->ownerRentalNotificationUrl((int) $rentalId, $notificationData));
            }

            if ($this->currentUserRentedRentalRequest((int) $rentalId)) {
                return redirect()->to($this->renterRentalNotificationUrl((int) $rentalId, $notificationData));
            }
        }

        $url = $notificationData['url'] ?? null;

        if ($url) {
            return redirect()->to($url);
        }

        if (! $url && isset($notificationData['item_id'])) {
            $url = route('item.view', $notificationData['item_id']);
        }

        return redirect()->to($url ?? route('my-listings'));
    }

    /**
     * @param  array<string, mixed>  $notificationData
     */
    private function resolveRentalIdFromNotificationData(array $notificationData): ?int
    {
        if (! isset($notificationData['item_id'])) {
            return null;
        }

        $query = Rental::query()
            ->where('item_id', (int) $notificationData['item_id'])
            ->whereHas('item', fn ($itemQuery) => $itemQuery->where('user_id', Auth::id()));

        if (isset($notificationData['renter_id'])) {
            $query->where('renter_id', (int) $notificationData['renter_id']);
        }

        $rental = $query->latest('id')->first();

        return $rental?->id;
    }

    /**
     * @param  array<string, mixed>  $notificationData
     * @return array<string, mixed>
     */
    private function decodeNotificationData(array $notificationData): array
    {
        if (isset($notificationData['encrypted_rental_id']) && ! isset($notificationData['rental_id'])) {
            $notificationData['rental_id'] = $this->decryptInt($notificationData['encrypted_rental_id']);
        }

        if (isset($notificationData['encrypted_item_id']) && ! isset($notificationData['item_id'])) {
            $notificationData['item_id'] = $this->decryptInt($notificationData['encrypted_item_id']);
        }

        if (isset($notificationData['encrypted_renter_id']) && ! isset($notificationData['renter_id'])) {
            $notificationData['renter_id'] = $this->decryptInt($notificationData['encrypted_renter_id']);
        }

        return $notificationData;
    }

    private function decryptInt(mixed $value): ?int
    {
        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            return (int) $value;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            $decryptedValue = Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }

        return ctype_digit($decryptedValue) ? (int) $decryptedValue : null;
    }

    private function currentUserOwnsRentalRequest(int $rentalId): bool
    {
        return Rental::query()
            ->whereKey($rentalId)
            ->whereHas('item', fn ($itemQuery) => $itemQuery->where('user_id', Auth::id()))
            ->exists();
    }

    private function currentUserRentedRentalRequest(int $rentalId): bool
    {
        return Rental::query()
            ->whereKey($rentalId)
            ->where('renter_id', Auth::id())
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $notificationData
     */
    private function ownerRentalNotificationUrl(int $rentalId, array $notificationData): string
    {
        if ($this->isMessageNotification($notificationData)) {
            return route('lister.messages', ['rental' => $rentalId]);
        }

        return route('rental-requests.show', [
            'rental' => $rentalId,
            'portal' => 'lister',
        ]);
    }

    /**
     * @param  array<string, mixed>  $notificationData
     */
    private function renterRentalNotificationUrl(int $rentalId, array $notificationData): string
    {
        if ($this->isMessageNotification($notificationData)) {
            return route('renter.messages', ['rental' => $rentalId]);
        }

        return route('renter.my-rentals').'#rental-'.$rentalId;
    }

    /**
     * @param  array<string, mixed>  $notificationData
     */
    private function isMessageNotification(array $notificationData): bool
    {
        return ($notificationData['type'] ?? null) === 'message'
            || ($notificationData['title'] ?? null) === 'New message'
            || str_contains((string) ($notificationData['notification_class'] ?? ''), 'RentalMessageSentNotification');
    }

    public function markAsRead(string $notificationId): void
    {
        abort_unless(Auth::check(), 403);

        $notification = Auth::user()->notifications()->find($notificationId);

        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        abort_unless(Auth::check(), 403);

        Auth::user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        abort_unless(Auth::check(), 403);

        $user = Auth::user()->loadCount('unreadNotifications');

        $notifications = $user
            ->notifications()
            ->latest()
            ->limit(8)
            ->get();

        return view('livewire.notifications-dropdown', [
            'notifications' => $notifications,
            'unreadCount' => (int) ($user->unread_notifications_count ?? 0),
        ]);
    }
}
