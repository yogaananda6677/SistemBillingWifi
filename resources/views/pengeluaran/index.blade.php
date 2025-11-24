@extends('layouts.master')

@section('content')
<style>
/* layout utama */
.page-wrap { padding: 24px; }
.header-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
.title { font-weight:700; font-size:18px; color:#222; }
.month-selector { color:#ffbf00; font-weight:700; cursor:pointer; }

/* search & filter */
.controls { display:flex; gap:12px; align-items:center; }
.search-box { display:flex; align-items:center; background:#fff; border-radius:10px; border:1px solid #e6e6e6; overflow:hidden; }
.search-box input { border:0; padding:8px 12px; width:260px; outline:none; }
.search-box button { background:#fbc02d; border:0; padding:8px 12px; color:#111; cursor:pointer; }

.filter-btn { background:#fff; border:1px solid #ddd; padding:8px 12px; border-radius:8px; cursor:pointer; }

/* card & table */
.table-card { background:#fff; border-radius:14px; padding:18px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.table thead th { background:#f7f7f8; font-weight:600; font-size:13px; padding:10px; text-align:left; border-bottom:0; }
.table tbody td { padding:12px 10px; vertical-align:middle; font-size:13px; border-top:0; }
.table tbody tr:hover { background:#fafafa; }

.status {
    font-weight:700;
}
.status.pending { color:#ffb300; }
.status.approved { color:#00a651; }
.status.rejected { color:#dc3545; }

/* kecil */
.file-link { text-decoration:none; color:#666; font-weight:600; }
.small-muted { font-size:12px; color:#999; }
.pagination-wrapper { margin-top:14px; display:flex; justify-content:center; }
.loading { text-align:center; padding:24px; display:none; }
</style>

<div class="container-fluid page-wrap">

    <div class="header-row">
        <div>
            <div class="title">Sales - Pengajuan</div>
            
        </div>

        <div class="controls">
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Cari...">
                <button id="search-button"><i class="fas fa-search"></i></button>
            </div>

            <select id="status-filter" class="filter-btn">
                <option value="">Semua Status</option>
                <option value="pending">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
            </select>

        </div>
    </div>

    <div class="table-card">
        <div id="loading" class="loading">
            <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
            <div class="small-muted mt-2">Memuat data...</div>
        </div>

        <div id="table-area">
            <div class="table-responsive">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th style="width:48px">No</th>
                            <th>Nama Sales</th>
                            <th>Tanggal</th>
                            <th>Pengeluaran</th>
                            <th>Nominal</th>
                            <th>Bukti</th>
                            <th>Disetujui/Ditolak Oleh</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="pengajuan-body">
                        @include('pengeluaran.partials.table_rows', ['pengajuan' => $pengajuan])
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper" id="pagination-wrapper">
                {{ $pengajuan->links() }}
            </div>
        </div>
    </div>
</div>
<!-- Modal Ubah Status -->
<!-- Modal Status -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p id="modal-text"></p>

                <!-- Untuk pending (dua opsi) -->
                <div id="pending-options" class="d-none mt-3">
                    <button class="btn btn-success w-100 mb-2 choose-status" data-value="approved">
                        ✔ Setujui
                    </button>
                    <button class="btn btn-danger w-100 choose-status" data-value="rejected">
                        ✖ Tolak
                    </button>
                </div>

                <!-- Form untuk approved/rejected -->
                <form id="statusForm" method="POST" class="d-none mt-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status_approve" id="statusInput">

                    <button type="submit" class="btn btn-primary w-100">
                        Ya, Lanjutkan
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>


@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    let timeout = null;
    let currentPage = 1;

    function setLoading(on = true) {
        if (on) { $('#loading').show(); $('#table-area').hide(); }
        else { $('#loading').hide(); $('#table-area').show(); }
    }

    function loadData(page = 1) {
        currentPage = page;
        setLoading(true);

        const q = $('#search-input').val();
        const status = $('#status-filter').val();
        const month = $('#month-selector')?.data('month') || '';

        $.ajax({
            url: '{{ route("pengajuan.index") }}',
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
        const base = '{{ route("pengajuan.index") }}';
        const u = params.toString() ? base + '?' + params.toString() : base;
        window.history.replaceState({}, '', u);
    }

    // SEARCH debounce
    $('#search-input').on('input', function(){
        clearTimeout(timeout);
        timeout = setTimeout(()=> loadData(1), 300);
    });

    $('#search-button').on('click', function(){ loadData(1); });

    // FILTER STATUS
    $('#status-filter').on('change', function(){
        loadData(1);
    });

    // PAGINATION AJAX
    $(document).on('click', '.pagination a', function(e){
        e.preventDefault();
        const href = $(this).attr('href') || '';
        const page = new URL(href, window.location.origin).searchParams.get('page') || 1;
        loadData(page);
    });

    // MONTH SELECTOR
    $('#month-selector').on('click', function(){
        const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const cur = $(this).data('monthIndex') || 0;
        const next = (cur + 1) % months.length;

        $(this)
            .data('monthIndex', next)
            .data('month', months[next])
            .text(months[next]);

        loadData(1);
    });

});
$(document).on('click', '.update-status', function() {
    let id = $(this).data('id');
    let status = $(this).data('status');

    $.ajax({
        url: '/pengajuan/' + id + '/status',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            status: status
        },
        success: function() {
            loadData(currentPage); // reload tabel tanpa refresh halaman
        },
        error: function() {
            alert('Gagal memperbarui status');
        }
    });
});

$(document).on('click', '.status-badge', function () {
    const id = $(this).data('id');
    const current = $(this).data('current');

    // Set route
    $('#statusForm').attr('action', '{{ url("pengeluaran/update-status") }}/' + id);

    // Reset state modal
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

// Ketika tombol pending dipilih
$(document).on('click', '.choose-status', function() {
    const val = $(this).data('value');
    $('#statusInput').val(val);
    $('#statusForm').removeClass('d-none');
    $('#statusForm').submit();
});


$(document).on('click', '.choose-status', function () {
    const chosenStatus = $(this).data('value');

    $('#statusInput').val(chosenStatus);
    $('#statusForm').removeClass('d-none');  // pastikan form ada
    $('#statusForm').submit();
});


</script>

@endpush
