<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function install()
    {
        // ====== KODE RAHASIA ======
        $secretKey = 'NALENDRASUPERADMIN123'; // Ubah sesuai keinginanmu
        if (request('key') !== $secretKey) {
            return 'ACCESS DENIED';
        }
        // ===========================

        // Cek kalau sudah ada admin
        if (User::where('email', 'superadmin@gmail.com')->exists()) {
            return 'Superadmin sudah ada! Hapus route ini sekarang.';
        }

        // Buat user default
        $user = User::create([
            'name' => 'Super Admin',
            'no_hp' => '0800000000',
            'email' => 'superadmin@gmail.com',
            'role' => 'admin',
            'password' => Hash::make('admin123456'),
        ]);

        Admin::create([
            'user_id' => $user->id,
        ]);

        return 'Superadmin berhasil dibuat! Segera hapus route ini demi keamanan.';
    }
}
