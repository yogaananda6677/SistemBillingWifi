@extends('seles2.layout.master')

@section('content')
    <style>
        /* --- CSS KHUSUS HALAMAN PROFIL MOBILE --- */

        /* Header Profile dengan Gradient Kuning */
        .profile-bg-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            padding: 20px 20px 35px;
            /* Padding bawah besar untuk space avatar */
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            color: white;
            margin: -16px -16px 20px -16px;
            /* Negatif margin agar full width */
            position: relative;
        }

        /* Avatar Floating */
        .profile-avatar-container {
            margin-top: -0px;
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #fff;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: #d97706;
        }

        /* Card Styling */
        .settings-card {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            margin-bottom: 20px;
        }

        .card-title-custom {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Input Fields */
        .form-group {
            margin-bottom: 15px;
        }

        .form-label-sm {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .form-control-mobile {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            font-size: 0.95rem;
            background-color: #f9fafb;
            transition: all 0.3s;
        }

        .form-control-mobile:focus {
            background-color: #fff;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
        }

        /* Tombol Simpan */
        .btn-save {
            background: #f59e0b;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2);
        }

        .btn-save:hover,
        .btn-save:active {
            background: #d97706;
            transform: translateY(1px);
        }

        /* Tombol Logout */
        .btn-logout-mobile {
            background: #fee2e2;
            color: #ef4444;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
    </style>

    {{-- 1. HEADER BAGIAN ATAS --}}
    <div class="profile-bg-header">
        <div class="d-flex align-items-center mb-2">
            {{-- Tombol Kembali --}}
            <a href="{{ route('dashboard-sales') }}" class="text-white me-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <h5 class="mb-0 fw-bold">Pengaturan Akun</h5>
        </div>
        <p class="mb-0 opacity-75 small">Kelola informasi pribadi dan keamanan.</p>
    </div>

    <div class="container pb-5">

        {{-- 2. AVATAR USER --}}
        <div class="profile-avatar-container">
            <div class="profile-avatar">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
        </div>
        <div class="text-center mb-4">
            <h5 class="fw-bold mb-0 text-dark">{{ auth()->user()->name }}</h5>
            <span class="badge bg-warning text-dark bg-opacity-25 border border-warning">
                Sales Representative
            </span>
        </div>

        {{-- 3. FORM UPDATE PROFIL (Nama, Email, HP) --}}
        <div class="settings-card">
            <h6 class="card-title-custom">
                <i class="bi bi-person-lines-fill text-warning"></i> Data Diri
            </h6>

            <form method="POST" action="{{ route('user-profile-information.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label-sm">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                        class="form-control form-control-mobile" required>
                </div>

                <div class="form-group">
                    <label class="form-label-sm">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                        class="form-control form-control-mobile" required>
                </div>

                <div class="form-group">
                    <label class="form-label-sm">Nomor WhatsApp / HP</label>
                    {{-- Pastikan kolom 'no_hp' ada di database user Anda --}}
                    <input type="text" name="no_hp" value="{{ old('no_hp', auth()->user()->no_hp ?? '') }}"
                        class="form-control form-control-mobile" placeholder="0812xxxx">
                </div>

                <button type="submit" class="btn-save mt-2">
                    Simpan Perubahan
                </button>
            </form>
        </div>

        {{-- 4. FORM UPDATE PASSWORD --}}
        <div class="settings-card">
            <h6 class="card-title-custom">
                <i class="bi bi-shield-lock-fill text-warning"></i> Keamanan
            </h6>

            <form method="POST" action="{{ route('user-password.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label-sm">Password Saat Ini</label>
                    <input type="password" name="current_password" class="form-control form-control-mobile"
                        placeholder="•••••••">
                    @error('current_password', 'updatePassword')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label-sm">Password Baru</label>
                    <input type="password" name="password" class="form-control form-control-mobile"
                        placeholder="Minimal 8 karakter">
                    @error('password', 'updatePassword')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label-sm">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="form-control form-control-mobile"
                        placeholder="Ulangi password baru">
                </div>

                <button type="submit" class="btn-save mt-2">
                    Ganti Password
                </button>
            </form>
        </div>

        {{-- 5. TOMBOL LOGOUT --}}
        <div class="mb-5">
            <button type="button" class="btn-logout-mobile" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="bi bi-box-arrow-right"></i> Keluar Aplikasi
            </button>
            <p class="text-center text-muted small mt-3 mb-0">Versi Aplikasi 1.0.0</p>
        </div>

    </div>
@endsection
