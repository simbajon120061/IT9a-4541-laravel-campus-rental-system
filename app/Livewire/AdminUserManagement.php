<?php

namespace App\Livewire;

use App\Models\User;
use App\Notifications\AccountRestrictedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AdminUserManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public string $role = '';

    public ?int $viewingUserId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRole(): void
    {
        $this->resetPage();
    }

    public function toggleStudentVerification(int $userId): void
    {
        abort_unless(Auth::user()?->isAdministrator(), 403);

        $user = User::query()->withTrashed()->findOrFail($userId);

        if ($user->trashed()) {
            session()->flash('message', 'Deactivated accounts cannot be verified.');

            return;
        }

        $user->update(['is_verified_student' => ! $user->is_verified_student]);

        session()->flash('message', 'Student verification updated.');
    }

    public function viewUserDetails(int $userId): void
    {
        abort_unless(Auth::user()?->isAdministrator(), 403);

        User::query()->withTrashed()->findOrFail($userId);
        $this->viewingUserId = $userId;
    }

    public function closeUserDetails(): void
    {
        $this->viewingUserId = null;
    }

    public function restrictUser(int $userId): void
    {
        abort_unless(Auth::user()?->isAdministrator(), 403);

        $user = User::query()->withTrashed()->findOrFail($userId);

        if ($user->trashed() || $user->isAdministrator() || (int) $user->id === (int) Auth::id()) {
            session()->flash('message', 'This account cannot be restricted.');

            return;
        }

        $user->forceFill([
            'restricted_at' => now(),
            'restricted_by' => Auth::id(),
        ])->save();

        $user->notify(new AccountRestrictedNotification);

        session()->flash('message', 'Account restricted.');
    }

    public function revokeRestriction(int $userId): void
    {
        abort_unless(Auth::user()?->isAdministrator(), 403);

        $user = User::query()->withTrashed()->findOrFail($userId);

        if ($user->trashed() || $user->isAdministrator()) {
            session()->flash('message', 'This account restriction cannot be changed.');

            return;
        }

        $user->forceFill([
            'restricted_at' => null,
            'restricted_by' => null,
        ])->save();

        session()->flash('message', 'Account restriction revoked.');
    }

    public function render(): View
    {
        $usersQuery = User::query()
            ->withTrashed()
            ->withCount(['items', 'rentals'])
            ->when($this->search !== '', function (Builder $query): void {
                $search = '%'.trim($this->search).'%';

                $query->where(fn (Builder $query) => $query
                    ->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('student_id', 'like', $search));
            })
            ->when($this->role === 'admins', fn (Builder $query) => $query->where('is_admin', true))
            ->when($this->role === 'students', fn (Builder $query) => $query->where('is_admin', false))
            ->latest();

        $viewingUser = $this->viewingUserId
            ? User::query()
                ->withTrashed()
                ->withCount(['items', 'rentals', 'reportsMade', 'reportsReceived'])
                ->find($this->viewingUserId)
            : null;

        return view('livewire.admin-user-management', [
            'users' => $usersQuery->paginate(15),
            'viewingUser' => $viewingUser,
        ]);
    }
}
