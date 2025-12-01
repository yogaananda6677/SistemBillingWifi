@extends('sales.layouts.sales-master')

@section('content')
<!-- Header dengan Add Button -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0">Pengajuan Pengeluaran</h6>
    <a href="{{ route('sales.pengajuan.create') }}" class="btn btn-primary btn-sm rounded-pill">
        <i class="bi bi-plus me-1"></i>Baru
    </a>
</div>

<!-- Status Filter -->
<div class="filter-tabs mb-3">
    <a href="?status=all" class="filter-tab {{ !request('status') || request('status') == 'all' ? 'active' : '' }}">Semua</a>
    <a href="?status=pending" class="filter-tab {{ request('status') == 'pending' ? 'active' : '' }}">Pending</a>
    <a href="?status=approved" class="filter-tab {{ request('status') == 'approved' ? 'active' : '' }}">Disetujui</a>
    <a href="?status=rejected" class="filter-tab {{ request('status') == 'rejected' ? 'active' : '' }}">Ditolak</a>
</div>

<!-- List Pengajuan -->
<div class="menu-section">
    @for($i = 1; $i <= 5; $i++)
    <div class="menu-list-item">
        <div class="item-icon {{ $i % 3 == 0 ? 'warning' : ($i % 3 == 1 ? 'success' : 'primary') }}">
            <i class="bi bi-wallet2"></i>
        </div>
        <div class="item-content">
            <div class="item-label">Pengajuan Transport {{ $i }}</div>
            <div class="item-desc">Rp 75.000 • {{ date('d M Y', strtotime("-{$i} days")) }}</div>
        </div>
        <div class="status-badge {{ $i % 3 == 0 ? 'bg-warning' : ($i % 3 == 1 ? 'bg-success' : 'bg-secondary') }} text-white">
            {{ $i % 3 == 0 ? 'Pending' : ($i % 3 == 1 ? 'Disetujui' : 'Ditolak') }}
        </div>
    </div>
    @endfor
</div>
@endsection