@extends('seles2.layout.master')

@php
    use Carbon\Carbon;
@endphp

@section('content')
    <div class="pelanggan-page">

        {{-- 1. HEADER (Gradient Amber) --}}
        <div class="pelanggan-header d-flex align-items-center justify-content-center px-3 pt-3 pb-5">
            <a href="{{ route('dashboard-sales') }}" class="back-btn position-absolute start-0 ms-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="mb-0 fw-bold text-center text-white">Pengajuan Pengeluaran</h5>
        </div>

        {{-- 2. TOMBOL TAMBAH (Floating) --}}
        <div class="px-3" style="margin-top: -25px; position: relative; z-index: 20;">
            <a href="{{ route('sales.pengajuan.create') }}"
                class="btn btn-white w-100 rounded-pill shadow-sm py-2 fw-bold d-flex align-items-center justify-content-center gap-2"
                style="color: #d97706;">
                <i class="bi bi-plus-circle-fill fs-5"></i>
                <span>Buat Pengajuan Baru</span>
            </a>
        </div>

        {{-- 3. FILTER & PENCARIAN --}}
        <div class="filter-bar mt-3 px-1">
            <form id="filter-form" method="GET" action="{{ route('sales.pengajuan.index') }}" class="w-100">

                <div class="d-flex gap-2 mb-2">
                    <div class="flex-grow-1">
                        <div class="input-group input-group-sm shadow-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" id="search-input"
                                class="form-control border-start-0 rounded-end-pill ps-0"
                                placeholder="Cari pengeluaran / nominal..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-2">
                    <div class="flex-grow-1">
                        <select name="status" class="form-select form-select-sm rounded-pill shadow-sm text-muted">
                            <option value="">Semua status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu
                            </option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui
                            </option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak
                            </option>
                        </select>
                    </div>
                    <div class="flex-grow-1">
                        <input type="date" name="tanggal"
                            class="form-control form-control-sm rounded-pill shadow-sm text-muted"
                            value="{{ request('tanggal') }}">
                    </div>
                </div>
            </form>
        </div>

        {{-- 4. FLASH MESSAGE --}}
        <div class="px-3 mt-2">
            @if (session('success'))
                <div
                    class="alert alert-success py-2 mb-2 small rounded-3 border-success border-opacity-25 bg-success bg-opacity-10 text-success fw-bold fade show">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div
                    class="alert alert-danger py-2 mb-2 small rounded-3 border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger fw-bold fade show">
                    <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
                </div>
            @endif
        </div>

        {{-- 5. LIST PENGAJUAN --}}
        <div class="pelanggan-list mt-3">
            @forelse ($pengajuan as $row)
                @php
                    $status = $row->status_approve;

                    // Visual Mapping
                    if ($status === 'approved') {
                        $statusText = 'DISETUJUI';
                        $badgeClass = 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                        $cardBorderClass = 'card-approved';
                    } elseif ($status === 'rejected') {
                        $statusText = 'DITOLAK';
                        $badgeClass = 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25';
                        $cardBorderClass = 'card-rejected';
                    } else {
                        $statusText = 'MENUNGGU';
                        $badgeClass = 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25';
                        $cardBorderClass = 'card-pending';
                    }

                    $tanggalPengajuan = $row->tanggal_pengajuan
                        ? Carbon::parse($row->tanggal_pengajuan)->translatedFormat('d F Y, H:i')
                        : '-';

                    $modalIdDetail = 'modal-detail-' . $row->id_pengeluaran;
                    $modalIdDelete = 'modal-delete-' . $row->id_pengeluaran;
                @endphp

                {{-- A. CARD UTAMA --}}
                <div class="pelanggan-card position-relative {{ $cardBorderClass }} mb-3" data-bs-toggle="modal"
                    data-bs-target="#{{ $modalIdDetail }}">
                    <div class="row g-0 align-items-center w-100 m-0">
                        {{-- Kiri --}}
                        <div class="col-7 pe-2">
                            <div class="d-flex flex-column mb-1">
                                <div class="fw-bold text-dark text-truncate pelanggan-nama">
                                    {{ $row->nama_pengeluaran }}
                                </div>
                                    <div class="text-muted small text-truncate" style="font-size: 0.7rem;">
                                        Sales: {{ auth()->user()->name }}
                                        @if(!empty($row->nama_area))
                                            · {{ $row->nama_area }}
                                        @endif
                                    </div>
                                <div class="text-muted small text-truncate" style="font-size: 0.7rem;">
                                    Sales: {{ auth()->user()->name }}
                                </div>
                            </div>
                            <div class="small text-muted mt-1 text-truncate" style="font-size: 0.75rem;">
                                <i class="bi bi-calendar3 me-1"></i> {{ $tanggalPengajuan }}
                            </div>
                        </div>
                        {{-- Kanan --}}
                        <div class="col-5 ps-3 harga-col d-flex flex-column justify-content-between h-100">
                            <div class="d-flex flex-column align-items-end w-100">
                                <span class="badge {{ $badgeClass }} rounded-pill mb-1 shadow-sm"
                                    style="font-size: 0.65rem;">
                                    {{ $statusText }}
                                </span>
                            </div>
                            <div class="text-end w-100 mt-2 pt-2 border-top border-light">
                                <span class="harga-label small text-muted me-1" style="font-size: 0.7rem;">Rp</span>
                                <span class="harga-value fw-bold text-dark" style="font-size: 1.1rem;">
                                    {{ number_format($row->nominal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- B. MODAL DETAIL (DIPERBAIKI) --}}
                <div class="modal fade" id="{{ $modalIdDetail }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-mobile">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

                            {{-- Header Modal dengan Warna Status --}}
                            <div class="modal-header border-0 pb-0 pt-3 px-3">
                                <h6 class="modal-title fw-bold text-dark">Detail Pengajuan</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body p-3">
                                {{-- Info Box --}}
                                <div class="bg-light border border-secondary border-opacity-10 rounded-3 p-3 mb-3">
                                    <div class="text-center mb-3">
                                        <small class="text-muted text-uppercase d-block mb-1"
                                            style="font-size: 0.7rem;">Total Nominal</small>
                                        <h3 class="fw-bold text-dark mb-0">Rp
                                            {{ number_format($row->nominal, 0, ',', '.') }}</h3>
                                        <span class="badge {{ $badgeClass }} rounded-pill mt-2 px-3">
                                            {{ $statusText }}
                                        </span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2 small border-bottom pb-2">
                                        <span class="text-muted">Keperluan</span>
                                        <span class="fw-bold text-end text-dark">{{ $row->nama_pengeluaran }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2 small border-bottom pb-2">
                                        <span class="text-muted">Wilayah</span>
                                        <span class="text-end text-dark">{{ $row->nama_area ?? '-' }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2 small border-bottom pb-2">
                                        <span class="text-muted">Tanggal</span>
                                        <span class="text-end text-dark">{{ $tanggalPengajuan }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Diajukan Oleh</span>
                                        <span class="text-end text-dark">{{ auth()->user()->name }}</span>
                                    </div>

                                </div>

                                
                                {{-- Catatan (Jika ada) --}}
                                @if ($row->catatan)
                                    <div class="mb-3">
                                        <label class="small fw-bold text-muted mb-1">Catatan</label>
                                        <div class="p-2 bg-white border rounded text-dark small">
                                            {{ $row->catatan }}
                                        </div>
                                    </div>
                                @endif

                                {{-- Tombol Bukti --}}
                                @if ($row->bukti_file)
                                    <a href="{{ route('sales.pengajuan.bukti', $row->id_pengeluaran) }}" target="_blank"
                                        class="btn btn-outline-secondary w-100 btn-sm rounded-pill mb-3 dashed-border">
                                        <i class="bi bi-paperclip me-1"></i> Lihat Bukti Lampiran
                                    </a>
                                @else
                                    <div class="text-center text-muted small mb-3 fst-italic">
                                        Tidak ada bukti lampiran.
                                    </div>
                                @endif
                            </div>

                            {{-- Footer Action --}}
                            <div class="modal-footer border-0 bg-light px-3 py-3">
                                @if ($status === 'pending')
                                    <div class="d-flex w-100 gap-2">
                                        {{-- Tombol Edit --}}
                                        <a href="{{ route('sales.pengajuan.edit', $row->id_pengeluaran) }}"
                                            class="btn btn-primary w-50 rounded-pill fw-bold btn-action-amber">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </a>

                                        {{-- Tombol Hapus (Trigger Modal Delete) --}}
                                        <button type="button"
                                            class="btn btn-white text-danger w-50 rounded-pill fw-bold border-danger"
                                            data-bs-toggle="modal" data-bs-target="#{{ $modalIdDelete }}">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </button>
                                    </div>
                                @else
                                    <button class="btn btn-secondary w-100 rounded-pill fw-bold" data-bs-dismiss="modal">
                                        Tutup
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- C. MODAL DELETE (MODERN) --}}
                @if ($status === 'pending')
                    <div class="modal fade" id="{{ $modalIdDelete }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" style="max-width: 350px; margin: auto;">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-body p-4 text-center">
                                    {{-- Ikon Sampah Besar --}}
                                    <div class="mb-3 d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle"
                                        style="width: 70px; height: 70px;">
                                        <i class="bi bi-trash3-fill text-danger display-5"></i>
                                    </div>

                                    <h5 class="fw-bold mb-2">Hapus Pengajuan?</h5>
                                    <p class="text-muted small mb-4">
                                        Apakah Anda yakin ingin menghapus pengajuan
                                        <strong>"{{ $row->nama_pengeluaran }}"</strong>?<br>
                                        Tindakan ini tidak dapat dibatalkan.
                                    </p>

                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold"
                                            data-bs-dismiss="modal">
                                            Batal
                                        </button>

                                        <form action="{{ route('sales.pengajuan.destroy', $row->id_pengeluaran) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                                                Ya, Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            @empty
                <div class="text-center mt-5 py-5 px-3">
                    <div class="mb-3">
                        <i class="bi bi-wallet2 text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                    <h6 class="text-muted fw-bold">Belum ada pengajuan</h6>
                    <p class="text-muted small">Tap tombol di atas untuk membuat pengajuan baru.</p>
                </div>
            @endforelse

            {{-- PAGINATION --}}
            @if (method_exists($pengajuan, 'links'))
                <div class="mt-4 px-2">
                    {{ $pengajuan->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        {{-- Footer Hint --}}
        <div class="hint-footer text-center mt-3 mb-2 mx-3 shadow-sm">
            <i class="bi bi-hand-index-thumb me-1"></i> Tap kartu untuk detail
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

        /* 1. HEADER (Gradient Kuning Emas) */
        .pelanggan-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.25);
            margin: -16px -16px 0 -16px;
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

        /* TOMBOL TAMBAH (Floating Button) */
        .btn-white {
            background: #ffffff;
            border: 1px solid #f3f4f6;
            transition: all 0.2s;
        }

        .btn-white:active {
            background: #fdfdfd;
            transform: scale(0.98);
        }

        /* 2. FILTER BAR */
        .filter-bar input,
        .filter-bar select,
        .input-group-text {
            border: 1px solid #f3f4f6;
            height: 40px;
            font-size: 0.85rem;
        }

        .filter-bar input:focus,
        .filter-bar select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
        }

        /* 3. CARD PENGAJUAN */
        .pelanggan-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #f3f4f6;
            transition: transform 0.2s;
            cursor: pointer;
        }

        .pelanggan-card:active {
            transform: scale(0.98);
            background-color: #fcfcfc;
        }

        /* Indikator Status (Border Kiri Tebal) */
        .card-pending {
            border-left: 5px solid #f59e0b !important;
        }

        .card-approved {
            border-left: 5px solid #10b981 !important;
        }

        .card-rejected {
            border-left: 5px solid #ef4444 !important;
        }

        .harga-col {
            border-left: 1px dashed #e5e7eb;
        }

        /* 4. FOOTER HINT */
        .hint-footer {
            background: #fffbeb;
            color: #d97706;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid #fcd34d;
        }

        /* 5. MODAL STYLING */
        .modal-mobile {
            max-width: 420px;
            margin: 0.5rem auto;
        }

        @media (max-width: 575.98px) {
            .modal-mobile {
                max-width: 95%;
                margin: 1rem auto;
            }
        }

        .btn-action-amber {
            background: #d97706;
            border: none;
            color: white;
        }

        .btn-action-amber:hover {
            background: #b45309;
            color: white;
        }

        .dashed-border {
            border-style: dashed !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filter-form');
            if (!form) return;

            const inputs = form.querySelectorAll('input[name], select[name]');
            let timer = null;

            function submitWithDebounce() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    form.submit();
                }, 300);
            }

            inputs.forEach(el => {
                const eventName =
                    (el.tagName === 'INPUT' && el.type === 'text') ?
                    'input' :
                    'change';

                el.addEventListener(eventName, submitWithDebounce);
            });
        });
    </script>
@endpush
