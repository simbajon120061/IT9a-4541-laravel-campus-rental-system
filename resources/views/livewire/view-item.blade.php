<div class="bg-gradient-to-b from-gray-50 to-white py-8 dark:from-slate-950 dark:to-slate-900 md:py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session()->has('message'))
            <x-floating-action-notice
                :message="session('message')"
                :tone="str_contains(session('message'), 'success') || str_contains(session('message'), 'submitted') ? 'success' : 'warning'"
                wire:key="item-session-notice-{{ $noticeToken }}"
            />
        @endif

        <a
            href="{{ $isOwner ? route('my-listings') : route('home') }}"
            class="mb-6 inline-flex items-center font-medium text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            {{ $isOwner ? 'Back to My Listings' : 'Back to Marketplace' }}
        </a>

        @if(!$isEditing)
            @if($isOwner)
                <div class="overflow-hidden rounded-2xl bg-white shadow-lg dark:bg-slate-900 dark:shadow-slate-950/40">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10 p-6 md:p-8 lg:p-10">
                        <div class="flex flex-col">
                            @if($item->imageUrl())
                                <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" class="w-full h-auto object-cover rounded-xl shadow-md">
                            @else
                                <div class="flex h-72 w-full items-center justify-center rounded-xl bg-gray-100 dark:bg-slate-800 md:h-96">
                                    <svg class="h-20 w-20 text-gray-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col justify-between">
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-6">
                                    <div>
                                        <h1 class="mb-2 text-3xl font-bold text-gray-900 dark:text-slate-100 md:text-4xl">{{ $item->name }}</h1>
                                        <p class="text-sm text-gray-600 dark:text-slate-400">Listed by {{ $item->user->name }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full
                                        @if($item->status === 'available') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200
                                        @elseif($item->status === 'rented') bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200
                                        @else bg-gray-100 text-gray-800 dark:bg-slate-800 dark:text-slate-200 @endif">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </div>

                                <div class="mb-6">
                                    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">About</h3>
                                    <p class="text-base leading-relaxed text-gray-700 dark:text-slate-300">{{ $item->description }}</p>
                                </div>

                                <div class="mb-6 rounded-xl bg-blue-50 p-6 dark:bg-blue-950/40">
                                    <span class="text-sm font-medium text-gray-600 dark:text-slate-300">Rental Price</span>
                                    <div class="flex items-baseline gap-2 mt-2">
                                        <span class="text-4xl font-bold text-blue-600 dark:text-blue-300">&#8369;{{ number_format($item->price, 2) }}</span>
                                        <span class="font-medium text-gray-600 dark:text-slate-400">per day</span>
                                    </div>
                                </div>
                            </div>

                            <button wire:click="toggleEdit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                Edit Item
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
                        <div class="space-y-4">
                            <div class="aspect-[4/3] overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800">
                                @if($item->imageUrl())
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full flex items-center justify-center">
                                        <svg class="h-20 w-20 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Item Owner</p>
                                    <button wire:click="openReportForm('user')" class="rounded-md border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 dark:border-rose-900/60 dark:text-rose-300 dark:hover:bg-rose-950/40">
                                        Report User
                                    </button>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold">
                                        {{ collect(explode(' ', trim($item->user->name)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $item->user->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Member since {{ $item->user->created_at->format('M Y') }}</p>
                                    </div>
                                </div>

                                @php
                                    $ownerDetails = collect([
                                        'Email' => $item->user->email,
                                        'Phone' => $item->user->phone_number,
                                        'Secondary phone' => $item->user->secondary_phone_number,
                                        'Student ID' => $item->user->student_id,
                                        'Department' => $item->user->department,
                                        'Course' => $item->user->course,
                                        'Year level' => $item->user->year_level,
                                    ])->filter();
                                @endphp

                                @if($ownerDetails->isNotEmpty() || filled($item->user->bio) || $item->user->isStudentVerified())
                                    <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                                        <div class="grid grid-cols-1 gap-3">
                                            @foreach($ownerDetails as $label => $value)
                                                <div>
                                                    <p class="text-[11px] font-semibold uppercase text-slate-400 dark:text-slate-500">{{ $label }}</p>
                                                    <p class="break-words text-sm text-slate-700 dark:text-slate-300">{{ $value }}</p>
                                                </div>
                                            @endforeach
                                            @if($item->user->isStudentVerified())
                                                <div>
                                                    <p class="text-[11px] font-semibold uppercase text-slate-400">Verification</p>
                                                    <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Verified student</p>
                                                </div>
                                            @endif
                                        </div>
                                        @if(filled($item->user->bio))
                                            <div class="mt-3">
                                                <p class="text-[11px] font-semibold uppercase text-slate-400 dark:text-slate-500">Bio</p>
                                                <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-300">{{ $item->user->bio }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                        </div>

                        <div class="space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ $item->name }}</h1>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $item->categoryRecord?->name ?? 'General' }}</p>
                                </div>
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold
                                    @if($item->status === 'available') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200
                                    @elseif($item->status === 'rented') bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200
                                    @else bg-gray-100 text-gray-800 dark:bg-slate-800 dark:text-slate-200 @endif">
                                    <span class="w-2.5 h-2.5 rounded-full @if($item->status === 'available') bg-green-500 @elseif($item->status === 'rented') bg-orange-500 @else bg-gray-500 @endif"></span>
                                    {{ ucfirst($item->status) }}
                                </span>
                            </div>

                            <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-300">{{ $item->description }}</p>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/60">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Condition</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $item->status === 'maintenance' ? 'Needs Attention' : 'Good' }}</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-blue-50 p-3 dark:border-blue-900/50 dark:bg-blue-950/40">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Price Per Day</p>
                                    <p class="mt-1 text-3xl font-bold text-blue-700 dark:text-blue-300">&#8369;{{ number_format($item->price, 2) }}</p>
                                </div>
                            </div>

                            @if (! $isAdmin)
                                <div class="space-y-3 rounded-lg border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-900/60 dark:bg-blue-950/30">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Request Rental</p>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="mb-1 block text-xs text-slate-600 dark:text-slate-400">Start Date</label>
                                            <input
                                                type="date"
                                                wire:model.live="startDate"
                                                min="{{ $minimumStartDate }}"
                                                class="w-full rounded-md border-slate-300 bg-white text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                            >
                                            @error('startDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs text-slate-600 dark:text-slate-400">End Date</label>
                                            <input
                                                type="date"
                                                wire:model="endDate"
                                                min="{{ $minimumEndDate }}"
                                                max="{{ $maximumEndDate }}"
                                                class="w-full rounded-md border-slate-300 bg-white text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100 disabled:text-slate-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:disabled:bg-slate-800 dark:disabled:text-slate-500"
                                            >
                                            @error('endDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-xs text-slate-600 dark:text-slate-400">Message (Optional)</label>
                                        <textarea wire:model.live="rentalMessage" maxlength="40" rows="2" class="w-full rounded-md border-slate-300 bg-white text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="Message the owner about this request."></textarea>
                                        <div class="mt-1 flex items-center justify-between gap-3 text-xs">
                                            @error('rentalMessage')
                                                <span class="font-semibold text-red-600">{{ $message }}</span>
                                            @else
                                                <span class="{{ $rentalMessageSentNotice ? 'font-semibold text-emerald-700 dark:text-emerald-300' : 'text-slate-500 dark:text-slate-400' }}">
                                                    {{ $rentalMessageSentNotice ?: 'Sent through this item conversation.' }}
                                                </span>
                                            @enderror
                                            <span class="text-slate-500 dark:text-slate-400">{{ strlen($rentalMessage) }}/40</span>
                                        </div>
                                    </div>

                                    <button
                                        wire:click="requestRental"
                                        @if($item->status !== 'available') disabled @endif
                                        class="w-full rounded-md bg-slate-900 py-2.5 text-sm font-semibold text-white hover:bg-black disabled:bg-slate-300 disabled:text-slate-500 dark:bg-blue-600 dark:hover:bg-blue-700 dark:disabled:bg-slate-800 dark:disabled:text-slate-500"
                                    >
                                        Send Rental Request
                                    </button>
                                    @if($rentalRequestNotice)
                                        <x-floating-action-notice
                                            :message="$rentalRequestNotice"
                                            :tone="str_contains($rentalRequestNotice, 'successfully') ? 'success' : 'warning'"
                                            wire:key="rental-request-notice-{{ $noticeToken }}"
                                        />
                                    @endif
                                </div>
                            @else
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-300">
                                    Admin accounts can inspect marketplace items but cannot create rental requests.
                                </div>
                            @endif

                            <div class="rounded-lg border border-rose-200 bg-rose-50/50 p-4 dark:border-rose-900/60 dark:bg-rose-950/20">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Report this listing</p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400">Admins verify each report before taking action.</p>
                                    </div>
                                    <button wire:click="openReportForm('item')" class="rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                                        Report Item
                                    </button>
                                </div>
                            </div>

                            @if($showReportForm)
                                <div class="rounded-lg border border-rose-200 bg-white p-4 shadow-sm dark:border-rose-900/60 dark:bg-slate-950">
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                            Report {{ $reportType === 'item' ? 'Item' : 'User' }}
                                        </p>
                                        <button wire:click="cancelReport" class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                                            Cancel
                                        </button>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="mb-1 block text-xs text-slate-600 dark:text-slate-400">Reason</label>
                                            <input type="text" wire:model="reportReason" maxlength="120" class="w-full rounded-md border-slate-300 bg-white text-sm text-slate-900 focus:border-rose-500 focus:ring-rose-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="Example: misleading listing or unsafe behavior">
                                            @error('reportReason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs text-slate-600 dark:text-slate-400">Details</label>
                                            <textarea wire:model="reportDetails" rows="3" class="w-full rounded-md border-slate-300 bg-white text-sm text-slate-900 focus:border-rose-500 focus:ring-rose-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="Share what admins should verify."></textarea>
                                            @error('reportDetails') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <button wire:click="submitReport" class="w-full rounded-md bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-black dark:bg-rose-600 dark:hover:bg-rose-700">
                                            Submit Report
                                        </button>
                                        @if($reportNotice)
                                            <x-floating-action-notice
                                                :message="$reportNotice"
                                                :tone="str_contains($reportNotice, 'submitted') ? 'success' : 'warning'"
                                                wire:key="item-report-notice-{{ $noticeToken }}"
                                            />
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                                <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
                                    <li class="flex items-start gap-2"><span class="mt-1 inline-block h-2 w-2 rounded-full bg-blue-600"></span>Safe Campus Transactions</li>
                                    <li class="flex items-start gap-2"><span class="mt-1 inline-block h-2 w-2 rounded-full bg-blue-600"></span>Meet on campus for item handover</li>
                                    <li class="flex items-start gap-2"><span class="mt-1 inline-block h-2 w-2 rounded-full bg-blue-600"></span>Check item condition before payment</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="overflow-hidden rounded-2xl bg-white p-6 shadow-lg dark:bg-slate-900 dark:shadow-slate-950/40 md:p-8 lg:p-10">
                <h2 class="mb-8 text-3xl font-bold text-gray-900 dark:text-slate-100 md:text-4xl">Edit Item</h2>

                <form wire:submit.prevent="updateItem" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="mb-3 block text-sm font-semibold text-gray-700 dark:text-slate-300">Item Name <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                wire:model="name"
                                placeholder="Enter item name"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-slate-900 transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            >
                            @error('name')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-3 block text-sm font-semibold text-gray-700 dark:text-slate-300">Description <span class="text-red-500">*</span></label>
                            <textarea
                                wire:model="description"
                                rows="4"
                                placeholder="Describe your item in detail..."
                                class="w-full resize-none rounded-lg border border-gray-300 bg-white px-4 py-3 text-slate-900 transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            ></textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-3 block text-sm font-semibold text-gray-700 dark:text-slate-300">Price per Day <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 font-semibold text-gray-500 dark:text-slate-400">&#8369;</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    wire:model="price"
                                    placeholder="0.00"
                                    class="w-full rounded-lg border border-gray-300 bg-white py-3 pl-8 pr-4 text-slate-900 transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                >
                            </div>
                            @error('price')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-3 block text-sm font-semibold text-gray-700 dark:text-slate-300">Status <span class="text-red-500">*</span></label>
                            <select
                                wire:model="status"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-slate-900 transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            >
                                <option value="available">Available</option>
                                <option value="rented">Rented</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-3 block text-sm font-semibold text-gray-700 dark:text-slate-300">Update Image (Optional)</label>
                            <div class="relative rounded-lg border-2 border-dashed border-gray-300 p-6 transition-colors hover:border-blue-500 dark:border-slate-700 dark:bg-slate-950/40">
                                <input
                                    type="file"
                                    wire:model="image"
                                    accept="image/*"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                >
                                <div class="text-center">
                                    <svg class="mx-auto mb-2 h-12 w-12 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <p class="font-medium text-gray-600 dark:text-slate-300">Click or drag image here</p>
                                    <p class="text-sm text-gray-500 dark:text-slate-400">PNG, JPG, GIF up to 1MB</p>
                                </div>
                            </div>
                            @error('image')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror

                            @if($image)
                                <div class="mt-4">
                                    <p class="mb-3 text-sm font-medium text-gray-700 dark:text-slate-300">Preview</p>
                                    <img src="{{ $image->temporaryUrl() }}" class="h-48 w-full object-cover rounded-lg shadow-md">
                                </div>
                            @elseif($item->imageUrl())
                                <div class="mt-4">
                                    <p class="mb-3 text-sm font-medium text-gray-700 dark:text-slate-300">Current Image</p>
                                    <img src="{{ $item->imageUrl() }}" class="h-48 w-full object-cover rounded-lg shadow-md">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 dark:border-slate-800 sm:flex-row">
                        <button
                            type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200"
                        >
                            Save Changes
                        </button>
                        <button
                            type="button"
                            wire:click="toggleEdit"
                            class="flex-1 rounded-lg bg-gray-200 px-4 py-3 font-semibold text-gray-800 transition-colors duration-200 hover:bg-gray-300 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
