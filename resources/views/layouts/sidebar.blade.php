<div id="sidebarContainer" class="sidebar bg-white shadow-sm"
     style="width: 260px; height: 100vh; position: sticky; left:0; top:0; transition:0.25s ease;">

    <div class="p-3">

        <!-- Dashboard -->
        <a href="#" class="sidebar-link active">
            <i class="bi bi-grid"></i>
            <span class="link-text">Dashboard</span>
        </a>

        <!-- Pelanggan -->
        <div class="sidebar-group">

            <a class="sidebar-link pelanggan-toggle" data-bs-toggle="collapse" data-bs-target="#menuPelanggan">
                <i class="bi bi-people"></i>
                <span class="link-text">Pelanggan</span>
                <i class="bi bi-caret-down-fill arrow-icon ms-auto small"></i>
            </a>

            <div class="collapse ps-4" id="menuPelanggan">
                <a href="/pelanggan" class="sidebar-sublink">Data Pelanggan</a>
                <a href="#" class="sidebar-sublink">Status Pelanggan</a>
                <a href="#" class="sidebar-sublink">Status Pembayaran</a>
                <a href="#" class="sidebar-sublink">Riwayat Pembayaran</a>
            </div>
        </div>

        <!-- Sales -->
        <a href="#" class="sidebar-link">
            <i class="bi bi-cart"></i>
            <span class="link-text">Sales</span>
        </a>

        <!-- Pembukuan -->
        <a href="#" class="sidebar-link">
            <i class="bi bi-journal-text"></i>
            <span class="link-text">Pembukuan</span>
        </a>

        <!-- Pengaturan -->
        <a href="#" class="sidebar-link">
            <i class="bi bi-gear"></i>
            <span class="link-text">Pengaturan</span>
        </a>

        <!-- Logout -->
        <a href="#" class="sidebar-link text-danger mt-5">
            <i class="bi bi-box-arrow-left"></i>
            <span class="link-text">Logout</span>
        </a>

    </div>
</div>

<style>

    /* BASE STYLE */
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 14px;
        margin-bottom: 8px;
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

    .sidebar-link i {
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .sidebar-sublink {
        display: block;
        margin: 4px 0;
        padding: 6px 0;
        color: #444;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .sidebar-sublink:hover { color: #000; }

    .arrow-icon { transition: 0.3s; }

    /* ROTATE ARROW WHEN OPEN */
    .pelanggan-toggle[aria-expanded="true"] .arrow-icon {
        transform: rotate(180deg);
    }

    /* SIDEBAR COLLAPSE */
    .sidebar.collapsed {
        width: 70px !important;
    }

    /* Hide text on collapse */
    .sidebar.collapsed .link-text {
        opacity: 0;
        width: 0;
        overflow: hidden;
        pointer-events: none;
    }

    /* Hide submenu & arrow */
    .sidebar.collapsed #menuPelanggan,
    .sidebar.collapsed .arrow-icon {
        display: none !important;
    }

    /* FIX ICON SIZE & ALIGNMENT WHEN COLLAPSED */
    .sidebar.collapsed .sidebar-link {
        justify-content: center;
        padding: 12px 0 !important;
        gap: 0 !important;
    }

    .sidebar.collapsed .sidebar-link i {
        font-size: 1.3rem;   /* ukuran stabil, nggak membesar */
        margin: 0 auto;
        display: block;
    }

    .sidebar.collapsed .sidebar-group {
        margin: 0 !important;
        padding: 0 !important;
    }

</style>
