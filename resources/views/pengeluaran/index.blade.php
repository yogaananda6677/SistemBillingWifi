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

    /* 2. Card Styles */
    .card-admin {
        background: #fff;
        border: none;
        border-radius: var(--card-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border-top: 4px solid var(--theme-yellow);
        width: 100%;
    }

    /* 3. Form Inputs */
    .form-control-admin, .form-select-admin {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
    }
    .form-control-admin:focus, .form-select-admin:focus {
        border-color: var(--theme-yellow);
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
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

    /* 5. Pagination Styling (Yellow & Consistent) */
    .pagination-wrapper {
        display: flex;
        justify-content: center !important;
        align-items: center;
        width: 100%;
        padding: 15px; 
        background: #fff;
        border-top: 1px solid #f0f0f0;
    }

    .pagination-wrapper nav .d-none.d-sm-flex > div:first-child {
        display: none !important; 
    }
    .pagination-wrapper nav .d-none.d-sm-flex {
        justify-content: center !important;
    }

    .page-item .page-link {
        color: #333;
        border: none;
        margin: 0 2px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
        padding: 6px 12px;
        background: #f8f9fa;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .page-item.active .page-link {
        background-color: var(--theme-yellow) !important;
        border-color: var(--theme-yellow) !important;
        color: #000 !important;
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4);
    }
    
    /* 6. Custom Badge Status */
    .status { font-weight: 700; font-size: 12px; padding: 4px 8px; border-radius: 6px; }
    .status.pending { background: #fff3cd; color: #856404; }
    .status.approved { background: #d1e7dd; color: #0f5132; }
    .status.rejected { background: #f8d7da; color: #842029; }
    
    /* Label Filter Kecil */
    .filter-label {
        font-size: 11px;
        font-weight: 700;
        color: #6c757d;
        margin-bottom: 4px;
        display: block;
    }
</style>

<div class="container-fluid p-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-file-earmark-text-fill text-warning me-2"></i>Pengajuan Sales
            </h4>
            <div class="text-muted small">Kelola persetujuan pengeluaran sales</div>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="card-admin p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <span class="filter-label">Pencarian</span>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 8px 0 0 8px;">
                        <i class="fas fa-search text-warning" style="font-size: 13px;"></i>
                    </span>
                    <input type="text" id="search-input" class="form-control form-control-admin border-start-0" 
                           style="border-radius: 0 8px 8px 0;"
                           placeholder="Cari sales, keterangan...">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <span class="filter-label">Filter Status</span>
                <select id="status-filter" class="form-select form-select-admin">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>
            
            {{-- Month Selector Simple (Optional jika ingin dipertahankan) --}}
            <div class="col-6 col-md-4 text-end">
                 {{-- Bisa ditambahkan kontrol bulan di sini jika perlu --}}
            </div>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card-admin p-0" style="overflow: hidden;">
        
        {{-- Loading Spinner --}}
        <div id="loading" class="text-center p-4" style="display: none;">
            <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
            <p class="mt-2 text-muted small">Memuat data...</p>
        </div>

        {{-- Table Container --}}
        <div id="table-area">
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width:48px">No</th>
                            <th>Nama Sales</th>
                            <th>Wilayah</th>
                            <th>Tanggal</th>
                            <th>Pengeluaran</th>
                            <th>Nominal</th>
                            <th>Bukti</th>
                            <th>Approve By</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="pengajuan-body">
                        @include('pengeluaran.partials.table_rows', ['pengajuan' => $pengajuan])
                    </tbody>
                </table>
            </div>

            {{-- Pagination Consistent --}}
            <div class="pagination-wrapper" id="pagination-wrapper">
                {{ $pengajuan->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header bg-warning text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title fw-bold">Konfirmasi Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p id="modal-text" class="text-center fw-medium mb-4 text-secondary"></p>

                <div id="pending-options" class="d-none">
                    <div class="d-flex gap-2">
                        <button class="btn btn-success w-100 choose-status fw-bold" data-value="approved">
                            <i class="bi bi-check-circle me-1"></i> Setujui
                        </button>
                        <button class="btn btn-danger w-100 choose-status fw-bold" data-value="rejected">
                            <i class="bi bi-x-circle me-1"></i> Tolak
                        </button>
                    </div>
                </div>

                <form id="statusForm" method="POST" class="d-none">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status_approve" id="statusInput">
                    <button type="submit" class="btn btn-warning w-100 fw-bold text-dark">
                        Ya, Lanjutkan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- SCRIPT ASLI TIDAK BERUBAH SAMA SEKALI --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    let timeout = null;
    let currentPage = 1;

    function setLoading(on = true) {
        if (on) { $('#loading').show(); $('#table-area').css('opacity', '0.5'); }
        else { $('#loading').hide(); $('#table-area').css('opacity', '1'); }
    }

    function loadData(page = 1) {
        currentPage = page;
        setLoading(true);

        const q = $('#search-input').val();
        const status = $('#status-filter').val();
        const month = $('#month-selector')?.data('month') || '';

        $.ajax({
            url: '{{ route("admin.pengajuan.index") }}',
            type: 'GET',
            data: { 
                ajax: true, 
                search: q, 
                page: page,
                month: month,
                status: status 
            },
            success: function(res) {
                $('#pengajuan-body').html(res.html);
                $('#pagination-wrapper').html(res.pagination);
                setLoading(false);
                updateUrl(q, page, month, status);
            },
            error: function() {
                setLoading(false);
                alert('Terjadi kesalahan saat memuat data.');
            }
        });
    }

    function updateUrl(q, page, month, status) {
        const params = new URLSearchParams();
        if (q) params.set('search', q);
        if (page && page > 1) params.set('page', page);
        if (month) params.set('month', month);
        if (status) params.set('status', status);
        const base = '{{ route("admin.pengajuan.index") }}';
        const u = params.toString() ? base + '?' + params.toString() : base;
        window.history.replaceState({}, '', u);
    }

    $('#search-input').on('input', function(){
        clearTimeout(timeout);
        timeout = setTimeout(()=> loadData(1), 300);
    });

    $('#status-filter').on('change', function(){
        loadData(1);
    });

    $(document).on('click', '.pagination a', function(e){
        e.preventDefault();
        const href = $(this).attr('href') || '';
        const page = new URL(href, window.location.origin).searchParams.get('page') || 1;
        loadData(page);
    });

    // LOGIC MODAL STATUS
    $(document).on('click', '.status-badge', function () {
        const id = $(this).data('id');
        const current = $(this).data('current');

        $('#statusForm').attr('action', '{{ url("pengeluaran/update-status") }}/' + id);
        $('#pending-options').addClass('d-none');
        $('#statusForm').addClass('d-none');

        if (current === 'pending') {
            $('#modal-text').html("Pilih tindakan untuk pengajuan ini:");
            $('#pending-options').removeClass('d-none');
        }
        else if (current === 'approved') {
            $('#modal-text').html("Ubah status dari <b>Setuju</b> menjadi <b>Tolak</b>?");
            $('#statusInput').val('rejected');
            $('#statusForm').removeClass('d-none');
        }
        else if (current === 'rejected') {
            $('#modal-text').html("Ubah status dari <b>Tolak</b> menjadi <b>Setuju</b>?");
            $('#statusInput').val('approved');
            $('#statusForm').removeClass('d-none');
        }

        $('#statusModal').modal('show');
    });

    $(document).on('click', '.choose-status', function () {
        const chosenStatus = $(this).data('value');
        $('#statusInput').val(chosenStatus);
        $('#statusForm').removeClass('d-none');  
        $('#statusForm').submit();
    });
});
</script>
@endpush