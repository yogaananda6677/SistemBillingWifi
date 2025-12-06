@extends('layouts.master')

@section('content')
<style>
    /* --- ADMIN YELLOW THEME (COMPACT) --- */
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

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-geo-alt-fill text-warning me-2"></i>Pengaturan Area
            </h4>
            <div class="text-muted small">Kelola wilayah dan sales area</div>
        </div>
        
        <a href="{{ route('area.create') }}" class="btn btn-admin-yellow">
            <i class="bi bi-plus-lg me-1"></i> Tambah Area
        </a>
    </div>

    {{-- TABLE CARD --}}
    <div class="card-admin p-0" style="overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-admin mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" width="5%">No</th>
                        <th>Nama Area</th>
                        <th>Sales di Area</th>
                        <th class="text-center" width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataArea ?? [] as $index => $area)
                        @php
                            $salesDariHasMany = $area->sales ?? collect();
                            $salesDariPivot   = $area->salesMulti ?? collect();
                            $allSales = $salesDariHasMany->merge($salesDariPivot)->unique('id_sales')->values();
                            $jumlahSales = $allSales->count();
                        @endphp

                        <tr>
                            <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                            <td class="fw-bold text-dark">{{ $area->nama_area }}</td>

                            {{-- Kolom Sales --}}
                            <td>
                                @if($jumlahSales > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($allSales as $sales)
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-person me-1 text-muted"></i>
                                                {{ $sales->user->name ?? 'Sales tanpa user' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small fst-italic">Belum ada sales</span>
                                @endif
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- Edit --}}
                                    <a href="{{ route('area.edit', $area->id_area) }}" 
                                       class="btn-icon btn-edit-soft" title="Edit Area">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>

                                    {{-- Hapus --}}
                                    @if($jumlahSales == 0)
                                        <button type="button" class="btn-icon btn-delete-soft btn-delete"
                                                data-url="{{ route('area.destroy', $area->id_area) }}"
                                                title="Hapus Area">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn-icon btn-disabled" disabled
                                                title="Area ini memiliki sales, tidak dapat dihapus">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-geo text-light-gray fs-1 d-block mb-2"></i>
                                Belum ada data area. Silakan tambah area baru.
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
    const deleteForm = document.getElementById('deleteForm'); // Pastikan ID form delete di master layout sesuai

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