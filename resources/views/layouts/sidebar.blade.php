<div id="sidebarContainer" class="sidebar bg-white shadow-sm"
    style="width: 260px; height: 100vh; position: sticky; left:0; top:0; transition:0.25s ease;">

    <div class="p-3">

        <!-- DASHBOARD -->
        <a href="{{ route('dashboard-admin') }}" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid"></i>
            <span class="link-text">Dashboard</span>
        </a>

        <!-- PELANGGAN -->
        <div class="sidebar-group">

            <a class="sidebar-link pelanggan-toggle {{ request()->is('pelanggan*') ? '' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#menuPelanggan">
                <i class="bi bi-people"></i>
                <span class="link-text">Pelanggan</span>
                <i class="bi bi-caret-down-fill arrow-icon ms-auto small"></i>
            </a>

            <div class="collapse ps-4 {{ request()->is('pelanggan*') ? 'show' : '' }}" id="menuPelanggan">

                <a href="/pelanggan" class="sidebar-sublink {{ request()->is('pelanggan') ? 'active-sub' : '' }}">
                    Data Pelanggan
                </a>

                <div class="sidebar-group">
                    <a class="sidebar-sublink pelanggan-status-toggle
                        {{ request()->is('pelanggan/status*') ? 'active-sub' : '' }}"
                        data-bs-toggle="collapse" data-bs-target="#menuStatusPelanggan">

                        <span class="link-text">Status Pelanggan</span>
                        <i class="bi bi-caret-down-fill arrow-icon ms-auto small"></i>
                    </a>

                <div class="collapse ps-3 {{ request()->is('pelanggan/status/*') ? 'show' : '' }}" id="menuStatusPelanggan">

                    <a href="/pelanggan?status=baru" class="sidebar-sublink {{ request('status') == 'baru' ? 'active-sub' : '' }}">
                        Baru
                    </a>

                    <a href="/pelanggan?status=aktif" class="sidebar-sublink {{ request('status') == 'aktif' ? 'active-sub' : '' }}">
                        Aktif
                    </a>

                    <a href="/pelanggan?status=berhenti" class="sidebar-sublink {{ request('status') == 'berhenti' ? 'active-sub' : '' }}">
                        Berhenti
                    </a>

                    <a href="/pelanggan?status=isolir" class="sidebar-sublink {{ request('status') == 'isolir' ? 'active-sub' : '' }}">
                        Isolir
                    </a>

                </div>

                </div>
                <div class="sidebar-group">
                    <a class="sidebar-sublink pembayaran-status-toggle
                        {{ request()->is('pelanggan/pembayaran*') ? 'active-sub' : '' }}"
                        data-bs-toggle="collapse" data-bs-target="#menuStatusPembayaran">

                        <span class="link-text">Status Pembayaran</span>
                        <i class="bi bi-caret-down-fill arrow-icon ms-auto small"></i>
                    </a>

                    <div class="collapse ps-3 {{ request()->is('pelanggan/pembayaran/*') ? 'show' : '' }}" id="menuStatusPembayaran">

                        <a href="/pelanggan/pembayaran/lunas" class="sidebar-sublink
                            {{ request()->is('pelanggan/pembayaran/lunas') ? 'active-sub' : '' }}">
                            Lunas
                        </a>

                        <a href="/pelanggan/pembayaran/belum-lunas" class="sidebar-sublink
                            {{ request()->is('pelanggan/pembayaran/belum-lunas') ? 'active-sub' : '' }}">
                            Belum Lunas
                        </a>
                    </div>
                </div>

                <a href="/pelanggan/riwayat" class="sidebar-sublink
                    {{ request()->is('pelanggan/riwayat') ? 'active-sub' : '' }}">
                    Riwayat Pembayaran
                </a>

            </div>
        </div>

        <div class="sidebar-group">
            <a class="sidebar-link sales-toggle {{ request()->is('sales*') ? '' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#menuSales">
                <i class="bi bi-cart"></i>
                <span class="link-text">Sales</span>
                <i class="bi bi-caret-down-fill arrow-icon ms-auto small"></i>
            </a>

            <div class="collapse ps-4 {{ request()->is('sales*') ? 'show' : '' }}" id="menuSales">
                <a href="/sales" class="sidebar-sublink {{ request()->is('sales') ? 'active-sub' : '' }}">
                    Data Sales
                </a>
                <a href="/sales/laporan" class="sidebar-sublink">
                    Setoran
                </a>
                <a href="/sales/laporan" class="sidebar-sublink">
                    Pengajuan Pengeluran
                </a>
                </div>
        </div>

        <!-- PEMBUKUAN -->
        <a href="/pembukuan" class="sidebar-link">
            <i class="bi bi-journal-text"></i>
            <span class="link-text">Pembukuan</span>
        </a>


        <!-- PENGATURAN -->
        <div class="sidebar-group">

            <a class="sidebar-link pengaturan-toggle {{ request()->is('pengaturan*') ? '' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#menuPengaturan">
                <i class="bi bi-gear"></i>
                <span class="link-text">Pengaturan</span>
                <i class="bi bi-caret-down-fill arrow-icon ms-auto small"></i>
            </a>

            <div class="collapse ps-4 {{ request()->is('pengaturan*') ? 'show' : '' }}" id="menuPengaturan">

                <a href="{{ route('ppn.index') }}" class="sidebar-sublink {{ request()->is('pengaturan/ppn') ? 'active-sub' : '' }}">
                    PPN
                </a>

                <a href="{{ route('area.index') }}" class="sidebar-sublink {{ request()->is('pengaturan/area') ? 'active-sub' : '' }}">
                    Area
                </a>

                <a href="{{ route('paket-layanan.index') }}" class="sidebar-sublink {{ request()->is('pengaturan/paket-layanan') ? 'active-sub' : '' }}">
                    Paket Layanan
                </a>

            </div>
        </div>

        <!-- LOGOUT -->
        <div class="sidebar-link text-danger mt-5 cursor-pointer"
            data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="bi bi-box-arrow-left"></i>
            <span class="link-text">Logout</span>
        </div>

    </div>
</div>

<style>
    /* GENERAL */
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
        padding: 6px 6px;
        color: #555;
        text-decoration: none !important;
        font-size: 14px;
        border-radius: 6px;
    }

    .sidebar-sublink:hover {
        background: #f2f2f2;
        color: #222 !important;
    }

    .sidebar-sublink.active-sub {
        color: #d8a800;
        font-weight: 600;
    }

    .arrow-icon {
        transition: 0.3s;
    }

    .pelanggan-toggle[aria-expanded="true"] .arrow-icon {
        transform: rotate(180deg);
    }

    /* COLLAPSED SIDEBAR */
    .sidebar.collapsed {
        width: 70px !important;
    }

    .sidebar.collapsed .link-text {
        opacity: 0;
        pointer-events: none;
    }

    .sidebar.collapsed #menuPelanggan,
    .sidebar.collapsed .arrow-icon {
        display: none !important;
    }
</style>
