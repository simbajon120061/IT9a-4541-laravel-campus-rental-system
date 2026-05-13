<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">User Management</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Review users, warnings, and account activity.</p>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950 dark:text-emerald-100">{{ session('message') }}</div>
    @endif

    <div class="mb-6 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row dark:border-slate-700 dark:bg-slate-900">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search name, email, or student ID..." class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
        <select wire:model.live="role" class="w-full rounded-lg border-slate-300 text-sm sm:w-44 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            <option value="">All Users</option>
            <option value="students">Students</option>
            <option value="admins">Admins</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="overflow-hidden">
            <table class="block min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700 lg:table">
                <thead class="hidden bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400 lg:table-header-group">
                    <tr>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Date Registered</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Warnings</th>
                        <th class="px-5 py-3">Items</th>
                        <th class="px-5 py-3">Rentals</th>
                        <th class="px-5 py-3">Verified</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="block divide-y divide-slate-200 dark:divide-slate-700 lg:table-row-group">
                    @forelse ($users as $user)
                        <tr wire:key="admin-user-{{ $user->id }}" class="block p-4 lg:table-row lg:p-0">
                            <td class="block py-2 lg:table-cell lg:px-5 lg:py-4">
                                <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 lg:hidden">User</p>
                                <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $user->name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</div>
                            </td>
                            <td class="block py-2 lg:table-cell lg:px-5 lg:py-4">
                                <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 lg:hidden">Role</p>
                                {{ $user->isAdministrator() ? 'Admin' : 'Student' }}
                            </td>
                            <td class="block py-2 lg:table-cell lg:px-5 lg:py-4">
                                <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 lg:hidden">Date Registered</p>
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $user->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->created_at->format('g:i A') }}</div>
                            </td>
                            <td class="block py-2 lg:table-cell lg:px-5 lg:py-4">
                                <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 lg:hidden">Status</p>
                                <span class="rounded-lg px-3 py-1.5 text-xs font-semibold
                                    @if ($user->trashed()) bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300
                                    @elseif ($user->isRestricted()) bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200
                                    @else bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200 @endif">
                                    {{ $user->accountStatusLabel() }}
                                </span>
                            </td>
                            <td class="block py-2 lg:table-cell lg:px-5 lg:py-4"><span class="font-semibold text-slate-400 lg:hidden">Warnings: </span>{{ $user->warning_count }}</td>
                            <td class="block py-2 lg:table-cell lg:px-5 lg:py-4"><span class="font-semibold text-slate-400 lg:hidden">Items: </span>{{ $user->items_count }}</td>
                            <td class="block py-2 lg:table-cell lg:px-5 lg:py-4"><span class="font-semibold text-slate-400 lg:hidden">Rentals: </span>{{ $user->rentals_count }}</td>
                            <td class="block py-2 lg:table-cell lg:px-5 lg:py-4">
                                <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 lg:hidden">Verified</p>
                                @if ($user->isAdministrator())
                                    <span class="text-xs font-semibold text-slate-500">Admin</span>
                                @else
                                    <button wire:click="toggleStudentVerification({{ $user->id }})" class="rounded-lg px-3 py-1.5 text-xs font-semibold {{ $user->is_verified_student ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $user->is_verified_student ? 'Verified' : 'Unverified' }}
                                    </button>
                                @endif
                            </td>
                            <td class="block pt-3 lg:table-cell lg:px-5 lg:py-4 lg:text-right">
                                <div class="flex flex-wrap gap-2 lg:justify-end">
                                    <button wire:click="viewUserDetails({{ $user->id }})" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:text-blue-700 dark:border-slate-700 dark:text-slate-200">
                                        View Details
                                    </button>

                                    @if (! $user->isAdministrator() && ! $user->trashed())
                                        @if ($user->isRestricted())
                                            <button wire:click="revokeRestriction({{ $user->id }})" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                                Revoke
                                            </button>
                                        @else
                                            <button wire:click="restrictUser({{ $user->id }})" class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-600">
                                                Restrict
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-5 py-10 text-center text-slate-500">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>

    @if ($viewingUser)
        <x-dialog-modal wire:model.live="viewingUserId">
            <x-slot name="title">
                User Details
            </x-slot>

            <x-slot name="content">
                <div class="space-y-5">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ $viewingUser->name }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $viewingUser->email }}</p>
                    </div>

                    <dl class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="text-xs font-semibold uppercase text-slate-500">Status</dt>
                            <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $viewingUser->accountStatusLabel() }}</dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="text-xs font-semibold uppercase text-slate-500">Role</dt>
                            <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $viewingUser->isAdministrator() ? 'Admin' : 'Student' }}</dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="text-xs font-semibold uppercase text-slate-500">Date Registered</dt>
                            <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $viewingUser->created_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="text-xs font-semibold uppercase text-slate-500">Student ID</dt>
                            <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $viewingUser->student_id ?: 'Not provided' }}</dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="text-xs font-semibold uppercase text-slate-500">Phone</dt>
                            <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $viewingUser->phone_number ?: 'Not provided' }}</dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="text-xs font-semibold uppercase text-slate-500">Program</dt>
                            <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $viewingUser->course ?: 'Not provided' }}</dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="text-xs font-semibold uppercase text-slate-500">Warnings</dt>
                            <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $viewingUser->warning_count }}</dd>
                        </div>
                        <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <dt class="text-xs font-semibold uppercase text-slate-500">Activity</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $viewingUser->items_count }} items, {{ $viewingUser->rentals_count }} rentals</dd>
                        </div>
                    </dl>

                    @if ($viewingUser->restricted_at)
                        <p class="rounded-lg bg-amber-50 p-3 text-sm font-medium text-amber-900 dark:bg-amber-900/30 dark:text-amber-100">
                            Restricted since {{ $viewingUser->restricted_at->format('M d, Y g:i A') }}.
                        </p>
                    @endif

                    @if ($viewingUser->trashed())
                        <p class="rounded-lg bg-slate-100 p-3 text-sm font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            Deactivated on {{ $viewingUser->deleted_at?->format('M d, Y g:i A') }}.
                        </p>
                    @endif
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="closeUserDetails">
                    Close
                </x-secondary-button>
            </x-slot>
        </x-dialog-modal>
    @endif
</div>
