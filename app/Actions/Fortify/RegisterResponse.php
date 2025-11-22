<?php
namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Support\Facades\Auth;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->intended('/dashboard/admin');
        }

        if ($user->role === 'sales') {
            return redirect()->intended('/dashboard/sales');
        }

        return redirect()->intended('/dashboard');
    }
}
