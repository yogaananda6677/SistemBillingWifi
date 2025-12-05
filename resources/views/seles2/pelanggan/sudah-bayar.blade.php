@extends('seles2.layout.master')

@section('content')
    <div class="pelanggan-page">

        {{-- HEADER (Tema Amber) --}}
        <div class="pelanggan-header d-flex align-items-center">
            <a href="{{ route('seles2.pelanggan.index') }}" class="back-btn">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="mb-0 fw-bold">Pelanggan Sudah Bayar</h5>
        </div>

        {{-- FILTER TABS (Scrollable) --}}
        <div class="filter-tabs-container mt-3 px-3">
            <div class="filter-tabs d-flex gap-2 flex-nowrap overflow-auto pb-2">
                <a href="{{ route('seles2.pelanggan.index') }}" class="filter-tab">Semua</a>
                <a href="?filter=belum-bayar" class="filter-tab">Belum Bayar</a>
                <a href="?filter=sudah-bayar" class="filter-tab active">Sudah Bayar</a>
                <a href="?filter=baru" class="filter-tab">Baru</a>
                <a href="?filter=berhenti" class="filter-tab">Berhenti</a>
            </div>
        </div>

        {{-- LIST PELANGGAN SUDAH BAYAR --}}
        <div class="pelanggan-list mt-2 px-3">
            @for ($i = 1; $i <= 6; $i++)
                <div class="pelanggan-card mb-3">
                    <div class="d-flex align-items-center w-100">

                        {{-- Avatar (Hijau untuk menandakan Lunas) --}}
                        <div class="me-3">
                            <div class="avatar-circle bg-success bg-gradient">
                                {{ chr(64 + $i) }}
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="flex-grow-1">
                            <h6 class="fw-bold text-dark mb-0">Pelanggan {{ $i }}</h6>
                            <div class="small text-muted mb-1">
                                <i class="bi bi-geo-alt-fill text-warning me-1"></i> Kediri - Ngasem
                            </div>
                            <span
                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2"
                                style="font-size: 0.7rem;">
                                <i class="bi bi-check-circle-fill me-1"></i> Lunas
                            </span>
                        </div>

                        {{-- Action Arrow --}}
                        <div class="text-end text-muted">
                            <i class="bi bi-chevron-right"></i>
                        </div>

                        {{-- Stretched Link --}}
                        <a href="#" class="stretched-link"></a>
                    </div>
                </div>
            @endfor
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Global Page Style */
        .pelanggan-page {
            background: #f9fafb;
            min-height: 100vh;
            padding-bottom: 90px;
        }

        /* 1. HEADER (Gradient Amber) */
        .pelanggan-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 20px 20px 30px 20px;
            /* Padding bawah lebih besar untuk efek lengkung */
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.25);
            margin: -16px -16px 10px -16px;
            /* Negatif margin agar full width */
            gap: 12px;
            position: relative;
            z-index: 10;
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

        /* 2. FILTER TABS */
        .filter-tabs-container {
            position: relative;
            z-index: 11;
            margin-top: 15px !important;
            /* Naik ke atas menimpa header */
        }

        /* Hilangkan scrollbar */
        .filter-tabs::-webkit-scrollbar {
            display: none;
        }

        .filter-tabs {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .filter-tab {
            background: #ffffff;
            color: #6b7280;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            white-space: nowrap;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
            transition: all 0.2s;
        }

        .filter-tab.active {
            background: #f59e0b;
            /* Amber */
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
            border-color: #f59e0b;
        }

        /* 3. CARD PELANGGAN */
        .pelanggan-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #f3f4f6;
            transition: transform 0.2s;
            position: relative;
        }

        .pelanggan-card:active {
            transform: scale(0.98);
            background-color: #fcfcfc;
        }

        .avatar-circle {
            width: 50px;
            height: 50px;
            /* bg-success dihandle oleh bootstrap class di elemen */
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: 0 3px 6px rgba(16, 185, 129, 0.2);
        }
    </style>
@endpush
