<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales - Nalendra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            /* WARNA UTAMA: Kuning Emas (Amber) */
            --primary: #f59e0b;
            /* Amber 500 */
            --primary-dark: #b45309;
            /* Amber 700 */
            --primary-light: #fffbeb;
            /* Amber 50 */

            /* WARNA SEKUNDER: Dark Grey (Kontras untuk tulisan di atas kuning) */
            --secondary: #1e293b;
            /* Slate 800 */

            /* Indikator Status */
            --success: #10b981;
            /* Emerald */
            --warning: #ef4444;
            /* Red (Ganti warning jadi merah karena primary sudah kuning) */

            /* Netral */
            --dark: #111827;
            --gray: #6b7280;
            --light-gray: #f3f4f6;
        }

        body {
            /* Background diganti jadi soft grey/warm white agar bersih */
            background: #f9fafb;
            min-height: 100vh;
            padding-bottom: 80px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sales-container {
            padding: 16px;
            padding-bottom: 100px;
        }

        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            border: 1px solid rgba(245, 158, 11, 0.1);
            /* Border tipis kuning */
        }

        .stat-card:active {
            transform: scale(0.95);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-size: 1.2rem;
        }

        /* Update warna icon */
        .stat-icon.primary {
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .stat-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stat-icon.warning {
            background: rgba(239, 68, 68, 0.1);
            color: var(--warning);
        }

        .stat-icon.info {
            background: #f1f5f9;
            color: var(--secondary);
        }

        .stat-value {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 2px;
            color: var(--dark);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Menu Grid */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 30px;
        }

        .menu-item {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px 12px;
            text-align: center;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
        }

        .menu-item:active {
            transform: scale(0.95);
            border-color: var(--primary);
        }

        .menu-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 1.4rem;
        }

        .menu-icon.primary {
            background: var(--primary-light);
            color: var(--primary);
        }

        .menu-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .menu-icon.warning {
            background: rgba(239, 68, 68, 0.1);
            color: var(--warning);
        }

        .menu-icon.secondary {
            background: #f1f5f9;
            color: var(--secondary);
        }

        .menu-label {
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.2;
        }

        /* Menu Sections */
        .menu-section {
            background: #ffffff;
            border-radius: 20px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
        }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Header section icon warna kuning soft */
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .section-title {
            font-weight: 600;
            color: var(--dark);
            margin: 0;
            font-size: 1.1rem;
        }

        .menu-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu-list-item {
            padding: 16px 20px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--dark);
            transition: background 0.3s ease;
        }

        .menu-list-item:last-child {
            border-bottom: none;
        }

        .menu-list-item:active {
            background: var(--primary-light);
        }

        .item-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .item-icon.primary {
            background: var(--primary-light);
            color: var(--primary);
        }

        .item-label {
            flex: 1;
            font-weight: 500;
        }

        .item-arrow {
            color: var(--gray);
        }

        .badge-new {
            background: var(--warning);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-right: 8px;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            overflow-x: auto;
            padding-bottom: 8px;
        }

        .filter-tab {
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            text-decoration: none;
            color: var(--gray);
            font-size: 0.85rem;
            font-weight: 500;
            white-space: nowrap;
            transition: all 0.3s ease;
        }

        .filter-tab.active {
            background: var(--primary);
            color: white;
            /* Text putih di atas kuning */
            border-color: var(--primary);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Profile Styling */
        .profile-header {
            background: #ffffff;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border: 1px solid #f3f4f6;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 12px;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
        }

        .profile-role {
            color: var(--primary-dark);
            font-weight: 600;
            margin: 0 0 4px 0;
        }

        /* Responsive Fixes */
        @media (min-width: 576px) {
            .sales-container {
                max-width: 600px;
                margin: 0 auto;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    {{-- Tampilkan Header Mobile hanya jika BUKAN di halaman profil --}}
    @if (request()->Is('dashboard/sales'))
        @include('seles2.partials.mobile-header')
    @endif
    @include('seles2.partials.flash-message')

    <main class="sales-container">
        @yield('content')
    </main>

    @include('seles2.partials.bottom-nav')
    @include('seles2.partials.mobile-menu-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animasi klik
            const clickableItems = document.querySelectorAll('.menu-item, .menu-list-item, .stat-card, .btn');
            clickableItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    if (!this.classList.contains('disabled')) {
                        this.style.transform = 'scale(0.97)';
                        setTimeout(() => {
                            this.style.transform = 'scale(1)';
                        }, 150);
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
