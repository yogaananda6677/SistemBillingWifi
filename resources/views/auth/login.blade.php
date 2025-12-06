<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nalendra ISP</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

        /* Dekorasi Background (Tema Kuning) */
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            z-index: -1;
            opacity: 0.6;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            background: var(--nalendra-gold); /* Kuning */
            top: -10%;
            left: -10%;
        }

        .shape-2 {
            width: 350px;
            height: 350px;
            background: #fd7e14; /* Oranye lembut sebagai aksen */
            bottom: -10%;
            right: -10%;
        }

        /* Card Login */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border-top: 4px solid var(--nalendra-gold); /* Aksen garis atas */
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            /* Gradasi Kuning */
            background: linear-gradient(135deg, #ffc107, #ffca2c);
            color: #000; /* Ikon warna hitam agar kontras */
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3);
        }

        .title-text {
            font-weight: 800;
            color: #111827;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }

        .subtitle-text {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 30px;
        }

        /* Form Styling */
        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            font-size: 15px;
            background-color: #f9fafb;
            transition: all 0.2s;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: var(--nalendra-gold);
            box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.2); /* Glow Kuning */
        }

        /* Tombol Kuning Nalendra */
        .btn-login {
            background: var(--nalendra-gold);
            color: #000;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            width: 100%;
            margin-top: 10px;
            transition: all 0.2s;
            box-shadow: 0 4px 6px rgba(255, 193, 7, 0.2);
        }

        .btn-login:hover {
            background: var(--nalendra-gold-hover);
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(255, 193, 7, 0.3);
        }

        /* Checkbox Kuning */
        .form-check-input:checked {
            background-color: var(--nalendra-gold);
            border-color: var(--nalendra-gold);
        }
        .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
            border-color: var(--nalendra-gold);
        }

        .forgot-link {
            font-size: 13px;
            color: #d9a406; /* Kuning Gelap */
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-link:hover {
            text-decoration: underline;
            color: #b38600;
        }
    </style>
</head>

<body>

    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <div class="container d-flex justify-content-center">
        <div class="login-card">
            <div class="d-flex flex-column align-items-center text-center">
                <div class="brand-logo">
                    <i class="bi bi-wifi"></i>
                </div>
                <h3 class="title-text">Login Administrator</h3>
                <p class="subtitle-text">Masukkan kredensial Anda untuk mengakses sistem.</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="form-label mb-0">Password</label>
                    </div>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                        name="password" placeholder="••••••••" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label text-secondary small" for="remember">Ingat Saya</label>
                </div>

                <button type="submit" class="btn btn-login">
                    Masuk Sekarang <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="mt-4 text-center">
                <p class="small text-muted mb-0">&copy; {{ date('Y') }} Nalendra ISP System</p>
            </div>
        </div>
    </div>

</body>

</html>