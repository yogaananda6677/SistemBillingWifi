{{-- Bottom Navigation Menu --}}
<nav class="bottom-nav fixed-bottom bg-white shadow-lg">
    <div class="container">
        <div class="d-flex justify-content-around py-2">
            <a href="{{ route('dashboard-sales') }}"
                class="nav-item {{ request()->routeIs('dashboard-sales') ? 'active' : '' }}">
                <i class="bi bi-house-door"></i>
                <span>Home</span>
            </a>

            <a href="{{ route('seles2.pelanggan.index') }}"
                class="nav-item {{ request()->routeIs('seles2.pelanggan.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Pelanggan</span>
            </a>
            <a href="{{ route('seles2.tagihan.index') }}"
                class="nav-item {{ request()->routeIs('seles2.tagihan.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i>
                <span>Pembayaran</span>
            </a>

<a href="{{ route('seles2.pembukuan.index') }}"
    class="nav-item {{ request()->routeIs('seles2.pembukuan.*') ? 'active' : '' }}">
    <i class="bi bi-receipt"></i>
    <span>Pembukuan</span>
</a>


<a href="{{ route('seles2.setoran.index') }}"
    class="nav-item {{ request()->routeIs('seles2.setoran.*') ? 'active' : '' }}">
    <i class="bi bi-cash-stack"></i>
    <span>Setoran</span>
</a>


        </div>
    </div>
</nav>

<style>
    .bottom-nav {
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        padding-bottom: env(safe-area-inset-bottom);
    }

    .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #6c757d;
        padding: 8px 12px;
        border-radius: 12px;
        transition: all 0.3s;
        font-size: 0.75rem;
    }

    .nav-item.active {
        color: #4361ee;
        background: rgba(67, 97, 238, 0.1);
    }

    .nav-item i {
        font-size: 1.2rem;
        margin-bottom: 4px;
    }
</style>
