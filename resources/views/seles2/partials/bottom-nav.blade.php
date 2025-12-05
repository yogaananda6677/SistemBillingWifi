{{-- Bottom Navigation Menu --}}
<nav class="bottom-nav fixed-bottom">
    <div class="container">
        <div class="d-flex justify-content-around align-items-center py-2">

            {{-- 1. Home --}}
            <a href="{{ route('dashboard-sales') }}"
                class="nav-item {{ request()->routeIs('dashboard-sales') ? 'active' : '' }}">
                <i class="bi bi-house-door{{ request()->routeIs('dashboard-sales') ? '-fill' : '' }}"></i>
                <span>Home</span>
            </a>

            {{-- 2. Pelanggan --}}
            <a href="{{ route('seles2.pelanggan.index') }}"
                class="nav-item {{ request()->routeIs('seles2.pelanggan.*') ? 'active' : '' }}">
                <i class="bi bi-people{{ request()->routeIs('seles2.pelanggan.*') ? '-fill' : '' }}"></i>
                <span>Pelanggan</span>
            </a>

            {{-- 3. TOMBOL TENGAH: PEMBAYARAN (Input Data) --}}
            <a href="{{ route('seles2.tagihan.index') }}"
                class="nav-item-center {{ request()->routeIs('seles2.tagihan.*') ? 'active' : '' }}">
                <div class="center-icon-wrapper">
                    {{-- Icon Pencil Square: Simbol Input Manual --}}
                    <i class="bi bi-pencil-square"></i>
                </div>
                <span>Pembayaran</span>
            </a>
<a href="{{ route('seles2.setoran.index') }}"
    class="nav-item {{ request()->routeIs('seles2.setoran.*') ? 'active' : '' }}">
    <i class="bi bi-cash-stack"></i>
    <span>Setoran</span>
</a>

            {{-- 4. Pembukuan --}}
            <a href="{{ route('seles2.pembukuan.index') }}"
                class="nav-item {{ request()->routeIs('seles2.pembukuan.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i>
                <span>Buku</span>
            </a>


        </div>
    </div>
</nav>

<style>
    .bottom-nav {
        background: #ffffff;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        padding-bottom: env(safe-area-inset-bottom);
        box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.05);
        border-top: 1px solid #f3f4f6;
        z-index: 1040;
    }

    /* Style Item Biasa */
    .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #94a3b8;
        padding: 5px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 500;
        min-width: 60px;
    }

    .nav-item i {
        font-size: 1.3rem;
        margin-bottom: 2px;
        transition: transform 0.2s;
    }

    /* State Active */
    .nav-item.active {
        color: #d97706;
        /* Amber Dark */
    }

    .nav-item.active i {
        transform: translateY(-2px);
    }

    /* --- STYLE TOMBOL TENGAH (PEMBAYARAN) --- */
    .nav-item-center {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #94a3b8;
        font-size: 0.7rem;
        font-weight: 600;
        margin-top: -15px;
        /* Naik sedikit saja (sebelumnya -25px) */
    }

    .center-icon-wrapper {
        width: 42px;
        /* UKURAN DIPERKECIL (sebelumnya 50px) */
        height: 42px;
        /* UKURAN DIPERKECIL */
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-radius: 50%;
        /* Bulat Sempurna */
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
        /* Icon disesuaikan */
        box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
        border: 3px solid #ffffff;
        /* Border putih pemisah */
        margin-bottom: 3px;
        transition: all 0.3s ease;
    }

    /* Efek Active Tombol Tengah */
    .nav-item-center.active .center-icon-wrapper {
        transform: scale(1.05);
        box-shadow: 0 6px 15px rgba(245, 158, 11, 0.4);
    }

    .nav-item-center.active span {
        color: #d97706;
    }
</style>
