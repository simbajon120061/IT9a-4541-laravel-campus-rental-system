<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Reports & Complaints</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Resolve reports and appeals from users.</p>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950 dark:text-emerald-100">{{ session('message') }}</div>
    @endif

    <div class="mb-6 flex gap-2 rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
        <button wire:click="showTab('reports')" class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold {{ $tab === 'reports' ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-950 dark:text-slate-100' : 'text-slate-600 dark:text-slate-400' }}">Reports</button>
        <button wire:click="showTab('appeals')" class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold {{ $tab === 'appeals' ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-950 dark:text-slate-100' : 'text-slate-600 dark:text-slate-400' }}">Item Appeals</button>
    </div>

    @if ($tab === 'reports')
        <div class="mb-4 flex justify-end">
            <select wire:model.live="reportStatus" class="rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="pending">Pending</option>
                <option value="reviewed">Reviewed</option>
            </select>
        </div>

        <div class="space-y-4">
            @forelse ($reports as $report)
                @php($targetUser = $report->targetUser())
                <section wire:key="report-{{ $report->id }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start">
                            @if ($report->reportedItem)
                                <div class="h-32 w-full shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-slate-100 md:w-44 dark:border-slate-700 dark:bg-slate-800">
                                    @if ($report->reportedItem->imageUrl())
                                        <img src="{{ $report->reportedItem->imageUrl() }}" alt="{{ $report->reportedItem->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-xs font-semibold text-slate-400 dark:text-slate-500">
                                            No Image
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase text-white dark:bg-slate-100 dark:text-slate-900">{{ $report->type }}</span>
                                <span class="text-xs text-slate-500">{{ $report->created_at->format('M d, Y g:i A') }}</span>
                            </div>
                            <p class="text-sm text-slate-700 dark:text-slate-300"><span class="font-semibold">Reporter:</span> {{ $report->reporter?->name ?? 'Deleted user' }}</p>
                            <p class="text-sm text-slate-700 dark:text-slate-300"><span class="font-semibold">Target:</span> {{ $targetUser?->name ?? 'Unavailable' }}</p>
                            @if ($report->reportedItem)
                                <p class="text-sm text-slate-700 dark:text-slate-300"><span class="font-semibold">Item:</span> {{ $report->reportedItem->name }}</p>
                            @endif
                            @if ($report->reportedMessage)
                                <p class="text-sm text-slate-700 dark:text-slate-300"><span class="font-semibold">Message:</span> "{{ $report->reportedMessage->body }}"</p>
                            @endif
                            <p class="text-sm text-slate-800 dark:text-slate-200"><span class="font-semibold">Reason:</span> {{ $report->reason }}</p>
                            @if ($report->details)
                                <p class="max-w-3xl text-sm text-slate-600 dark:text-slate-400">{{ $report->details }}</p>
                            @endif
                            </div>
                        </div>

                        @if ($report->status === 'pending')
                            <div class="w-full shrink-0 space-y-3 lg:w-72">
                                <textarea wire:model="adminNotes.{{ $report->id }}" rows="2" class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="Formal message sent with this action"></textarea>
                                <div class="grid gap-2">
                                    <button wire:click="issueWarning({{ $report->id }})" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">Issue Warning</button>
                                    @if ($report->reportedItem)
                                        <button wire:click="removeItemFromReport({{ $report->id }})" class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">Remove Item</button>
                                    @endif
                                    @if ($targetUser)
                                        <button wire:click="removeAccount({{ $report->id }})" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Remove Account</button>
                                    @endif
                                    <button wire:click="dismissReport({{ $report->id }})" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">Dismiss</button>
                                </div>
                            </div>
                        @else
                            <div class="text-sm text-slate-500 dark:text-slate-400">Reviewed: {{ str_replace('_', ' ', $report->admin_action ?? 'reviewed') }}</div>
                        @endif
                    </div>
                </section>
            @empty
                <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700">No reports found.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $reports->links() }}</div>
    @else
        <div class="mb-4 flex justify-end">
            <select wire:model.live="appealStatus" class="rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div class="space-y-4">
            @forelse ($appeals as $appeal)
                <section wire:key="appeal-{{ $appeal->id }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase text-blue-800">{{ $appeal->status }}</span>
                                <span class="text-xs text-slate-500">{{ $appeal->created_at->format('M d, Y g:i A') }}</span>
                            </div>
                            <p class="text-sm text-slate-700 dark:text-slate-300"><span class="font-semibold">Item:</span> {{ $appeal->item?->name ?? 'Deleted item' }}</p>
                            <p class="text-sm text-slate-700 dark:text-slate-300"><span class="font-semibold">Owner:</span> {{ $appeal->user?->name ?? 'Deleted user' }}</p>
                            <p class="text-sm text-slate-800 dark:text-slate-200"><span class="font-semibold">Appeal reason:</span> {{ $appeal->reason }}</p>
                            @if ($appeal->details)
                                <p class="max-w-3xl text-sm text-slate-600 dark:text-slate-400">{{ $appeal->details }}</p>
                            @endif
                            @if ($appeal->item?->admin_removal_reason)
                                <p class="text-sm text-rose-700 dark:text-rose-300"><span class="font-semibold">Takedown:</span> {{ $appeal->item->admin_removal_reason }}</p>
                            @endif
                        </div>

                        @if ($appeal->status === 'pending')
                            <div class="w-full shrink-0 space-y-3 lg:w-72">
                                <textarea wire:model="adminNotes.{{ $appeal->id }}" rows="2" class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="Formal message sent with this decision"></textarea>
                                <button wire:click="approveAppeal({{ $appeal->id }})" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Approve & Restore</button>
                                <button wire:click="rejectAppeal({{ $appeal->id }})" class="w-full rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Reject Appeal</button>
                            </div>
                        @else
                            <div class="text-sm text-slate-500 dark:text-slate-400">Reviewed {{ $appeal->reviewed_at?->format('M d, Y') }}</div>
                        @endif
                    </div>
                </section>
            @empty
                <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700">No appeals found.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $appeals->links() }}</div>
    @endif
</div>
