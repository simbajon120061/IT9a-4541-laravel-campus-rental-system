<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);
        Fortify::loginView(function (Request $request): View {
            $portal = in_array($request->query('portal'), ['renter', 'lister'], true)
                ? $request->query('portal')
                : $request->session()->get('login_portal');

            if (in_array($portal, ['renter', 'lister'], true)) {
                $request->session()->put('login_portal', $portal);
            }

            return view('auth.login', [
                'portal' => $portal,
            ]);
        });

        Fortify::authenticateUsing(function (Request $request): ?User {
            $email = Str::lower((string) $request->input('email', ''));
            $user = User::query()->where('email', $email)->first();

            if (! $user) {
                return null;
            }

            if (! Str::endsWith($email, '@umindanao.edu.ph')) {
                throw ValidationException::withMessages([
                    'email' => 'Only University of Mindanao emails are allowed.',
                ]);
            }

            if (! Hash::check((string) $request->input('password', ''), (string) $user->password)) {
                return null;
            }

            if ($user->isRestricted()) {
                throw ValidationException::withMessages([
                    'email' => 'Your account has been restricted by an administrator. You cannot access your account until the restriction is revoked.',
                ]);
            }

            return $user;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
