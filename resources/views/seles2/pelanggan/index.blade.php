@extends('seles2.layout.sales-master')

@section('content')
    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="{{ route('pelanggan.index') }}" class="filter-tab {{ !request('filter') ? 'active' : '' }}">
            Semua
        </a>
        <a href="?filter=belum-bayar" class="filter-tab {{ request('filter') == 'belum-bayar' ? 'active' : '' }}">
            Belum Bayar
        </a>
        <a href="?filter=sudah-bayar" class="filter-tab {{ request('filter') == 'sudah-bayar' ? 'active' : '' }}">
            Sudah Bayar
        </a>
        <a href="?filter=baru" class="filter-tab {{ request('filter') == 'baru' ? 'active' : '' }}">
            Baru
        </a>
        <a href="?filter=berhenti" class="filter-tab {{ request('filter') == 'berhenti' ? 'active' : '' }}">
            Berhenti
        </a>
    </div>

    <!-- List Pelanggan -->
    <div class="pelanggan-list">
        @for ($i = 1; $i <= 8; $i++)
            <div class="pelanggan-card">
                <div class="pelanggan-avatar">
                    {{ chr(64 + $i) }}
                </div>
                <div class="pelanggan-info">
                    <h6 class="pelanggan-nama">Pelanggan {{ $i }}</h6>
                    <p class="pelanggan-wilayah">Kediri - Ngasem</p>
                    <div class="pelanggan-status {{ $i % 3 == 0 ? 'belum-bayar' : 'sudah-bayar' }}">
                        {{ $i % 3 == 0 ? 'Belum Bayar' : 'Sudah Bayar' }}
                    </div>
                </div>
                <div class="pelanggan-action">
                    <i class="bi bi-chevron-right"></i>
                </div>
            </div>
        @endfor
    </div>

    <!-- Add Button -->
    <a href="{{ route('sales.pelanggan.create') }}" class="btn btn-primary rounded-pill position-fixed"
        style="bottom: 100px; right: 20px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
        <i class="bi bi-plus fs-4"></i>
    </a>
@endsection
