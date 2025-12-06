@extends('layouts.master')

@section('content')
<style>
    /* --- ADMIN YELLOW THEME (CONSISTENT) --- */
    :root {
        --theme-yellow: #ffc107;
        --theme-yellow-dark: #e0a800;
        --theme-yellow-soft: #fff9e6;
        --text-dark: #212529;
        --card-radius: 12px;
    }

    /* Typography */
    .page-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.5px;
    }

    /* Card Styles */
    .card-admin {
        background: #fff;
        border: none;
        border-radius: var(--card-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border-top: 4px solid var(--theme-yellow);
        width: 100%;
    }

    /* Custom Yellow Button */
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
</style>

<div class="container-fluid p-4">

    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-percent text-warning me-2"></i>Pengaturan PPN
            </h4>
            <div class="text-muted small">Kelola pajak pertambahan nilai aplikasi</div>
        </div>
    </div>

    {{-- CONTENT CARD --}}
    <div class="card-admin p-4">
        @if($ppn) {{-- Jika data PPN sudah ada --}}
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                        <i class="bi bi-receipt-cutoff fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1">{{ $ppn->nama_ppn }}</h5>
                        <div class="text-muted small">
                            Besaran Persentase: 
                            <span class="fw-bold text-dark fs-5 ms-1">{{ $ppn->presentase_ppn * 100 }}%</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('ppn.edit', $ppn->id_setting) }}" class="btn btn-admin-yellow">
                        <i class="bi bi-pencil me-1"></i> Edit PPN
                    </a>
                    
                    </div>
            </div>

        @else {{-- Jika data PPN belum ada --}}
            
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-file-earmark-x text-warning" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <h6 class="fw-bold text-dark">Belum ada data PPN</h6>
                <p class="text-muted small mb-4">Silakan tambahkan pengaturan PPN untuk aplikasi.</p>
                
                <a href="{{ route('ppn.create') }}" class="btn btn-admin-yellow">
                    <i class="bi bi-plus-circle me-1"></i> Tambah PPN
                </a>
            </div>

        @endif
    </div>

</div>

{{-- SCRIPT --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Script ini disiapkan jika nanti tombol hapus di-uncomment
        const deleteButtons = document.querySelectorAll('.btn-delete');
        const deleteForm = document.getElementById('deleteForm'); // Pastikan form ini ada di layout/master atau di file ini

        if(deleteButtons && deleteForm) {
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const url = this.dataset.url;
                    deleteForm.action = url;
                });
            });
        }
    });
</script>

@endsection