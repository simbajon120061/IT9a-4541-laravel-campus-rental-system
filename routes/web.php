<?php

use App\Livewire\AddNewItem;
use App\Livewire\AdminDashboard;
use App\Livewire\AdminMarketplace;
use App\Livewire\AdminReportsComplaints;
use App\Livewire\AdminUserManagement;
use App\Livewire\Dashboard;
use App\Livewire\EditItem;
use App\Livewire\HomePage;
use App\Livewire\ListerDashboard;
use App\Livewire\ListerPayments;
use App\Livewire\ListerRentalLogs;
use App\Livewire\ListerRentalRequests;
use App\Livewire\MessagesIndex;
use App\Livewire\MyListings;
use App\Livewire\MyRentals;
use App\Livewire\OwnerItemRentalRequests;
use App\Livewire\OwnerRentalRequestView;
use App\Livewire\RenterDashboard;
use App\Livewire\RentInventoryManagement;
use App\Livewire\ViewItem;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Landing page for guests
Route::get('/', function () {
    if (auth()->guard('sanctum')->check()) {
        return redirect()->route('home');
    }

    return view('landing');
})->name('landing');

// Home page (accessible to both auth and guests)
Route::get('/marketplace', HomePage::class)->name('home');
Route::get('/categories/{category:slug}', HomePage::class)->name('categories.show');
Route::view('/help-center', 'help-center')->name('help-center');
Route::view('/login-options', 'auth.login-options')->name('login.options');
Route::get('/terms-of-service', function () {
    return view('terms', [
        'terms' => Str::markdown(file_get_contents(resource_path('markdown/terms.md'))),
    ]);
})->name('terms.show');
Route::get('/privacy-policy', function () {
    return view('policy', [
        'policy' => Str::markdown(file_get_contents(resource_path('markdown/policy.md'))),
    ]);
})->name('policy.show');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'not_restricted',
])->group(function () {
    Route::view('/choose-portal', 'auth.continue-options')->name('portal.choose');
    Route::post('/continue-as/{portal}', function (string $portal) {
        abort_unless(in_array($portal, ['lister', 'renter'], true), 404);

        session()->put('active_portal', $portal);

        return redirect()->route($portal === 'lister' ? 'lister.dashboard' : 'renter.dashboard');
    })->name('portal.continue');

    Route::get('/dashboard', RenterDashboard::class)->name('dashboard');
    Route::get('/renter/dashboard', RenterDashboard::class)->name('renter.dashboard');
    Route::get('/renter/marketplace', HomePage::class)->name('renter.marketplace');
    Route::get('/renter/my-rentals', MyRentals::class)->name('renter.my-rentals');
    Route::get('/renter/messages', MessagesIndex::class)->name('renter.messages');

    Route::get('/lister/dashboard', ListerDashboard::class)->name('lister.dashboard');
    Route::get('/lister/my-listings', MyListings::class)->name('lister.my-listings');
    Route::get('/lister/inventory', RentInventoryManagement::class)->name('lister.inventory');
    Route::get('/lister/rental-requests', ListerRentalRequests::class)->name('lister.rental-requests');
    Route::get('/lister/rental-logs', ListerRentalLogs::class)->name('lister.rental-logs');
    Route::get('/lister/payments', ListerPayments::class)->name('lister.payments');
    Route::get('/lister/messages', MessagesIndex::class)->name('lister.messages');

    Route::get('/my-listings', MyListings::class)->name('my-listings');
    Route::get('/legacy-dashboard', Dashboard::class)->name('dashboard.legacy');
    Route::get('/rent-inventory-management', RentInventoryManagement::class)->name('rent-inventory-management');
    Route::get('/items/{item}/rental-requests', OwnerItemRentalRequests::class)->name('rental-requests.item');
    Route::get('/rental-requests/{rental}', OwnerRentalRequestView::class)->name('rental-requests.show');
    Route::get('/add-item', AddNewItem::class)->name('add-item');
    Route::get('/my-rentals', MyRentals::class)->name('my-rentals');

    Route::get('/item/{id}', ViewItem::class)->name('item.view');
    Route::get('/item/{item}/edit', EditItem::class)->name('edit-item');

    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/dashboard', AdminDashboard::class)->name('admin.dashboard');
        Route::get('/admin/marketplace', AdminMarketplace::class)->name('admin.marketplace');
        Route::get('/admin/users', AdminUserManagement::class)->name('admin.users');
        Route::get('/admin/reports', AdminReportsComplaints::class)->name('admin.reports');
    });
});
