<div x-data="{ open: false }" class="relative" wire:poll.30s>
    <button
        type="button"
        @click="open = !open"
        class="relative inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-600 ring-1 ring-slate-200 transition hover:bg-white hover:text-slate-900"
        aria-label="Notifications"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        @click.outside="open = false"
        class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
    >
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-900">Notifications</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs font-medium text-[#2850d8] hover:text-[#1f42b5]">
                    Mark all as read
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                @php
                    $notificationUrl = $notification->data['url'] ?? null;

                    if (! $notificationUrl && Auth::user()?->isAdministrator() && (isset($notification->data['report_id']) || isset($notification->data['appeal_id']))) {
                        $notificationUrl = route('admin.reports', [], false);
                    }
                @endphp
                <a
                    href="{{ $notificationUrl ?: route('my-listings') }}"
                    wire:click.prevent="openNotification('{{ $notification->id }}')"
                    class="block border-b border-slate-100 px-4 py-3 transition hover:bg-slate-50 {{ is_null($notification->read_at) ? 'bg-blue-50/50' : '' }}"
                >
                    <p class="text-sm font-semibold text-slate-800">
                        {{ $notification->data['title'] ?? 'Notification' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-600">
                        {{ $notification->data['message'] ?? '' }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400">
                        {{ $notification->created_at?->diffForHumans() }}
                    </p>
                </a>
            @empty
                <div class="px-4 py-8 text-center text-sm text-slate-500">
                    No notifications yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
