<?php

namespace App\Actions\Fortify;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Sales;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'no_hp' => ['required' , 'string' , 'max:20'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'role' => ['required' , 'string' , 'max:20'],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'no_hp' => $input['no_hp'],
            'email' => $input['email'],
            'role' => $input['role'],
            'password' => Hash::make($input['password']),
        ]);

         // 🔹 Tambahkan ini untuk buat record sales/admin otomatis
        if ($user->role === 'sales') {
            Sales::create([
                'user_id' => $user->id,
                'komisi' => 0,
            ]);
        }

        if ($user->role === 'admin') {
            Admin::create([
                'user_id' => $user->id,
            ]);
        }

        // Buat record Sales/Admin
        if ($user->role === 'sales') {
            Sales::create(['user_id' => $user->id, 'komisi' => 0]);
        }
        if ($user->role === 'admin') {
            Admin::create(['user_id' => $user->id]);
        }

        return $user;
    }
}
