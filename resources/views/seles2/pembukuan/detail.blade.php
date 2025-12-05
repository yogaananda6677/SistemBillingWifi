@extends('seles2.layout.master')

@section('content')
    <div class="menu-section">
        <div class="section-header">
            <div class="section-icon">
                <i class="bi bi-receipt"></i>
            </div>
            <h6 class="section-title">Detail Pembukuan</h6>
        </div>

        <div class="p-3">
            <!-- Period Selector -->
            <div class="mb-3">
                <label class="form-label">Periode</label>
                <select class="form-select">
                    <option>November 2023</option>
                    <option>Oktober 2023</option>
                    <option>September 2023</option>
                </select>
            </div>

            <!-- Income Section -->
            <div class="card mb-3 border-0 bg-light">
                <div class="card-body">
                    <h6 class="card-title d-flex justify-content-between">
                        <span>Pendapatan Pelanggan</span>
                        <span class="text-success">Rp 2.400.000</span>
                    </h6>
                    <div class="small text-muted">
                        <div class="d-flex justify-content-between">
                            <span>Pelanggan Aktif:</span>
                            <span>18 orang</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fee Section -->
            <div class="card mb-3 border-0 bg-light">
                <div class="card-body">
                    <h6 class="card-title d-flex justify-content-between">
                        <span>Fee Sales (10%)</span>
                        <span class="text-primary">Rp 240.000</span>
                    </h6>
                    <div class="small text-muted">
                        Komisi dari total pendapatan
                    </div>
                </div>
            </div>

            <!-- Expenses Section -->
            <div class="card mb-3 border-0 bg-light">
                <div class="card-body">
                    <h6 class="card-title d-flex justify-content-between">
                        <span>Pengeluaran Operasional</span>
                        <span class="text-warning">Rp 150.000</span>
                    </h6>
                    <div class="small text-muted">
                        <div class="d-flex justify-content-between">
                            <span>Transport:</span>
                            <span>Rp 75.000</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Lainnya:</span>
                            <span>Rp 75.000</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Net Income -->
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title d-flex justify-content-between">
                        <span>Pendapatan Bersih</span>
                        <span>Rp 2.090.000</span>
                    </h6>
                    <div class="small opacity-75">
                        Setelah dikurangi fee dan pengeluaran
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
