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
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #3a0ca3;
            --success: #4cc9f0;
            --warning: #f72585;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
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
        
        .stat-icon.primary { background: var(--primary-light); color: var(--primary); }
        .stat-icon.success { background: rgba(76, 201, 240, 0.2); color: var(--success); }
        .stat-icon.warning { background: rgba(247, 37, 133, 0.2); color: var(--warning); }
        .stat-icon.info { background: rgba(58, 12, 163, 0.1); color: var(--secondary); }
        
        .stat-value {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 2px;
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 20px 12px;
            text-align: center;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .menu-item:active {
            transform: scale(0.95);
            background: rgba(255, 255, 255, 1);
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
        
        .menu-icon.primary { background: var(--primary-light); color: var(--primary); }
        .menu-icon.success { background: rgba(76, 201, 240, 0.1); color: var(--success); }
        .menu-icon.warning { background: rgba(247, 37, 133, 0.1); color: var(--warning); }
        .menu-icon.secondary { background: rgba(58, 12, 163, 0.1); color: var(--secondary); }
        
        .menu-label {
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.2;
        }
        
        /* Menu Sections */
        .menu-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
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
            background: var(--primary-light);
            color: var(--primary);
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
            background: rgba(0,0,0,0.05);
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .item-icon.primary { background: var(--primary-light); color: var(--primary); }
        .item-icon.success { background: rgba(76, 201, 240, 0.1); color: var(--success); }
        .item-icon.warning { background: rgba(247, 37, 133, 0.1); color: var(--warning); }
        
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
            background: rgba(255, 255, 255, 0.9);
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
        }
        
        /* Pelanggan List */
        .pelanggan-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .pelanggan-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .pelanggan-avatar {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .pelanggan-info {
            flex: 1;
        }
        
        .pelanggan-nama {
            font-weight: 600;
            margin: 0 0 4px 0;
            font-size: 1rem;
        }
        
        .pelanggan-wilayah {
            font-size: 0.8rem;
            color: var(--gray);
            margin: 0 0 6px 0;
        }
        
        .pelanggan-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .pelanggan-status.belum-bayar {
            background: rgba(247, 37, 133, 0.1);
            color: var(--warning);
        }
        
        .pelanggan-status.sudah-bayar {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .pelanggan-action {
            color: var(--gray);
        }
        
        /* Summary Cards */
        .summary-cards {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .summary-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .summary-card i {
            font-size: 1.5rem;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .summary-card.income i { background: rgba(76, 201, 240, 0.1); color: var(--success); }
        .summary-card.fee i { background: rgba(67, 97, 238, 0.1); color: var(--primary); }
        .summary-card.expense i { background: rgba(247, 37, 133, 0.1); color: var(--warning); }
        
        .summary-label {
            font-size: 0.85rem;
            color: var(--gray);
            margin: 0;
        }
        
        .summary-value {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
        }
        
        /* Profile */
        .profile-header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 12px;
        }
        
        .profile-name {
            font-weight: 700;
            margin: 0 0 4px 0;
        }
        
        .profile-role {
            color: var(--primary);
            font-weight: 600;
            margin: 0 0 4px 0;
        }
        
        .profile-email {
            color: var(--gray);
            margin: 0;
            font-size: 0.9rem;
        }
        
        .item-content {
            flex: 1;
        }
        
        .item-desc {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 2px;
        }
    </style>
</head>
<body>
    @include('seles2.partials.mobile-header')
    @include('seles2.partials.flash-message')
    
    <main class="sales-container">
        @yield('content')
    </main>
    
    @include('seles2.partials.bottom-nav')
    @include('seles2.partials.mobile-menu-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Bottom nav active state
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    navItems.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Menu item active state
            const menuItems = document.querySelectorAll('.menu-item, .menu-list-item, .stat-card');
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    if (this.getAttribute('href')) {
                        this.style.transform = 'scale(0.95)';
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