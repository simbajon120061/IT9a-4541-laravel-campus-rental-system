<div class="h-[calc(100vh-4rem)] bg-slate-50 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
    <div class="flex h-full w-full flex-col md:px-4 md:py-4 xl:px-6 xl:py-6">
        <div class="hidden shrink-0 lg:block">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-blue-600 dark:text-blue-300">Messages</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-normal text-slate-950 dark:text-white">Conversations</h1>
                    <p class="mt-2 text-lg text-slate-600 dark:text-slate-300">Read rental conversations connected to your account.</p>
                </div>
                <div class="hidden items-center justify-between gap-3 sm:flex">
                <div class="flex shrink-0 items-center gap-2">
                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-900 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700" aria-label="New message" title="New message">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M16.9 4.8 19.2 7.1m-8.8 8.8 1.8-.2a2 2 0 0 0 1.2-.6l6.2-6.2a1.6 1.6 0 0 0 0-2.3l-2.2-2.2a1.6 1.6 0 0 0-2.3 0l-6.2 6.2a2 2 0 0 0-.6 1.2l-.2 1.8a1.2 1.2 0 0 0 1.3 1.3ZM7 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-1" />
                        </svg>
                    </button>
                </div>
            </div>
            </div>
            
        </div>

        <section class="flex min-h-0 flex-1 overflow-hidden bg-white shadow-sm dark:bg-slate-900 sm:rounded-2xl sm:border sm:border-slate-200 dark:sm:border-slate-800 lg:mt-6">
            <div class="grid min-h-0 w-full grid-rows-[auto_minmax(0,1fr)] md:grid-cols-[minmax(18rem,34vw)_minmax(0,1fr)] md:grid-rows-none xl:grid-cols-[22rem_minmax(0,1fr)]">
                <aside class="{{ $selectedConversation ? 'hidden md:flex' : 'flex' }} min-h-0 flex-col border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950 md:border-b-0 md:border-r">
                    <div class="shrink-0 space-y-3 border-b border-slate-100 p-3 dark:border-slate-800 sm:space-y-4 sm:p-4">
                        

                        <label for="message-search" class="relative block">
                            <span class="sr-only">Search messages</span>
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-500 dark:text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />
                                </svg>
                            </span>
                            <input id="message-search" type="text" wire:model.live.debounce.300ms="search" class="w-full rounded-full border-0 bg-slate-100 py-2.5 pl-12 pr-4 text-sm font-medium text-slate-950 placeholder:text-slate-500 focus:bg-white focus:ring-2 focus:ring-blue-500 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-400 dark:focus:bg-slate-900 sm:py-3 sm:text-base" placeholder="Search Messages">
                        </label>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" wire:click="$set('unreadOnly', false)" class="{{ ! $unreadOnly ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300' : 'text-slate-950 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800' }} inline-flex h-10 items-center rounded-full px-4 text-sm font-bold transition sm:h-11 sm:text-base">
                                All
                            </button>
                            <button type="button" wire:click="$set('unreadOnly', true)" class="{{ $unreadOnly ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300' : 'text-slate-950 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800' }} inline-flex h-10 items-center rounded-full px-4 text-sm font-bold transition sm:h-11 sm:text-base">
                                Unread
                            </button>
                            <label class="relative ml-auto">
                                <span class="sr-only">Sort by</span>
                                <select wire:model.live="sortBy" class="h-10 rounded-full border-0 bg-slate-100 py-0 pl-4 pr-9 text-xs font-bold text-slate-950 focus:ring-2 focus:ring-blue-500 dark:bg-slate-800 dark:text-slate-100 sm:h-11 sm:text-sm">
                                    <option value="date">Date</option>
                                    <option value="item">Item</option>
                                    <option value="name">Name</option>
                                </select>
                            </label>
                        </div>
                    </div>

                    <div class="max-h-24 space-y-1 overflow-y-auto p-2 sm:max-h-48 md:max-h-none md:flex-1">
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
                                class="{{ $isSelected ? 'bg-blue-50 dark:bg-blue-500/15' : 'hover:bg-slate-50 dark:hover:bg-slate-900' }} flex w-full items-center gap-3 rounded-xl p-2.5 text-left transition sm:p-3"
                            >
                                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-violet-600 text-sm font-black text-white sm:h-14 sm:w-14 sm:text-base">
                                    {{ $initials ?: 'U' }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center justify-between gap-2">
                                        <span class="truncate text-sm font-extrabold text-slate-950 dark:text-white sm:text-base">{{ $otherUser?->name ?? 'Unknown user' }}</span>
                                        <span class="shrink-0 text-xs font-medium text-slate-500 dark:text-slate-400 sm:text-sm">{{ $latestMessage?->created_at?->diffForHumans() }}</span>
                                    </span>
                                    <span class="{{ $isUnread ? 'font-bold text-slate-900 dark:text-slate-100' : 'text-slate-600 dark:text-slate-400' }} mt-1 block truncate text-xs sm:text-sm">
                                        {{ $conversation->item?->name ?? 'Rental item' }} - {{ $latestMessage?->body }}
                                    </span>
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

                <main class="{{ $selectedConversation ? 'flex' : 'hidden md:flex' }} min-h-0 min-w-0 flex-col">
                    @if ($selectedConversation)
                        @php
                            $isOwner = (int) $selectedConversation->item?->user_id === (int) auth()->id();
                            $otherUser = $isOwner ? $selectedConversation->renter : $selectedConversation->item?->user;
                        @endphp

                        <header class="flex shrink-0 items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 dark:border-slate-800 sm:px-5 sm:py-4">
                            <div class="flex min-w-0 items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="closeConversation"
                                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white md:hidden"
                                    aria-label="Back to conversations"
                                    title="Back to conversations"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19 8 12l7-7" />
                                    </svg>
                                </button>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-300">{{ $selectedConversation->item?->name ?? 'Rental item' }}</p>
                                    <h2 class="truncate text-base font-extrabold text-slate-950 dark:text-white sm:text-xl">{{ $otherUser?->name ?? 'Unknown user' }}</h2>
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400 sm:text-sm">{{ $isOwner ? 'Renter conversation' : 'Lister conversation' }}</p>
                                </div>
                            </div>
                            <a href="{{ route('rental-requests.show', ['rental' => $selectedConversation, 'portal' => $portalContext]) }}#messages" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-violet-600 px-3 py-2 text-xs font-bold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 sm:px-4 sm:py-2.5 sm:text-sm">
                                Open rental thread
                            </a>
                        </header>

                        <div wire:poll.10s class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-slate-50/70 p-4 dark:bg-slate-950/50 sm:space-y-4 sm:p-5">
                            @foreach ($selectedConversation->messages as $message)
                                @php
                                    $isMine = (int) $message->sender_id === (int) auth()->id();
                                @endphp

                                <div wire:key="message-{{ $message->id }}" class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                    <div class="{{ $isMine ? 'bg-blue-600 text-white' : 'bg-white text-slate-800 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-100 dark:ring-slate-800' }} max-w-[82%] rounded-2xl px-4 py-3 shadow-sm sm:max-w-[78%]">
                                        <div class="mb-1 flex items-center gap-2 text-[11px] font-bold uppercase tracking-wide {{ $isMine ? 'text-blue-100' : 'text-slate-400' }}">
                                            <span>{{ $message->sender?->name ?? 'Unknown user' }}</span>
                                            <span>{{ $message->created_at->format('M d, g:i A') }}</span>
                                        </div>
                                        <p class="break-words text-sm font-medium leading-relaxed">{{ $message->body }}</p>
                                        @if ($isMine)
                                            <div class="mt-1 flex items-center justify-end gap-1 text-[11px] font-semibold {{ $message->read_at ? 'text-blue-100' : 'text-blue-200' }}">
                                                @if ($message->read_at)
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m4 12 5 5L20 6" />
                                                    </svg>
                                                    Seen
                                                @else
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Sent
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <form wire:submit.prevent="sendMessage" class="shrink-0 border-t border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900 sm:p-4">
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <div class="min-w-0 flex-1">
                                        <label for="messageText" class="sr-only">Message</label>
                                        <textarea
                                            id="messageText"
                                            wire:model.live="messageText"
                                            rows="1"
                                            maxlength="40"
                                            class="block max-h-24 min-h-12 w-full resize-none rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-950 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                            placeholder="Write a message..."
                                        ></textarea>
                                    </div>
                                    <button
                                        type="submit"
                                        class="inline-flex h-12 shrink-0 items-center justify-center gap-2 self-center rounded-xl bg-blue-600 px-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none dark:disabled:bg-slate-700 sm:px-4"
                                        wire:loading.attr="disabled"
                                        wire:target="sendMessage"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12 3.3 4.6c-.3-.9.6-1.7 1.4-1.3L21 12 4.7 20.7c-.8.4-1.7-.4-1.4-1.3L6 12Zm0 0h7" />
                                        </svg>
                                        <span class="hidden sm:inline">Send</span>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between gap-3 text-xs">
                                    <div class="min-w-0">
                                        @error('messageText')
                                            <span class="font-semibold text-rose-600 dark:text-rose-300">{{ $message }}</span>
                                        @else
                                            <span class="truncate text-slate-500 dark:text-slate-400">Messages are saved in this rental conversation.</span>
                                        @enderror
                                    </div>
                                    <span class="shrink-0 text-slate-500 dark:text-slate-400">{{ strlen($messageText) }}/40</span>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="flex flex-1 items-center justify-center p-8 text-center">
                            <div>
                                <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-500/15 dark:text-blue-300 dark:ring-blue-500/20">
                                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10.5h8M8 14h5m-8 5 3.5-3.5H18a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v5.5a3 3 0 0 0 2 2.83V19Z" />
                                    </svg>
                                </div>
                                <h2 class="mt-4 text-xl font-extrabold text-slate-950 dark:text-white">Select a conversation.</h2>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Click a rental conversation to read the full thread.</p>
                            </div>
                        </div>
                    @endif
                </main>
            </div>
        </section>
    </div>
</div>
