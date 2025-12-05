@extends('layouts.master')

@section('content')
    {{-- Load SweetAlert2 CDN (Wajib ada untuk modal) --}}


    <style>
        /* --- CSS UTAMA --- */

        /* GANTI WARNA: Gradient Kuning Emas */
        .profile-header-bg {
            background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);
            height: 120px;
            border-radius: 15px 15px 0 0;
        }

        .user-avatar-wrapper {
            margin-top: -60px;
            display: flex;
            justify-content: center;
        }

        .user-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid #fff;
            background-color: #ffffbeb;
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .admin-card {
            background: #fff;
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.03);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .admin-card-header {
            background: #fff;
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-card-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .admin-card-body {
            padding: 25px;
        }

        .form-label-custom {
            font-weight: 600;
            color: #4b5563;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .form-control-custom {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 10px 15px;
            font-size: 14px;
            transition: all 0.3s;
        }

        /* GANTI WARNA: Focus Border Kuning */
        .form-control-custom:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
        }

        /* GANTI WARNA: Tombol Kuning */
        .btn-primary-custom {
            background: #f59e0b;
            /* Amber-500 */
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            color: #fff;
            transition: all 0.3s;
        }

        .btn-primary-custom:hover {
            background: #d97706;
            /* Amber-600 (Lebih gelap saat hover) */
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
        }

        .user-meta-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .user-meta-item:last-child {
            border-bottom: none;
        }

        .user-meta-label {
            color: #6b7280;
            font-size: 14px;
        }

        .user-meta-value {
            font-weight: 600;
            color: #111827;
            font-size: 14px;
        }

        /* GANTI WARNA: Icon Kuning */
        .icon-header {
            width: 20px;
            height: 20px;
            stroke: #f59e0b;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row">

            {{-- KOLOM KIRI: KARTU PROFIL --}}
            <div class="col-lg-4 col-md-5">
                <div class="admin-card">
                    <div class="profile-header-bg"></div>

                    <div class="admin-card-body pt-0 text-center">
                        <div class="user-avatar-wrapper">
                            {{-- GANTI WARNA: Parameter background diganti ke hex kuning (f59e0b) tanpa pagar --}}
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=f59e0b&color=fff&size=128"
                                alt="User Avatar" class="user-avatar">
                        </div>

                        <h4 class="mt-3 mb-1 font-weight-bold text-dark">{{ auth()->user()->name }}</h4>
                        <span class="badge bg-warning text-dark mb-4">Administrator</span>

                        <div class="text-start mt-2">
                            <div class="user-meta-item">
                                <span class="user-meta-label">Email</span>
                                <span class="user-meta-value">{{ auth()->user()->email }}</span>
                            </div>
                            <div class="user-meta-item">
                                <span class="user-meta-label">Bergabung</span>
                                <span class="user-meta-value">{{ auth()->user()->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="user-meta-item">
                                <span class="user-meta-label">Status</span>
                                <span class="text-success font-weight-bold" style="font-size: 14px;">● Aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: FORMULIR --}}
            <div class="col-lg-8 col-md-7">

                {{-- CARD 1: EDIT INFORMASI --}}
                <div class="admin-card">
                    <div class="admin-card-header">
                        <svg class="icon-header" fill="none" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <h5 class="admin-card-title">Edit Informasi Profil</h5>
                    </div>
                    <div class="admin-card-body">
                        <form method="POST" action="{{ route('user-profile-information.update') }}">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label-custom">Nama Lengkap</label>
                                    <input type="text" name="name" value="{{ auth()->user()->name }}"
                                        class="form-control form-control-custom" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label-custom">Alamat Email</label>
                                    <input type="email" name="email" value="{{ auth()->user()->email }}"
                                        class="form-control form-control-custom" required>
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="submit" class="btn-primary-custom">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- CARD 2: GANTI PASSWORD --}}
                <div class="admin-card">
                    <div class="admin-card-header">
                        {{-- Icon kunci tetap merah (danger) atau bisa diganti kuning jika mau --}}
                        <svg class="icon-header" fill="none" stroke-width="2" viewBox="0 0 24 24"
                            style="stroke: #ef4444;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <h5 class="admin-card-title">Keamanan & Password</h5>
                    </div>
                    <div class="admin-card-body">
                        <form method="POST" action="{{ route('user-password.update') }}">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label-custom">Password Saat Ini</label>
                                    <input type="password" name="current_password" class="form-control form-control-custom"
                                        placeholder="Masukkan password lama" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label-custom">Password Baru</label>
                                    <input type="password" name="password" class="form-control form-control-custom"
                                        placeholder="Minimal 8 karakter" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label-custom">Konfirmasi Password</label>
                                    <input type="password" name="password_confirmation"
                                        class="form-control form-control-custom" placeholder="Ulangi password baru"
                                        required>
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                {{-- Tombol update password tetap hijau (standard UI) atau ganti kuning jika mau --}}
                                <button type="submit" class="btn-primary-custom" style="background: #22c55e;">Update
                                    Password</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- SCRIPT UNTUK MENAMPILKAN MODAL --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // 1. Cek Session Status (Sukses) dari Laravel
            @if (session('status'))
                let message = "{{ session('status') }}";
                // Translate default Laravel Fortify message jika perlu
                if (message == 'profile-information-updated') {
                    message = 'Profil berhasil diperbarui!';
                } else if (message == 'password-updated') {
                    message = 'Password berhasil diubah!';
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: message,
                    confirmButtonColor: '#f59e0b', // Warna tombol modal sesuai tema kuning
                    timer: 3000
                });
            @endif

            // 2. Cek Error Validasi (Gagal)
            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: '<ul style="text-align: left;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                    confirmButtonColor: '#d33'
                });
            @endif
        });
    </script>
@endsection
