@extends('seles2.layout.master')

@section('content')
    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="{{ route('seles2.pelanggan.index') }}" class="filter-tab">Semua</a>
        <a href="?filter=belum-bayar" class="filter-tab">Belum Bayar</a>
        <a href="?filter=sudah-bayar" class="filter-tab active">Sudah Bayar</a>
        <a href="?filter=baru" class="filter-tab">Baru</a>
        <a href="?filter=berhenti" class="filter-tab">Berhenti</a>
    </div>

    <!-- List Pelanggan Sudah Bayar -->
    <div class="pelanggan-list">
        @for ($i = 1; $i <= 6; $i++)
            <div class="pelanggan-card">
                <div class="pelanggan-avatar">
                    {{ chr(64 + $i) }}
                </div>
                <div class="pelanggan-info">
                    <h6 class="pelanggan-nama">Pelanggan {{ $i }}</h6>
                    <p class="pelanggan-wilayah">Kediri - Ngasem</p>
                    <div class="pelanggan-status sudah-bayar">
                        Sudah Bayar
                    </div>
                </div>
                <div class="pelanggan-action">
                    <i class="bi bi-chevron-right"></i>
                </div>
            </div>
        @endfor
    </div>
@endsection
