<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        .dashboard-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1b1b18;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 30px;
        }

        .quick-action {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .quick-action:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }

        .recent-activity {
            background: #fff;
            border-radius: 8px;
            padding: 16px;
            border: 1px solid #e5e7eb;
        }

        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .activity-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-wifi"></i> Nalendra ISP
            </a>

            <div class="navbar-nav ms-auto">
                @auth
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                    @if (Route::has('register'))
                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid py-4">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="fw-bold mb-2">Selamat Datang, {{ Auth::user()->name ?? 'Pengguna' }}!</h1>
                        <p class="mb-0">
                            @auth
                                @if(Auth::user()->role === 'admin')
                                    Anda login sebagai Administrator Sistem
                                @else
                                    Anda login sebagai Sales
                                @endif
                            @else
                                Silakan login untuk mengakses sistem
                            @endauth
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="bg-white bg-opacity-20 p-3 rounded">
                            <small class="d-block">Tanggal</small>
                            <strong id="current-date">{{ date('d F Y') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number" id="total-pelanggan">0</div>
                                <div class="stat-label">Total Pelanggan</div>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-2 rounded">
                                <i class="bi bi-people-fill text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number" id="pelanggan-aktif">0</div>
                                <div class="stat-label">Pelanggan Aktif</div>
                            </div>
                            <div class="bg-success bg-opacity-10 p-2 rounded">
                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number" id="total-sales">0</div>
                                <div class="stat-label">Total Sales</div>
                            </div>
                            <div class="bg-info bg-opacity-10 p-2 rounded">
                                <i class="bi bi-person-badge-fill text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number" id="pendapatan">0</div>
                                <div class="stat-label">Pendapatan Bulan Ini</div>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-2 rounded">
                                <i class="bi bi-currency-dollar text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="row">
                <!-- Quick Actions -->
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card">
                        <h5 class="fw-bold mb-3">Aksi Cepat</h5>
                        <div class="row g-2">
                            @auth
                                @if(Auth::user()->role === 'admin')
                                    <div class="col-6">
                                        <a href="{{ route('pelanggan.create') }}" class="quick-action text-decoration-none">
                                            <i class="bi bi-person-plus fs-2 text-primary mb-2"></i>
                                            <div class="small fw-medium">Tambah Pelanggan</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('pelanggan.index') }}" class="quick-action text-decoration-none">
                                            <i class="bi bi-list-ul fs-2 text-success mb-2"></i>
                                            <div class="small fw-medium">Data Pelanggan</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="quick-action text-decoration-none">
                                            <i class="bi bi-graph-up fs-2 text-info mb-2"></i>
                                            <div class="small fw-medium">Laporan</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="quick-action text-decoration-none">
                                            <i class="bi bi-gear fs-2 text-warning mb-2"></i>
                                            <div class="small fw-medium">Pengaturan</div>
                                        </a>
                                    </div>
                                @else
                                    <div class="col-6">
                                        <a href="{{ route('pelanggan.create') }}" class="quick-action text-decoration-none">
                                            <i class="bi bi-person-plus fs-2 text-primary mb-2"></i>
                                            <div class="small fw-medium">Input Pelanggan</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('pelanggan.index') }}" class="quick-action text-decoration-none">
                                            <i class="bi bi-people fs-2 text-success mb-2"></i>
                                            <div class="small fw-medium">Pelanggan Saya</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="quick-action text-decoration-none">
                                            <i class="bi bi-cash-coin fs-2 text-info mb-2"></i>
                                            <div class="small fw-medium">Komisi</div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="quick-action text-decoration-none">
                                            <i class="bi bi-bar-chart fs-2 text-warning mb-2"></i>
                                            <div class="small fw-medium">Target</div>
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="col-12 text-center py-4">
                                    <p class="text-muted">Silakan login untuk mengakses aksi cepat</p>
                                    <a href="{{ route('login') }}" class="btn btn-primary">Login Sekarang</a>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-md-8">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Aktivitas Terbaru</h5>
                            <a href="#" class="small text-decoration-none">Lihat Semua</a>
                        </div>

                        <div class="recent-activity">
                            @auth
                                @if(Auth::user()->role === 'admin')
                                    <div class="activity-item">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                                <i class="bi bi-person-plus text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium">Pelanggan baru ditambahkan</div>
                                                <small class="text-muted">Yoga Ananda - 2 menit yang lalu</small>
                                            </div>
                                            <span class="badge bg-success">Aktif</span>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-opacity-10 p-2 rounded me-3">
                                                <i class="bi bi-cash-coin text-warning"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium">Pembayaran diterima</div>
                                                <small class="text-muted">Ahmad Fauzi - 1 jam yang lalu</small>
                                            </div>
                                            <span class="badge bg-success">Rp 250.000</span>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info bg-opacity-10 p-2 rounded me-3">
                                                <i class="bi bi-wrench text-info"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium">Maintenance jaringan</div>
                                                <small class="text-muted">Wilayah Utara - 3 jam yang lalu</small>
                                            </div>
                                            <span class="badge bg-warning">Progress</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="activity-item">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-opacity-10 p-2 rounded me-3">
                                                <i class="bi bi-check-circle text-success"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium">Pelanggan berhasil didaftarkan</div>
                                                <small class="text-muted">Siti Rahayu - 30 menit yang lalu</small>
                                            </div>
                                            <span class="badge bg-primary">Baru</span>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info bg-opacity-10 p-2 rounded me-3">
                                                <i class="bi bi-telephone text-info"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium">Follow up pelanggan</div>
                                                <small class="text-muted">Budi Santoso - 2 jam yang lalu</small>
                                            </div>
                                            <span class="badge bg-warning">Pending</span>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-opacity-10 p-2 rounded me-3">
                                                <i class="bi bi-graph-up text-warning"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium">Target penjualan tercapai</div>
                                                <small class="text-muted">75% dari target bulanan</small>
                                            </div>
                                            <span class="badge bg-success">On Track</span>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-activity fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">Login untuk melihat aktivitas terbaru</p>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Info -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="dashboard-card">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="fw-bold mb-1">Sistem Informasi Nalendra ISP</h6>
                                <p class="text-muted mb-0 small">
                                    @auth
                                        @if(Auth::user()->role === 'admin')
                                            Anda memiliki akses penuh untuk mengelola semua data pelanggan, sales, dan laporan keuangan.
                                        @else
                                            Anda dapat mengelola data pelanggan yang Anda daftarkan dan melihat progress penjualan.
                                        @endif
                                    @else
                                        Sistem manajemen pelanggan dan penjualan untuk provider internet Nalendra.
                                    @endauth
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group">
                                    <a href="{{ route('pelanggan.index') }}" class="btn btn-primary">
                                        <i class="bi bi-people me-1"></i> Kelola Pelanggan
                                    </a>
                                    @auth
                                        @if(Auth::user()->role === 'admin')
                                            <a href="#" class="btn btn-outline-primary">
                                                <i class="bi bi-graph-up me-1"></i> Dashboard Admin
                                            </a>
                                        @else
                                            <a href="#" class="btn btn-outline-primary">
                                                <i class="bi bi-person-badge me-1"></i> Dashboard Sales
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simulasi data statistik
        document.addEventListener('DOMContentLoaded', function() {
            // Update statistik
            document.getElementById('total-pelanggan').textContent = '1,247';
            document.getElementById('pelanggan-aktif').textContent = '1,089';
            document.getElementById('total-sales').textContent = '23';
            document.getElementById('pendapatan').textContent = 'Rp 287Jt';

            // Update waktu real-time
            function updateDateTime() {
                const now = new Date();
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                document.getElementById('current-date').textContent =
                    now.toLocaleDateString('id-ID', options);
            }

            updateDateTime();
            setInterval(updateDateTime, 1000);
        });
    </script>
</body>
</html>
