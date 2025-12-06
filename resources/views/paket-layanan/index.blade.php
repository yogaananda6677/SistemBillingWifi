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

    /* 4. Table Styling (COMPACT) */
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

    /* 5. Action Buttons (Bulat Modern) */
    .btn-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 12px;
        transition: all 0.2s;
        border: none;
    }
    .btn-icon:hover { transform: translateY(-2px); box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    
    .btn-edit-soft { background: #fff3cd; color: #856404; }
    .btn-edit-soft:hover { background: #ffc107; color: #000; }

    .btn-delete-soft { background: #f8d7da; color: #842029; }
    .btn-delete-soft:hover { background: #dc3545; color: #fff; }

    .btn-disabled { background: #e9ecef; color: #adb5bd; cursor: not-allowed; }
</style>

<div class="container-fluid p-4">

    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-box-seam text-warning me-2"></i>Daftar Paket
            </h4>
            <div class="text-muted small">Kelola paket layanan internet</div>
        </div>

        @if($isPpnSet)
            <a href="{{ route('paket-layanan.create') }}" class="btn btn-admin-yellow">
                <i class="bi bi-plus-lg me-1"></i> Tambah Paket
            </a>
        @else
            <button class="btn btn-secondary" type="button" disabled
                    title="Silakan tambah PPN terlebih dahulu sebelum menambah paket">
                <i class="bi bi-lock me-1"></i> Tambah Paket
            </button>
        @endif
    </div>

    {{-- PERINGATAN PPN BELUM DISET --}}
    @if(!$isPpnSet)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" role="alert" style="border-left: 5px solid #ffc107;">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
            <div>
                <strong>Perhatian!</strong> PPN belum diatur. Silakan atur PPN terlebih dahulu.
                <a href="{{ route('ppn.index') }}" class="text-decoration-underline fw-bold text-dark ms-1">Klik di sini</a>.
            </div>
        </div>
    @endif

    {{-- TABLE CARD --}}
    <div class="card-admin p-0" style="overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-admin mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" width="5%">No</th>
                        <th>Nama Paket</th>
                        <th>Kecepatan</th>
                        <th>Harga Dasar</th>
                        <th>PPN</th>
                        <th>Harga Total</th>
                        <th>Pelanggan</th>
                        <th class="text-center" width="12%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataPaket as $index => $p)
                        <tr>
                            <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                            <td class="fw-bold text-dark">{{ $p->nama_paket }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $p->kecepatan }}</span></td>
                            <td class="text-muted">Rp {{ number_format($p->harga_dasar,0,',','.') }}</td>
                            <td class="text-muted">Rp {{ number_format($p->ppn_nominal,0,',','.') }}</td>
                            <td class="fw-bold text-success">Rp {{ number_format($p->harga_total,0,',','.') }}</td>

                            {{-- Jumlah Pelanggan --}}
                            <td>
                                @if($p->langganan_count > 0)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1 rounded">
                                        <i class="bi bi-people-fill me-1"></i> {{ $p->langganan_count }}
                                    </span>
                                @else
                                    <span class="text-muted small fst-italic">0 user</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- EDIT --}}
                                    @if($isPpnSet)
                                        <a href="{{ route('paket-layanan.edit', $p->id_paket) }}" 
                                           class="btn-icon btn-edit-soft" title="Edit Paket">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                    @else
                                        <button class="btn-icon btn-disabled" disabled title="PPN belum diatur">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @endif

                                    {{-- HAPUS --}}
                                    @if($p->langganan_count == 0)
                                        <button type="button" class="btn-icon btn-delete-soft btn-delete"
                                                data-url="{{ route('paket-layanan.destroy', $p->id_paket) }}"
                                                title="Hapus Paket">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    @else
                                        <button class="btn-icon btn-disabled" disabled 
                                                title="Paket sedang digunakan pelanggan">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam fs-1 d-block mb-2 text-light-gray"></i>
                                Belum ada data paket layanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const deleteForm = document.getElementById('deleteForm');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const url = this.dataset.url;
            deleteForm.action = url; 
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });
});
</script>
@endsection