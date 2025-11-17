<div id="sidebarContainer" class="sidebar bg-white shadow-sm"
     style="width: 260px; height: 100vh; position: sticky; left:0; top:0; transition:0.3s;">

    <div class="p-3">

        <a href="#" class="sidebar-link active">
            <i class="bi bi-grid"></i>
            <span class="link-text">Dashboard</span>
        </a>

        <div class="sidebar-group">
            <div class="sidebar-link d-flex justify-content-between align-items-center"
                 data-bs-toggle="collapse" data-bs-target="#menuPelanggan">
                <span><i class="bi bi-people me-2"></i> <span class="link-text">Pelanggan</span></span>
                <i class="bi bi-caret-down-fill small arrow-icon"></i>
            </div>

            <div class="collapse ms-4" id="menuPelanggan">
                <a href="/pelanggan" class="sidebar-sublink">Data Pelanggan</a>
                <a href="#" class="sidebar-sublink">Status Pelanggan</a>
                <a href="#" class="sidebar-sublink">Status Pembayaran</a>
                <a href="#" class="sidebar-sublink">Riwayat Pembayaran</a>
            </div>
        </div>

        <a href="#" class="sidebar-link">
            <i class="bi bi-cart"></i>
            <span class="link-text">Sales</span>
        </a>

        <a href="#" class="sidebar-link">
            <i class="bi bi-journal-text"></i>
            <span class="link-text">Pembukuan</span>
        </a>

        <a href="#" class="sidebar-link">
            <i class="bi bi-gear"></i>
            <span class="link-text">Pengaturan</span>
        </a>

        <a href="#" class="sidebar-link text-danger mt-5">
            <i class="bi bi-box-arrow-left"></i>
            <span class="link-text">Logout</span>
        </a>
    </div>
</div>

<style>
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px;
        margin-bottom: 6px;
        border-radius: 8px;
        color: #333;
        text-decoration: none;
        transition: 0.2s;
    }
    .sidebar-link:hover { background: #f7f7f7; }

    .sidebar-link.active {
        background: #fff7cc;
        color: #f2be00;
        font-weight: bold;
        border-right: 4px solid #f2be00;
    }

    .sidebar-sublink {
        display: block;
        margin: 4px 0;
        padding: 5px 0;
        color: #444;
        text-decoration: none;
    }
    .sidebar-sublink:hover { color: #000; }

    .sidebar.collapsed {
        width: 70px !important;
    }
    .sidebar.collapsed .link-text {
        opacity: 0;
        pointer-events: none;
    }
    .sidebar.collapsed #menuPelanggan {
        display: none !important;
    }
    .sidebar.collapsed .arrow-icon {
        display: none !important;
    }
</style>
