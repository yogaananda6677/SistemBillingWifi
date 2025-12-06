@extends('seles2.layout.master')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Riwayat Setoran</h6>
        <a href="{{ route('seles2.setoran.index') }}" class="btn btn-primary btn-sm rounded-pill">
            <i class="bi bi-plus me-1"></i>Setor Baru
        </a>
    </div>

    <!-- Summary -->
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="stat-card text-center">
                <div class="stat-value text-success">5</div>
                <div class="stat-label">Total Setor</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card text-center">
                <div class="stat-value text-primary">Rp 8,5JT</div>
                <div class="stat-label">Total Dana</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card text-center">
                <div class="stat-value text-warning">2</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>

    <!-- List Riwayat -->
    <div class="menu-section">
        @for ($i = 1; $i <= 6; $i++)
            <div class="menu-list-item">
                <div class="item-icon {{ $i % 3 == 0 ? 'warning' : ($i % 3 == 1 ? 'success' : 'primary') }}">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div class="item-content">
                    <div class="item-label">Setoran {{ $i }}</div>
                    <div class="item-desc">Rp {{ number_format(500000 + $i * 100000, 0, ',', '.') }} •
                        {{ date('d M Y', strtotime("-{$i} days")) }}</div>
                </div>
                <div
                    class="status-badge {{ $i % 3 == 0 ? 'bg-warning' : ($i % 3 == 1 ? 'bg-success' : 'bg-secondary') }} text-white">
                    {{ $i % 3 == 0 ? 'Pending' : ($i % 3 == 1 ? 'Diterima' : 'Ditolak') }}
                </div>
            </div>
        @endfor
    </div>
@endsection
