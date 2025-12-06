<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nalendra ISP</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --nalendra-gold: #ffc107;
            --nalendra-gold-hover: #e0a800;
            --nalendra-dark: #212529;
        }

        body {
            font-family: 'Instrument Sans', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Dekorasi Background (Kuning) */
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.6;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            background: var(--nalendra-gold);
            top: -100px;
            right: -50px;
        }

        .shape-2 {
            width: 300px;
            height: 300px;
            background: #fd7e14; /* Oranye lembut */
            bottom: -50px;
            left: -50px;
        }

        /* Card Utama */
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            text-align: center;
            position: relative;
            margin: 20px;
            border-top: 4px solid var(--nalendra-gold);
        }

        .brand-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #ffc107, #ffca2c);
            color: #000;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 25px;
            box-shadow: 0 10px 20px rgba(255, 193, 7, 0.3);
        }

        .welcome-title {
            font-size: 26px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .welcome-subtitle {
            color: #6b7280;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 35px;
        }

        /* Tombol Custom */
        .btn-custom {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        /* Tombol Login Hitam (Kontras dengan tema kuning) */
        .btn-login {
            background: #000;
            color: #ffffffff; /* Teks Kuning */
            border: none;
        }

        .btn-login:hover {
            background: #212529;
            color: #ffca2c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        /* Tombol Dashboard (Kuning) */
        .btn-dashboard {
            background: var(--nalendra-gold);
            color: #000;
            border: none;
            margin-bottom: 12px;
        }

        .btn-dashboard:hover {
            background: var(--nalendra-gold-hover);
            color: #000;
        }

        .btn-logout {
            background: #fee2e2;
            color: #dc2626;
            border: none;
        }

        .btn-logout:hover {
            background: #fecaca;
            color: #b91c1c;
        }

        .footer-copy {
            margin-top: 30px;
            font-size: 12px;
            color: #9ca3af;
        }

        @media (max-width: 576px) {
            .welcome-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <div class="welcome-card">
        <div class="brand-icon">
            <i class="bi bi-wifi"></i>
        </div>

        <h1 class="welcome-title">Nalendra ISP</h1>
        <p class="welcome-subtitle">
            Sistem Informasi Manajemen Pelanggan.<br>
            Solusi internet cepat dan stabil untuk kebutuhan Anda.
        </p>

        @auth
            {{-- TAMPILAN JIKA SUDAH LOGIN --}}
            <div class="p-3 bg-warning bg-opacity-10 rounded-3 mb-4 border border-warning border-opacity-25">
                <p class="mb-1 text-muted small">Halo, kembali lagi!</p>
                <h5 class="fw-bold text-dark mb-0">{{ Auth::user()->name }}</h5>
                <span class="badge bg-warning text-dark mt-2">
                    {{ ucfirst(Auth::user()->role ?? 'User') }}
                </span>
            </div>

            @if (Auth::user()->role == 'admin')
                <a href="{{ url('/dashboard/admin') }}" class="btn btn-custom btn-dashboard">
                    <i class="bi bi-speedometer2 me-2"></i> Buka Dashboard
                @else
                    <a href="{{ url('/dashboard/sales') }}" class="btn btn-custom btn-dashboard">
                        <i class="bi bi-speedometer2 me-2"></i> Buka Dashboard
            @endif

            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-custom btn-logout">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </button>
            </form>
        @else
            {{-- TAMPILAN JIKA BELUM LOGIN (TAMU) --}}

            <a href="{{ route('login') }}" class="btn btn-custom btn-login">
                Masuk ke Sistem <i class="bi bi-arrow-right ms-2"></i>
            </a>

            <div class="mt-4 pt-3 border-top">
                <p class="small text-muted mb-0">
                    <i class="bi bi-info-circle me-1 text-warning"></i>
                    Akses terbatas hanya untuk karyawan & staf.
                </p>
            </div>
        @endauth

        <div class="footer-copy">
            &copy; {{ date('Y') }} Nalendra ISP. All rights reserved.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>