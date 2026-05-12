<div class="bg-gradient-to-b from-gray-50 to-white py-8 md:py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session()->has('message'))
            <div class="mb-6 p-4 md:p-5 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-800 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ session('message') }}
                </div>
            </div>
        @endif

        <a
            href="{{ $isOwner ? route('my-listings') : route('home') }}"
            class="inline-flex items-center mb-6 text-blue-600 hover:text-blue-700 font-medium transition-colors"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            {{ $isOwner ? 'Back to My Listings' : 'Back to Marketplace' }}
        </a>

        @if(!$isEditing)
            @if($isOwner)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10 p-6 md:p-8 lg:p-10">
                        <div class="flex flex-col">
                            @if($item->imageUrl())
                                <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" class="w-full h-auto object-cover rounded-xl shadow-md">
                            @else
                                <div class="w-full h-72 md:h-96 bg-gray-100 rounded-xl flex items-center justify-center">
                                    <svg class="h-20 w-20 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col justify-between">
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-6">
                                    <div>
                                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ $item->name }}</h1>
                                        <p class="text-gray-600 text-sm">Listed by {{ $item->user->name }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full
                                        @if($item->status === 'available') bg-green-100 text-green-800
                                        @elseif($item->status === 'rented') bg-orange-100 text-orange-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </div>

                                <div class="mb-6">
                                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">About</h3>
                                    <p class="text-gray-700 text-base leading-relaxed">{{ $item->description }}</p>
                                </div>

                                <div class="bg-blue-50 rounded-xl p-6 mb-6">
                                    <span class="text-gray-600 text-sm font-medium">Rental Price</span>
                                    <div class="flex items-baseline gap-2 mt-2">
                                        <span class="text-4xl font-bold text-blue-600">&#8369;{{ number_format($item->price, 2) }}</span>
                                        <span class="text-gray-600 font-medium">per day</span>
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
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
                        <div class="space-y-4">
                            <div class="overflow-hidden rounded-lg bg-slate-100 aspect-[4/3]">
                                @if($item->imageUrl())
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full flex items-center justify-center">
                                        <svg class="h-20 w-20 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <div class="rounded-lg border border-slate-200 p-4">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-slate-700">Item Owner</p>
                                    <button wire:click="openReportForm('user')" class="rounded-md border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                                        Report User
                                    </button>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold">
                                        {{ collect(explode(' ', trim($item->user->name)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $item->user->name }}</p>
                                        <p class="text-xs text-slate-500">Member since {{ $item->user->created_at->format('M Y') }}</p>
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
                                    <div class="mt-4 border-t border-slate-100 pt-4">
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            @foreach($ownerDetails as $label => $value)
                                                <div>
                                                    <p class="text-[11px] font-semibold uppercase text-slate-400">{{ $label }}</p>
                                                    <p class="break-words text-sm text-slate-700">{{ $value }}</p>
                                                </div>
                                            @endforeach
                                            @if($item->user->isStudentVerified())
                                                <div>
                                                    <p class="text-[11px] font-semibold uppercase text-slate-400">Verification</p>
                                                    <p class="text-sm font-semibold text-emerald-700">Verified student</p>
                                                </div>
                                            @endif
                                        </div>
                                        @if(filled($item->user->bio))
                                            <div class="mt-3">
                                                <p class="text-[11px] font-semibold uppercase text-slate-400">Bio</p>
                                                <p class="text-sm leading-relaxed text-slate-700">{{ $item->user->bio }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @if (! $isAdmin)
                                <div class="rounded-lg border border-slate-200 bg-white p-4">
                                    <p class="text-sm font-semibold text-slate-800">Message Owner</p>
                                    <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                                        <div class="flex-1">
                                            <input
                                                type="text"
                                                wire:model.live="ownerMessage"
                                                maxlength="40"
                                                class="w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="Ask about this item..."
                                            >
                                            <div class="mt-1 flex items-center justify-between gap-3 text-xs">
                                                @error('ownerMessage')
                                                    <span class="font-semibold text-rose-600">{{ $message }}</span>
                                                @else
                                                    <span class="text-slate-500">Sent through this item conversation.</span>
                                                @enderror
                                                <span class="text-slate-500">{{ strlen($ownerMessage) }}/40</span>
                                            </div>
                                        </div>
                                        <button wire:click="sendOwnerMessage" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                                            Send
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h1 class="text-4xl font-bold tracking-tight text-slate-900">{{ $item->name }}</h1>
                                    <p class="text-xs text-slate-500 mt-1">{{ $item->categoryRecord?->name ?? 'General' }}</p>
                                </div>
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold
                                    @if($item->status === 'available') bg-green-100 text-green-800
                                    @elseif($item->status === 'rented') bg-orange-100 text-orange-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    <span class="w-2.5 h-2.5 rounded-full @if($item->status === 'available') bg-green-500 @elseif($item->status === 'rented') bg-orange-500 @else bg-gray-500 @endif"></span>
                                    {{ ucfirst($item->status) }}
                                </span>
                            </div>

                            <p class="text-slate-700 text-sm leading-relaxed">{{ $item->description }}</p>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <p class="text-xs text-slate-500">Condition</p>
                                    <p class="text-sm font-semibold text-slate-800 mt-1">{{ $item->status === 'maintenance' ? 'Needs Attention' : 'Good' }}</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-blue-50 p-3">
                                    <p class="text-xs text-slate-500">Price Per Day</p>
                                    <p class="text-3xl font-bold text-blue-700 mt-1">&#8369;{{ number_format($item->price, 2) }}</p>
                                </div>
                            </div>

                            @if (! $isAdmin)
                                <div class="rounded-lg border border-blue-200 bg-blue-50/50 p-4 space-y-3">
                                    <p class="text-sm font-semibold text-slate-800">Request Rental</p>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs text-slate-600 mb-1">Start Date</label>
                                            <input
                                                type="date"
                                                wire:model.live="startDate"
                                                min="{{ $minimumStartDate }}"
                                                class="w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                            >
                                            @error('startDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs text-slate-600 mb-1">End Date</label>
                                            <input
                                                type="date"
                                                wire:model="endDate"
                                                min="{{ $minimumEndDate }}"
                                                max="{{ $maximumEndDate }}"
                                                class="w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100 disabled:text-slate-500"
                                            >
                                            @error('endDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">Message (Optional)</label>
                                        <textarea wire:model.live="rentalMessage" maxlength="40" rows="2" class="w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Message the owner about this request."></textarea>
                                        <div class="mt-1 flex items-center justify-between gap-3 text-xs">
                                            @error('rentalMessage')
                                                <span class="font-semibold text-red-600">{{ $message }}</span>
                                            @else
                                                <span class="text-slate-500">Sent through this item conversation.</span>
                                            @enderror
                                            <span class="text-slate-500">{{ strlen($rentalMessage) }}/40</span>
                                        </div>
                                    </div>

                                    <button
                                        wire:click="requestRental"
                                        @if($item->status !== 'available') disabled @endif
                                        class="w-full rounded-md bg-slate-900 hover:bg-black text-white text-sm font-semibold py-2.5 disabled:bg-slate-300 disabled:text-slate-500"
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
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                                    Admin accounts can inspect marketplace items but cannot create rental requests.
                                </div>
                            @endif

                            <div class="rounded-lg border border-rose-200 bg-rose-50/50 p-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">Report this listing</p>
                                        <p class="text-xs text-slate-600">Admins verify each report before taking action.</p>
                                    </div>
                                    <button wire:click="openReportForm('item')" class="rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                                        Report Item
                                    </button>
                                </div>
                            </div>

                            @if($showReportForm)
                                <div class="rounded-lg border border-rose-200 bg-white p-4 shadow-sm">
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold text-slate-900">
                                            Report {{ $reportType === 'item' ? 'Item' : 'User' }}
                                        </p>
                                        <button wire:click="cancelReport" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
                                            Cancel
                                        </button>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs text-slate-600 mb-1">Reason</label>
                                            <input type="text" wire:model="reportReason" maxlength="120" class="w-full rounded-md border-slate-300 text-sm focus:border-rose-500 focus:ring-rose-500" placeholder="Example: misleading listing or unsafe behavior">
                                            @error('reportReason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs text-slate-600 mb-1">Details</label>
                                            <textarea wire:model="reportDetails" rows="3" class="w-full rounded-md border-slate-300 text-sm focus:border-rose-500 focus:ring-rose-500" placeholder="Share what admins should verify."></textarea>
                                            @error('reportDetails') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <button wire:click="submitReport" class="w-full rounded-md bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-black">
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

                            <div class="rounded-lg border border-slate-200 p-4">
                                <ul class="space-y-2 text-xs text-slate-600">
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
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden p-6 md:p-8 lg:p-10">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8">Edit Item</h2>

                <form wire:submit.prevent="updateItem" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Item Name <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                wire:model="name"
                                placeholder="Enter item name"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                            @error('name')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Description <span class="text-red-500">*</span></label>
                            <textarea
                                wire:model="description"
                                rows="4"
                                placeholder="Describe your item in detail..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                            ></textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Price per Day <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-gray-500 font-semibold">&#8369;</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    wire:model="price"
                                    placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                >
                            </div>
                            @error('price')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Status <span class="text-red-500">*</span></label>
                            <select
                                wire:model="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-white"
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
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Update Image (Optional)</label>
                            <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 hover:border-blue-500 transition-colors">
                                <input
                                    type="file"
                                    wire:model="image"
                                    accept="image/*"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                >
                                <div class="text-center">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <p class="text-gray-600 font-medium">Click or drag image here</p>
                                    <p class="text-gray-500 text-sm">PNG, JPG, GIF up to 1MB</p>
                                </div>
                            </div>
                            @error('image')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror

                            @if($image)
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-gray-700 mb-3">Preview</p>
                                    <img src="{{ $image->temporaryUrl() }}" class="h-48 w-full object-cover rounded-lg shadow-md">
                                </div>
                            @elseif($item->imageUrl())
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-gray-700 mb-3">Current Image</p>
                                    <img src="{{ $item->imageUrl() }}" class="h-48 w-full object-cover rounded-lg shadow-md">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
                        <button
                            type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200"
                        >
                            Save Changes
                        </button>
                        <button
                            type="button"
                            wire:click="toggleEdit"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg transition-colors duration-200"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
