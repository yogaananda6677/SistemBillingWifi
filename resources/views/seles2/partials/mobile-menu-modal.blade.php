{{-- Modal Menu --}}
<div class="modal fade" id="mobileMenuModal" tabindex="-1">
    <div class="modal-dialog modal-bottom">
        <div class="modal-content rounded-top-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="menu-list">
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

.menu-list-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    text-decoration: none;
    color: #333;
    border-bottom: 1px solid #f1f1f1;
}

.menu-list-item i {
    font-size: 1.2rem;
}

</style>