<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $portal = $request->session()->pull('login_portal');

        $home = match (true) {
            $request->user()?->isAdministrator() => route('admin.dashboard'),
            $portal === 'lister' => route('lister.dashboard'),
            default => route('renter.dashboard'),
        };

        $request->session()->put('active_portal', $portal === 'lister' ? 'lister' : 'renter');

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended($home);
    }
}
