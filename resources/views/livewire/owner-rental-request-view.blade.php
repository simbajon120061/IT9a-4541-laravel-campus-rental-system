<div class="bg-gradient-to-b from-slate-50 via-blue-50/30 to-white py-8 md:py-12">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">View Request</h1>
                <p class="text-sm text-slate-600">
                    {{ $isOwner ? 'Review requester details and decide to grant or reject.' : 'Review your rental request details and status.' }}
                </p>
            </div>
            <a href="{{ $isOwner ? route('lister.inventory') : route('renter.my-rentals') }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                {{ $isOwner ? 'Go to Rent Inventory' : 'Back to My Rentals' }}
            </a>
        </div>

        @if (session()->has('message'))
            <x-floating-action-notice
                :message="session('message')"
                :tone="str_contains(session('message'), 'rejected') ? 'danger' : 'success'"
                wire:key="rental-session-notice-{{ $noticeToken }}"
            />
        @endif

        @if($reportNotice)
            <x-floating-action-notice
                :message="$reportNotice"
                wire:key="rental-report-notice-{{ $noticeToken }}"
            />
        @endif

        @if ($showReportForm)
            <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/50 px-4 py-6">
                <div class="w-full max-w-md rounded-xl border border-rose-200 bg-white p-5 shadow-2xl dark:border-rose-900/60 dark:bg-slate-900">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            Report {{ $reportType === 'message' ? 'Message' : 'User' }}
                        </p>
                        <button wire:click="cancelReport" class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                            Cancel
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs text-slate-600 dark:text-slate-400">Reason</label>
                            <input type="text" wire:model="reportReason" maxlength="120" class="w-full rounded-md border-slate-300 text-sm focus:border-rose-500 focus:ring-rose-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="Example: harassment or unsafe behavior">
                            @error('reportReason') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-slate-600 dark:text-slate-400">Details</label>
                            <textarea wire:model="reportDetails" rows="3" class="w-full rounded-md border-slate-300 text-sm focus:border-rose-500 focus:ring-rose-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="Share what admins should verify."></textarea>
                            @error('reportDetails') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <button wire:click="submitReport" class="w-full rounded-md bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-black dark:bg-rose-600 dark:hover:bg-rose-700">
                            Submit Report
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if ($dueTomorrow)
            <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                <span class="font-semibold">Due soon:</span> This rental is due in {{ $daysLeft }} day.
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-lg font-bold text-slate-900">Request Information</h2>
                        @if ($isOwner && $rental->status === 'pending')
                            @if ($isEditingSchedule)
                                <div class="flex gap-2">
                                    <button wire:click="updateSchedule" class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
                                        Save Dates
                                    </button>
                                    <button wire:click="cancelScheduleEdit" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-400">
                                        Cancel
                                    </button>
                                </div>
                            @else
                                <button wire:click="editSchedule" class="rounded-lg border border-blue-200 px-3 py-2 mb-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">
                                    Edit Dates
                                </button>
                            @endif
                        @endif
                    </div>
                    <div class="aspect-[16/9] bg-slate-100">
                        @if ($rental->item->imageUrl())
                            <img
                                src="{{ $rental->item->imageUrl() }}"
                                alt="{{ $rental->item->name }} rental item image"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-slate-100 text-sm font-semibold text-slate-500">
                                No item image uploaded
                            </div>
                        @endif
                    </div>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Item</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $rental->item->name }}</dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Rental Status</dt>
                            <dd class="mt-2">
                                @if ($rental->status === 'pending')
                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Pending Request</span>
                                @elseif ($rental->status === 'approved')
                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">On Process</span>
                                @elseif ($rental->status === 'active')
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Active Loan</span>
                                @else
                                    <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">Rejected</span>
                                @endif
                            </dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Start Date</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                @if ($isEditingSchedule)
                                    <input type="date" wire:model="editableStartDate" class="w-full rounded-md border-slate-300 text-sm font-semibold focus:border-blue-500 focus:ring-blue-500">
                                    @error('editableStartDate') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                                @else
                                    {{ $rental->start_date->format('M d, Y') }}
                                @endif
                            </dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">End Date</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                @if ($isEditingSchedule)
                                    <input type="date" wire:model="editableEndDate" class="w-full rounded-md border-slate-300 text-sm font-semibold focus:border-blue-500 focus:ring-blue-500">
                                    @error('editableEndDate') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                                @else
                                    {{ $rental->end_date->format('M d, Y') }}
                                @endif
                            </dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Days Requested</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $daysRequested }} day(s)</dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Amount</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">&#8369;{{ number_format($rental->total_price, 2) }}</dd>
                        </div>
                    </dl>
                </div>

                
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-bold text-slate-900">Requester</h2>
                        @if ((int) $rental->renter_id !== (int) auth()->id())
                            <button wire:click="openUserReportForm({{ $rental->renter_id }})" class="rounded-md border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                                Report User
                            </button>
                        @endif
                    </div>
                    <div class="mt-4 space-y-3 text-sm">
                        <p><span class="font-semibold text-slate-700">Name:</span> <span class="text-slate-900">{{ $rental->renter->name }}</span></p>
                        <p><span class="font-semibold text-slate-700">Email:</span> <span class="text-slate-900">{{ $rental->renter->email }}</span></p>
                        <p><span class="font-semibold text-slate-700">Phone 1:</span> <span class="text-slate-900">{{ $rental->renter->phone_number ?: 'Not provided' }}</span></p>
                        <p><span class="font-semibold text-slate-700">Phone 2:</span> <span class="text-slate-900">{{ $rental->renter->secondary_phone_number ?: 'Not provided' }}</span></p>
                        <p><span class="font-semibold text-slate-700">Program:</span> <span class="text-slate-900">{{ $rental->renter->course ?: 'Not provided' }}</span></p>
                        <p><span class="font-semibold text-slate-700">Year Level:</span> <span class="text-slate-900">{{ $rental->renter->year_level ?: 'Not provided' }}</span></p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">Decision</h2>
                    @if ($isOwner && $rental->status === 'pending')
                        <div class="mt-4 grid gap-3">
                            <button wire:click="grantRequest" class="rounded-lg bg-gradient-to-r from-emerald-600 to-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:shadow-md">
                                Grant Request
                            </button>
                            <button wire:click="rejectRequest" class="rounded-lg bg-gradient-to-r from-rose-600 to-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:shadow-md">
                                Reject Request
                            </button>
                        </div>
                        
                    @elseif (! $isOwner)
                        <p class="mt-3 text-sm text-slate-600">Only the item owner can approve or reject this request. You can monitor updates here.</p>
                    @else
                        <p class="mt-3 text-sm text-slate-600">Request already processed. The requester has been notified automatically.</p>
                    @endif
                    @if($decisionNotice)
                        <x-floating-action-notice
                            :message="$decisionNotice"
                            :tone="str_contains($decisionNotice, 'rejected') ? 'danger' : 'success'"
                            wire:key="decision-notice-{{ $noticeToken }}"
                        />
                    @endif
                </div>

                @if (! $isOwner)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">Owner</h2>
                                <p class="mt-1 text-sm text-slate-600">{{ $rental->item->user->name }}</p>
                            </div>
                            <button wire:click="openUserReportForm({{ $rental->item->user_id }})" class="rounded-md border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                                Report User
                            </button>
                        </div>
                    </div>
                @endif
                
                @if ($isOwner)
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="mt-1 grid gap-3">
                                <a href="{{ route('lister.messages', ['rental' => $rental->id]) }}" class="inline-flex w-full items-center justify-center rounded-md border border-blue-200 px-4 py-2 text-m font-semibold text-blue-700 transition hover:bg-blue-50 dark:border-blue-900/60 dark:text-blue-200 dark:hover:bg-blue-900/30">
                                    Open Chat
                                </a>
                                <a href="{{ route('lister.payments', ['filter' => $rental->payment_status]) }}#payment-{{ $rental->id }}" class="inline-flex w-full items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-m font-semibold text-white transition hover:bg-blue-700">
                                    Add Payment
                                </a>
                            </div>
                        </div>
                    @else (! $isOwner)
                        <div class="mt-1 grid gap-3">
                            <a href="{{ route('renter.messages', ['rental' => $rental->id]) }}" class="inline-flex w-full items-center justify-center rounded-md border border-blue-200 px-4 py-2 text-m font-semibold text-blue-700 transition hover:bg-blue-50 dark:border-blue-900/60 dark:text-blue-200 dark:hover:bg-blue-900/30">
                                Open Chat
                            </a>
                        </div>
                    @endif
                </div>

                
            </div>
        </div>
    </div>
</div>
