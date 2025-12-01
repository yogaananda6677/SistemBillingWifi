{{-- Modal Menu --}}
<div class="modal fade" id="mobileMenuModal" tabindex="-1">
    <div class="modal-dialog modal-bottom">
        <div class="modal-content rounded-top-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Menu Sales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <a href="{{ route('seles2.pelanggan.create') }}" class="quick-action-btn">
                            <i class="bi bi-person-plus"></i>
                            <span>Tambah Pelanggan</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('seles2.setoran.index') }}" class="quick-action-btn">
                            <i class="bi bi-cash-coin"></i>
                            <span>Setor Dana</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('seles2.pembukuan.pengajuan.create') }}" class="quick-action-btn">
                            <i class="bi bi-wallet2"></i>
                            <span>Ajukan Pengeluaran</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('seles2.pembukuan.detail') }}" class="quick-action-btn">
                            <i class="bi bi-graph-up"></i>
                            <span>Laporan</span>
                        </a>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <div class="menu-list">
                    <a href="{{ route('seles2.dashboard') }}" class="menu-list-item">
                        <i class="bi bi-house"></i>
                        <span>Dashboard</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('seles2.pelanggan.index') }}" class="menu-list-item">
                        <i class="bi bi-people"></i>
                        <span>Data Pelanggan</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('seles2.pembukuan.index') }}" class="menu-list-item">
                        <i class="bi bi-journal-text"></i>
                        <span>Pembukuan Sales</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('seles2.profile') }}" class="menu-list-item">
                        <i class="bi bi-person-gear"></i>
                        <span>Akun Saya</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('logout') }}" class="menu-list-item text-danger">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.modal-bottom {
    margin: 0;
    align-items: end;
}
.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 16px 8px;
    background: #f8f9fa;
    border-radius: 12px;
    text-decoration: none;
    color: #333;
    text-align: center;
    transition: all 0.3s;
}
.quick-action-btn:active {
    background: #e9ecef;
    transform: scale(0.95);
}
.quick-action-btn i {
    font-size: 1.5rem;
    margin-bottom: 8px;
    color: #4361ee;
}
.quick-action-btn span {
    font-size: 0.8rem;
    font-weight: 500;
}
</style>