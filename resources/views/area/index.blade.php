@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">

    <h4 class="mb-4">Dashboard Tagihan Bulanan</h4>

    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" id="search" class="form-control" placeholder="Cari pelanggan atau paket...">
        </div>
        <div class="col-md-3">
            <select id="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="lunas">Lunas</option>
                <option value="belum_lunas">Belum Lunas</option>
            </select>
        </div>
    </div>

    <div id="tagihan-table">
        @include('tagihan.partials.table', ['dataTagihan' => $dataTagihan])
    </div>

</div>
@endsection

@section('scripts')
<script>
$(document).ready(function(){

    function fetchTagihan(page = 1){
        var search = $('#search').val();
        var status = $('#status').val();

        $.ajax({
            url: "{{ route('tagihan.index') }}",
            type: "GET",
            data: {
                search: search,
                status: status,
                page: page
            },
            success: function(data){
                $('#tagihan-table').html(data);
            }
        });
    }

    $('#search').on('keyup', function(){
        fetchTagihan();
    });

    $('#status').on('change', function(){
        fetchTagihan();
    });

    // Pagination link click
    $(document).on('click', '.pagination a', function(e){
        e.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        fetchTagihan(page);
    });

});
</script>
@endsection
