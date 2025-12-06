@extends('seles2.layout.master')

@section('content')
    <div class="pelanggan-page">

        {{-- 1. HEADER (Gradient Amber) --}}
        <div class="pelanggan-header d-flex align-items-center justify-content-center px-3 pt-3 pb-5">
            <a href="{{ route('dashboard-sales') }}" class="back-btn position-absolute start-0 ms-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="mb-0 fw-bold text-center text-white">Detail Pembukuan</h5>
        </div>

        {{-- 2. CONTENT CARD (Floating) --}}
        <div class="px-3" style="margin-top: -35px; position: relative; z-index: 20;">
            <div class="bg-white rounded-4 shadow-sm p-4 border border-light">

                {{-- PERIODE SELECTOR --}}
                <div class="mb-4">
                    <label class="small text-muted fw-bold mb-1 text-uppercase">Periode Laporan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-calendar-event"></i>
                        </span>
                        <select class="form-select border-start-0 bg-light fw-bold text-dark">
                            <option selected>November 2023</option>
                            <option>Oktober 2023</option>
                            <option>September 2023</option>
                        </select>
                    </div>
                </div>

                {{-- 1. PENDAPATAN (INCOME) --}}
                <div
                    class="card border-0 bg-success bg-opacity-10 border-success border-opacity-25 mb-3 rounded-4 overflow-hidden">
                    <div class="card-body p-3 position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="icon-circle bg-success text-white">
                                    <i class="bi bi-arrow-down-left"></i>
                                </div>
                                <h6 class="mb-0 fw-bold text-success-dark">Pendapatan</h6>
                            </div>
                            <h5 class="mb-0 fw-bold text-success-dark">Rp 2.400.000</h5>
                        </div>

                        {{-- Detail Accordion / List --}}
                        <div class="bg-white bg-opacity-50 rounded-3 p-2 mt-2">
                            <div class="d-flex justify-content-between align-items-center small text-muted">
                                <span><i class="bi bi-people-fill me-1"></i> Pelanggan Aktif</span>
                                <span class="fw-bold text-dark">18 Orang</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DIVIDER ARROW (Visual Flow) --}}
                <div class="text-center text-muted mb-3" style="font-size: 0.8rem; margin-top: -10px; opacity: 0.5;">
                    <i class="bi bi-arrow-down fs-4"></i>
                </div>

                {{-- 2. FEE SALES (OUT) --}}
                <div class="card border-0 bg-light mb-2 rounded-4">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <div class="icon-circle bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-percent"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">Fee Sales (10%)</h6>
                                    <small class="text-muted tiny">Potongan Komisi</small>
                                </div>
                            </div>
                            <h6 class="mb-0 fw-bold text-danger">- Rp 240.000</h6>
                        </div>
                    </div>
                </div>

                {{-- 3. OPERASIONAL (OUT) --}}
                <div class="card border-0 bg-light mb-4 rounded-4">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="icon-circle bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-cart"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">Operasional</h6>
                                    <small class="text-muted tiny">Pengeluaran Disetujui</small>
                                </div>
                            </div>
                            <h6 class="mb-0 fw-bold text-danger">- Rp 150.000</h6>
                        </div>

                        {{-- Detail --}}
                        <div class="border-top pt-2 mt-1">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Transport</span>
                                <span>Rp 75.000</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted">
                                <span>Lainnya</span>
                                <span>Rp 75.000</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. TOTAL NETTO (HIGHLIGHT) --}}
                <div class="total-card bg-gradient-amber text-white p-4 rounded-4 shadow-sm text-center">
                    <small class="text-white text-opacity-75 fw-bold letter-spacing-1">PENDAPATAN BERSIH</small>
                    <h2 class="fw-bold mb-0 mt-1">Rp 2.090.000</h2>
                    <div class="mt-2 text-white text-opacity-75 tiny border-top border-white border-opacity-25 pt-2">
                        Siap untuk disetorkan
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer spacer --}}
        <div style="height: 100px;"></div>
    </div>
@endsection

@push('styles')
    <style>
        /* Global Page Style */
        .pelanggan-page {
            background: #f9fafb;
            min-height: 100vh;
        }

        /* HEADER */
        .pelanggan-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.25);
            margin: -16px -16px 0 -16px;
        }

        .back-btn {
            color: white;
            text-decoration: none;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            justify-content: center;
            transition: 0.2s;
        }

        .back-btn:active {
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0.9);
        }

        /* CUSTOM ELEMENTS */
        .icon-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .text-success-dark {
            color: #065f46;
            /* Emerald darker */
        }

        .bg-gradient-amber {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        }

        .letter-spacing-1 {
            letter-spacing: 1px;
        }

        .tiny {
            font-size: 0.75rem;
        }

        /* Form Styles */
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-color: #f8f9fa;
            border: 1px solid #f8f9fa;
        }

        .form-select:focus {
            border-color: #f59e0b;
            box-shadow: none;
            background-color: #fff;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #f8f9fa;
        }
    </style>
@endpush
