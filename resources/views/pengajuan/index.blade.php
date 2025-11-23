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
            <div class="small-muted">Default Mounth: <span class="month-selector" id="month-selector">Januari <i class="fas fa-chevron-right"></i></span></div>
        </div>

        <div class="controls">
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Cari...">
                <button id="search-button"><i class="fas fa-search"></i></button>
            </div>

            <button class="filter-btn" id="filter-btn">Filter <i class="fas fa-chevron-down"></i></button>
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
                        @include('sales.pengajuan.partials.rows', ['pengajuan' => $pengajuan])
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper" id="pagination-wrapper">
                {{ $pengajuan->links() }}
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
        const month = $('#month-selector').data('month') || '';

        $.ajax({
            url: '{{ route("pengajuan.index") }}',
            type: 'GET',
            data: { ajax: true, search: q, page: page, month: month },
            success: function(res) {
                $('#pengajuan-body').html(res.html);
                $('#pagination-wrapper').html(res.pagination);
                setLoading(false);
                updateUrl(q, page, month);
            },
            error: function() {
                setLoading(false);
                alert('Terjadi kesalahan saat memuat data.');
            }
        });
    }

    function updateUrl(q, page, month) {
        const params = new URLSearchParams();
        if (q) params.set('search', q);
        if (page && page > 1) params.set('page', page);
        if (month) params.set('month', month);
        const base = '{{ route("pengajuan.index") }}';
        const u = params.toString() ? base + '?' + params.toString() : base;
        window.history.replaceState({}, '', u);
    }

    // debounce
    $('#search-input').on('input', function(){
        clearTimeout(timeout);
        timeout = setTimeout(()=> loadData(1), 300);
    });

    $('#search-button').on('click', function(){ loadData(1); });

    // pagination links (response returns proper links)
    $(document).on('click', '.pagination a', function(e){
        e.preventDefault();
        const href = $(this).attr('href') || '';
        const page = new URL(href, window.location.origin).searchParams.get('page') || 1;
        loadData(page);
    });

    // month selector (simple cycle demo)
    $('#month-selector').on('click', function(){
        // rotate months quickly (for demo)
        const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const cur = $(this).data('monthIndex') || 0;
        const next = (cur + 1) % months.length;
        $(this).data('monthIndex', next).data('month', months[next]).text(months[next] + ' ').append(' \u25B6');
        loadData(1);
    });

});
</script>
@endpush
