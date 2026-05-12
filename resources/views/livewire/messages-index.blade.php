<div class="bg-slate-50 py-8 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-blue-600 dark:text-blue-300">Messages</p>
                <h1 class="mt-2 text-4xl font-extrabold tracking-normal text-slate-950 dark:text-white">Conversations</h1>
                <p class="mt-2 text-lg text-slate-600 dark:text-slate-300">Read rental conversations connected to your account.</p>
            </div>
        </div>

        <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="grid min-h-[38rem] grid-cols-1 lg:grid-cols-[22rem_minmax(0,1fr)]">
                <aside class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-950/50 lg:border-b-0 lg:border-r">
                    <div class="space-y-4 border-b border-slate-200 p-4 dark:border-slate-800">
                        <label for="message-search" class="sr-only">Search messages</label>
                        <input id="message-search" type="text" wire:model.live.debounce.300ms="search" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-950 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Search people, items, messages">

                        <div class="flex flex-col gap-3">
                            <button
                                type="button"
                                wire:click="$toggle('unreadOnly')"
                                class="{{ $unreadOnly ? 'bg-blue-600 text-white shadow-blue-600/20' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800' }} inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold shadow-sm transition"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.9 5.2a2 2 0 0 0 2.2 0L21 8m-18 8.5h18A1.5 1.5 0 0 0 22.5 15V6A1.5 1.5 0 0 0 21 4.5H3A1.5 1.5 0 0 0 1.5 6v9A1.5 1.5 0 0 0 3 16.5Z" />
                                </svg>
                                Unread Messages
                            </button>

                            <label class="space-y-1">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sort by</span>
                                <select wire:model.live="sortBy" class="w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                                    <option value="date">Date</option>
                                    <option value="name">Name</option>
                                    <option value="item">Items</option>
                                </select>
                            </label>
                        </div>
                    </div>

                    <div class="max-h-[28rem] overflow-y-auto p-3">
                        @forelse ($conversations as $conversation)
                            @php
                                $isOwner = (int) $conversation->item?->user_id === (int) auth()->id();
                                $otherUser = $isOwner ? $conversation->renter : $conversation->item?->user;
                                $latestMessage = $conversation->messages->last();
                                $isUnread = (int) $latestMessage?->sender_id !== (int) auth()->id();
                                $isSelected = (int) $selectedRentalId === (int) $conversation->id;
                                $initials = collect(explode(' ', trim($otherUser?->name ?? 'Unknown User')))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                    ->implode('');
                            @endphp

                            <button
                                type="button"
                                wire:click="selectConversation({{ $conversation->id }})"
                                wire:key="conversation-{{ $conversation->id }}"
                                class="{{ $isSelected ? 'bg-white shadow-sm ring-1 ring-blue-200 dark:bg-slate-900 dark:ring-blue-900/60' : 'hover:bg-white dark:hover:bg-slate-900' }} mb-2 flex w-full items-start gap-3 rounded-xl p-3 text-left transition"
                            >
                                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-violet-600 text-sm font-black text-white">
                                    {{ $initials ?: 'U' }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center justify-between gap-2">
                                        <span class="truncate text-sm font-bold text-slate-950 dark:text-white">{{ $otherUser?->name ?? 'Unknown user' }}</span>
                                        <span class="shrink-0 text-[11px] font-semibold text-slate-400">{{ $latestMessage?->created_at?->diffForHumans() }}</span>
                                    </span>
                                    <span class="mt-0.5 block truncate text-xs font-semibold text-blue-600 dark:text-blue-300">{{ $conversation->item?->name ?? 'Rental item' }}</span>
                                    <span class="{{ $isUnread ? 'font-bold text-slate-800 dark:text-slate-100' : 'text-slate-500 dark:text-slate-400' }} mt-1 block truncate text-sm">{{ $latestMessage?->body }}</span>
                                </span>
                                @if ($isUnread)
                                    <span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-blue-600"></span>
                                @endif
                            </button>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                No conversations found.
                            </div>
                        @endforelse
                    </div>
                </aside>

                <main class="flex min-h-[38rem] flex-col">
                    @if ($selectedConversation)
                        @php
                            $isOwner = (int) $selectedConversation->item?->user_id === (int) auth()->id();
                            $otherUser = $isOwner ? $selectedConversation->renter : $selectedConversation->item?->user;
                        @endphp

                        <header class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-300">{{ $selectedConversation->item?->name ?? 'Rental item' }}</p>
                                <h2 class="truncate text-xl font-extrabold text-slate-950 dark:text-white">{{ $otherUser?->name ?? 'Unknown user' }}</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $isOwner ? 'Renter conversation' : 'Lister conversation' }}</p>
                            </div>
                            <a href="{{ route('rental-requests.show', $selectedConversation) }}#messages" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-violet-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5">
                                Open rental thread
                            </a>
                        </header>

                        <div class="flex-1 space-y-4 overflow-y-auto bg-slate-50/70 p-5 dark:bg-slate-950/50">
                            @foreach ($selectedConversation->messages as $message)
                                @php
                                    $isMine = (int) $message->sender_id === (int) auth()->id();
                                @endphp

                                <div wire:key="message-{{ $message->id }}" class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                    <div class="{{ $isMine ? 'bg-blue-600 text-white' : 'bg-white text-slate-800 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-100 dark:ring-slate-800' }} max-w-[78%] rounded-2xl px-4 py-3 shadow-sm">
                                        <div class="mb-1 flex items-center gap-2 text-[11px] font-bold uppercase tracking-wide {{ $isMine ? 'text-blue-100' : 'text-slate-400' }}">
                                            <span>{{ $message->sender?->name ?? 'Unknown user' }}</span>
                                            <span>{{ $message->created_at->format('M d, g:i A') }}</span>
                                        </div>
                                        <p class="break-words text-sm font-medium leading-relaxed">{{ $message->body }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-1 items-center justify-center p-8 text-center">
                            <div>
                                <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-500/15 dark:text-blue-300 dark:ring-blue-500/20">
                                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10.5h8M8 14h5m-8 5 3.5-3.5H18a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v5.5a3 3 0 0 0 2 2.83V19Z" />
                                    </svg>
                                </div>
                                <h2 class="mt-4 text-xl font-extrabold text-slate-950 dark:text-white">No messages yet.</h2>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Your rental conversations will appear here.</p>
                            </div>
                        </div>
                    @endif
                </main>
            </div>
        </section>
    </div>
</div>
