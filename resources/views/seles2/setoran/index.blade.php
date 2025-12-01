@extends('sales.layouts.sales-master')

@section('content')
<div class="menu-section">
    <div class="section-header">
        <div class="section-icon">
            <i class="bi bi-cash-coin"></i>
        </div>
        <h6 class="section-title">Setor Dana</h6>
    </div>
    
    <div class="p-3">
        <!-- Saldo Info -->
        <div class="card border-0 bg-primary text-white mb-4">
            <div class="card-body text-center">
                <h6 class="card-title">Saldo Tersedia</h6>
                <h3 class="fw-bold">Rp 2.090.000</h3>
                <small>Pendapatan bersih bulan ini</small>
            </div>
        </div>

        <form>
            <div class="mb-3">
                <label class="form-label">Jumlah Setoran</label>
                <input type="number" class="form-control" placeholder="Masukkan jumlah setoran">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Tanggal Setoran</label>
                <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Metode Setoran</label>
                <select class="form-select">
                    <option>Transfer Bank</option>
                    <option>Tunai</option>
                    <option>E-Wallet</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Keterangan (Optional)</label>
                <textarea class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success rounded-pill py-2">
                    <i class="bi bi-check-circle me-2"></i>Setor Sekarang
                </button>
                <a href="{{ route('sales.setoran.riwayat') }}" class="btn btn-outline-primary rounded-pill py-2">
                    <i class="bi bi-clock-history me-2"></i>Lihat Riwayat
                </a>
            </div>
        </form>
    </div>
</div>
@endsection