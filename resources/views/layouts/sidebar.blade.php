<div id="sidebarContainer" class="sidebar bg-white shadow-sm"
    style="width: 260px; height: 100vh; position: sticky; left:0; top:0; transition:0.25s ease; overflow-y: auto;">

    <div class="p-3">

        <a href="{{ route('dashboard-admin') }}" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid"></i>
            <span class="link-text">Dashboard</span>
        </a>

        <div class="sidebar-group">
            
            {{-- Logic agar menu tetap terbuka saat submenu diklik --}}
            @php
                $isPelangganActive = request()->is('pelanggan*') || 
                                     request()->is('tagihan*') || 
                                     request()->is('admin/tagihan*') || 
                                     request()->is('pembayaran*');
            @endphp

            <a class="sidebar-link pelanggan-toggle {{ $isPelangganActive ? '' : 'collapsed' }}"
                data-bs-toggle="collapse" 
                data-bs-target="#menuPelanggan"
                aria-expanded="{{ $isPelangganActive ? 'true' : 'false' }}">
                
                <i class="bi bi-people"></i>
                <span class="link-text">Pelanggan</span>
                <i class="bi bi-caret-down-fill arrow-icon ms-auto small"></i>
            </a>

            <div class="collapse ps-4 {{ $isPelangganActive ? 'show' : '' }}" id="menuPelanggan">

                {{-- 1. Data Pelanggan --}}
                <a href="{{ route('pelanggan.index') }}"
                   class="sidebar-sublink {{ request()->routeIs('pelanggan.index') ? 'active-sub' : '' }}">
                    Data Pelanggan
                </a>

                {{-- 2. Status Pelanggan --}}
                <a href="{{ route('pelanggan.status') }}"
                   class="sidebar-sublink {{ request()->routeIs('pelanggan.status') ? 'active-sub' : '' }}">
                    Status Pelanggan
                </a>

                {{-- 3. Status Pembayaran (Tagihan) --}}
                <a href="{{ route('tagihan.index') }}"
                   class="sidebar-sublink {{ request()->routeIs('tagihan.index') ? 'active-sub' : '' }}">
                    Status Pembayaran
                </a>

                {{-- 4. Pembayaran Pelanggan (Admin) --}}
                <a href="{{ route('admin.tagihan.index') }}" 
                   class="sidebar-sublink {{ request()->is('admin/tagihan*') ? 'active-sub' : '' }}">
                    Pembayaran Pelanggan
                </a>

                {{-- 5. Riwayat Pembayaran --}}
                <a href="{{ route('pembayaran.riwayat') }}"
                   class="sidebar-sublink {{ request()->routeIs('pembayaran.riwayat') ? 'active-sub' : '' }}">
                    Riwayat Pembayaran
                </a>
            </div>
        </div>

        <div class="sidebar-group">
            <a class="sidebar-link sales-toggle {{ request()->is('sales/*') ? '' : 'collapsed' }}"
                data-bs-toggle="collapse"
                data-bs-target="#menuSales"
                aria-expanded="{{ request()->is('sales/*') ? 'true' : 'false' }}">
                
                <i class="bi bi-person-circle"></i>
                <span class="link-text">Sales</span>
                <i class="bi bi-caret-down-fill arrow-icon ms-auto small"></i>
            </a>

            <div class="collapse ps-4 {{ request()->is('sales/*') ? 'show' : '' }}" id="menuSales">

                <a href="/sales/data-sales" 
                   class="sidebar-sublink {{ request()->is('sales/data-sales') ? 'active-sub' : '' }}">
                    Data Sales
                </a>

                <a href="/sales/setoran"
                   class="sidebar-sublink {{ request()->is('sales/setoran') ? 'active-sub' : '' }}">
                    Setoran
                </a>

                <a href="/sales/pengajuan"
                   class="sidebar-sublink {{ request()->is('sales/pengajuan') ? 'active-sub' : '' }}">
                    Pengajuan Pengeluaran
                </a>

            </div>
        </div>

        <a href="/pembukuan" class="sidebar-link {{ request()->is('pembukuan*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i>
            <span class="link-text">Pembukuan</span>
        </a>

        <div class="sidebar-group">

            <a class="sidebar-link pengaturan-toggle {{ request()->is('pengaturan*') ? '' : 'collapsed' }}"
                data-bs-toggle="collapse" 
                data-bs-target="#menuPengaturan"
                aria-expanded="{{ request()->is('pengaturan*') ? 'true' : 'false' }}">
                
                <i class="bi bi-gear"></i>
                <span class="link-text">Pengaturan</span>
                <i class="bi bi-caret-down-fill arrow-icon ms-auto small"></i>
            </a>

            <div class="collapse ps-4 {{ request()->is('pengaturan*') ? 'show' : '' }}" id="menuPengaturan">

                <a href="{{ route('ppn.index') }}" 
                   class="sidebar-sublink {{ request()->is('pengaturan/ppn') ? 'active-sub' : '' }}">
                    PPN
                </a>

                <a href="{{ route('area.index') }}" 
                   class="sidebar-sublink {{ request()->is('pengaturan/area') ? 'active-sub' : '' }}">
                    Area
                </a>

                <a href="{{ route('paket-layanan.index') }}" 
                   class="sidebar-sublink {{ request()->is('pengaturan/paket-layanan') ? 'active-sub' : '' }}">
                    Paket Layanan
                </a>

            </div>
        </div>

        <div class="sidebar-link text-danger mt-5 cursor-pointer"
            data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="bi bi-box-arrow-left"></i>
            <span class="link-text">Logout</span>
        </div>

    </div>
</div>

<style>
    /* GENERAL STYLE */
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 14px;
        margin-bottom: 6px;
        border-radius: 8px;
        color: #333;
        text-decoration: none !important;
        transition: 0.2s;
        font-size: 15px;
        cursor: pointer;
    }

    .sidebar-link:hover {
        background: #f5f5f5;
    }

    .sidebar-link.active {
        background: #fff7cc;
        color: #d8a800;
        font-weight: 600;
        border-right: 4px solid #e1b800;
    }

    .sidebar-sublink {
        display: block;
        margin: 4px 0;
        padding: 8px 10px;
        color: #555;
        text-decoration: none !important;
        font-size: 14px;
        border-radius: 6px;
        transition: 0.2s;
    }

    .sidebar-sublink:hover {
        background: #f2f2f2;
        color: #222 !important;
    }

    .sidebar-sublink.active-sub {
        color: #d8a800; /* Warna kuning emas sesuai tema */
        font-weight: 600;
        background: #fffbef;
    }

    .arrow-icon {
        transition: transform 0.3s ease;
    }

    /* Logic Panah: Kalau TIDAK ada class 'collapsed', panah muter */
    .sidebar-link:not(.collapsed) .arrow-icon {
        transform: rotate(180deg);
    }

    /* COLLAPSED SIDEBAR (Saat tombol hamburger diklik) */
    .sidebar.collapsed {
        width: 70px !important;
    }

    .sidebar.collapsed .link-text {
        opacity: 0;
        pointer-events: none;
        display: none;
    }

    .sidebar.collapsed .collapse,
    .sidebar.collapsed .arrow-icon {
        display: none !important;
    }
    
    .sidebar.collapsed .sidebar-group {
        position: relative;
    }
</style>