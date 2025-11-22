<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return redirect()->intended('/dashboard/admin');
        }

        if ($user->role === 'sales') {
            return redirect()->intended('/dashboard/sales');
        }

        return redirect()->intended('/dashboard');
    }
}
