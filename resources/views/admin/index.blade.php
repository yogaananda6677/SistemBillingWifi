@extends('layouts.master')

@section('content')
<style>
    /* --- ADMIN YELLOW THEME (CONSISTENT COMPACT) --- */
    :root {
        --theme-yellow: #ffc107;
        --theme-yellow-dark: #e0a800;
        --theme-yellow-soft: #fff9e6;
        --text-dark: #212529;
        --card-radius: 12px;
    }

    /* 1. Typography */
    .page-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.5px;
    }

    /* 2. Tombol Kuning Custom */
    .btn-admin-yellow {
        background-color: var(--theme-yellow);
        color: var(--text-dark);
        font-weight: 600;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 13px;
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.3);
        transition: all 0.2s ease;
    }
    .btn-admin-yellow:hover {
        background-color: var(--theme-yellow-dark);
        color: var(--text-dark);
        transform: translateY(-2px);
    }

    /* 3. Card Styles */
    .card-admin {
        background: #fff;
        border: none;
        border-radius: var(--card-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border-top: 4px solid var(--theme-yellow);
        width: 100%;
    }

    /* 4. Form Inputs */
    .form-control-admin {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
    }
    .form-control-admin:focus {
        border-color: var(--theme-yellow);
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
    }

    /* 5. Table Styling (COMPACT) */
    .table-admin {
        width: 100%;
        margin-bottom: 0;
    }

    .table-admin thead th {
        background-color: var(--theme-yellow-soft);
        color: var(--text-dark);
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        border-bottom: 2px solid var(--theme-yellow);
        padding: 12px 10px;
        white-space: nowrap;
    }

    .table-admin tbody td {
        padding: 10px;
        vertical-align: middle;
        font-size: 13px;
        border-bottom: 1px solid #f0f0f0;
    }

    .table-admin tbody tr:hover td {
        background-color: #fffdf5;
    }

    /* 6. Statistik Card Kecil */
    .stat-card {
        border-left: 4px solid var(--theme-yellow);
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-3px); }
</style>

<div class="container-fluid p-4">

    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-shield-lock-fill text-warning me-2"></i>Kelola Admin
            </h4>
            <div class="text-muted small">Manajemen akun administrator sistem</div>
        </div>
        
        <button onclick="openModal()" class="btn btn-admin-yellow">
            <i class="bi bi-plus-lg me-1"></i> Tambah Admin
        </button>
    </div>

    {{-- STATISTICS CARDS --}}
    <div class="row mb-4 g-3">
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Total Admin
                            </div>
                            <div class="h5 mb-0 fw-bold text-dark">{{ $admins->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card stat-card h-100 py-2" style="border-left-color: #198754;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Password Default
                            </div>
                            <div class="h6 mb-0 fw-medium text-dark font-monospace">admin123456</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-key fs-2 text-gray-300 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="card-admin p-3 mb-3">
        <div class="row g-2">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 8px 0 0 8px;">
                        <i class="bi bi-search text-warning" style="font-size: 13px;"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control form-control-admin border-start-0" 
                           style="border-radius: 0 8px 8px 0;"
                           placeholder="Cari nama admin / email...">
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card-admin p-0" style="overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-admin mb-0" id="adminTable">
                <thead>
                    <tr>
                        <th class="ps-4">Nama Admin</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($admins as $adm)
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width:32px; height:32px;">
                                        <i class="bi bi-person-fill text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 13px;">{{ $adm->name }}</h6>
                                        <small class="text-muted" style="font-size: 11px;">ID: {{ $adm->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-secondary">
                                    <i class="bi bi-envelope me-2 small"></i> {{ $adm->email }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-secondary">
                                    <i class="bi bi-phone me-2 small"></i> {{ $adm->no_hp ?? '-' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill" style="font-size: 11px;">
                                    <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete border-0"
                                        data-url="{{ route('admin.destroy', $adm->id) }}"
                                        title="Hapus Admin">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="noDataRow">
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-4 opacity-25 mb-3 d-block"></i>
                                Belum ada data admin.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if ($admins->count() > 0)
            <div class="p-3 bg-white border-top text-muted small text-end">
                Total: <strong>{{ $admins->count() }}</strong> Admin Terdaftar
            </div>
        @endif
    </div>

</div>

{{-- MODAL TAMBAH ADMIN --}}
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-warning text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-plus-fill me-2"></i>Tambah Admin
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.store') }}" method="POST" id="adminForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-warning"></i></span>
                            <input type="text" name="name" class="form-control border-start-0" placeholder="Nama lengkap..." required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-warning"></i></span>
                            <input type="email" name="email" class="form-control border-start-0" placeholder="Email..." required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nomor HP</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-phone text-warning"></i></span>
                            <input type="tel" name="no_hp" class="form-control border-start-0" placeholder="08...">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT TETAP SAMA --}}
<script>
    function openModal() {
        new bootstrap.Modal(document.getElementById('modalCreate')).show();
    }

    // Search Logic (Client Side)
    document.getElementById('searchInput')?.addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#adminTable tbody tr:not(#noDataRow)');
        let hasVisibleRows = false;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
                hasVisibleRows = true;
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Delete Logic
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.btn-delete');
        const deleteForm = document.getElementById('deleteForm');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const url = this.dataset.url;
                deleteForm.action = url; 
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
        });
    });
</script>
@endsection