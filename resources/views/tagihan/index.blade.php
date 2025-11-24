@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">

    <h4 class="mb-4">Dashboard Tagihan Bulanan</h4>

    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" id="search-tagihan" class="form-control" placeholder="Cari pelanggan atau paket...">
        </div>
        <div class="col-md-3">
            <select id="status-tagihan" class="form-select">
                <option value="">Semua Status</option>
                <option value="lunas">Lunas</option>
                <option value="belum lunas">Belum Lunas</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="paket-tagihan" class="form-select">
                <option value="">Semua Paket</option>
                @foreach($paketList as $paket)
                    <option value="{{ $paket->id_paket }}">{{ $paket->nama_paket }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Pelanggan</th>
                <th>Paket</th>
                <th>Harga Dasar</th>
                <th>PPN</th>
                <th>Total</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="tagihan-table-body">
            @include('tagihan.partials.table', ['dataTagihan' => $dataTagihan])
        </tbody>
    </table>
    <div id="pagination-wrapper">
        {!! $dataTagihan->links() !!}
    </div>

</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let timeout = null;
    let currentPage = 1;

    function loadTagihanTable(search = '', status = '', paket = '', page = 1) {
        currentPage = page;

        $.ajax({
            url: '{{ route("tagihan.index") }}',
            type: 'GET',
            data: { search, status, paket, page, ajax: true },
            success: function(response) {
                $('#tagihan-table-body').html(response.html);
                $('#pagination-wrapper').html(response.pagination);

                // Update URL tanpa reload
                const params = new URLSearchParams();
                if(search) params.set('search', search);
                if(status) params.set('status', status);
                if(paket) params.set('paket', paket);
                if(page > 1) params.set('page', page);
                const newUrl = params.toString() ? '{{ route("tagihan.index") }}?' + params.toString() : '{{ route("tagihan.index") }}';
                window.history.replaceState({}, '', newUrl);
            },
            error: function(xhr) {
                console.error('Error load tagihan:', xhr);
                alert('Terjadi kesalahan saat memuat data tagihan');
            }
        });
    }

    // Event search dengan debounce
    $('#search-tagihan').on('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            loadTagihanTable($('#search-tagihan').val(), $('#status-tagihan').val(), $('#paket-tagihan').val(), 1);
        }, 300);
    });

    // Event filter status
    $('#status-tagihan').on('change', function() {
        loadTagihanTable($('#search-tagihan').val(), $(this).val(), $('#paket-tagihan').val(), 1);
    });

    // Event filter paket
    $('#paket-tagihan').on('change', function() {
        loadTagihanTable($('#search-tagihan').val(), $('#status-tagihan').val(), $(this).val(), 1);
    });

    // Pagination click (AJAX)
    $(document).on('click', '.pagination a', function(e){
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1] || 1;
        loadTagihanTable($('#search-tagihan').val(), $('#status-tagihan').val(), $('#paket-tagihan').val(), page);
    });

    // Load awal dari URL params
    const urlParams = new URLSearchParams(window.location.search);
    const initialSearch = urlParams.get('search') || '';
    const initialStatus = urlParams.get('status') || '';
    const initialPaket = urlParams.get('paket') || '';
    const initialPage = urlParams.get('page') || 1;

    $('#search-tagihan').val(initialSearch);
    $('#status-tagihan').val(initialStatus);
    $('#paket-tagihan').val(initialPaket);

    loadTagihanTable(initialSearch, initialStatus, initialPaket, initialPage);
});
</script>
@endpush

@endsection
