@extends('seles2.layout.master')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="pelanggan-page">

    {{-- HEADER: Judul dan Tombol Tambah --}}
    <div class="pelanggan-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <a href="{{ route('dashboard-sales') }}" class="back-btn">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="mb-0 fw-semibold ms-2">Pengajuan Pengeluaran</h5>
        </div>

        <a href="{{ route('sales.pengajuan.create') }}" class="btn btn-light btn-sm rounded-pill">
            <i class="bi bi-plus-circle"></i> Tambah
        </a>
    </div>

    {{-- FILTER & PENCARIAN --}}
    <div class="filter-bar mt-3 px-3">
        <form id="filter-form"
              method="GET"
              action="{{ route('sales.pengajuan.index') }}"
              class="w-100">

            {{-- BARIS 1: search --}}
            <div class="d-flex gap-2 mb-2">
                <div class="flex-grow-1">
                    <input
                        type="text"
                        name="search"
                        id="search-input"
                        class="form-control form-control-sm"
                        placeholder="Cari nama pengeluaran / nominal..."
                        value="{{ request('search') }}"
                    >
                </div>
            </div>

            {{-- BARIS 2: status + tanggal --}}
            <div class="d-flex gap-2 mb-2">
                <div class="flex-grow-1">
                    <select name="status" class="form-select form-select-sm rounded-pill">
                        <option value="">Semua status</option>
                        <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Menunggu</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div class="flex-grow-1">
                    <input
                        type="date"
                        name="tanggal"
                        class="form-control form-control-sm rounded-pill"
                        value="{{ request('tanggal') }}"
                    >
                </div>
            </div>
        </form>
    </div>

    {{-- FLASH MESSAGE --}}
    <div class="px-3 mt-2">
        @if(session('success'))
            <div class="alert alert-success py-2 mb-2">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger py-2 mb-2">{{ session('error') }}</div>
        @endif
    </div>

    {{-- LIST PENGAJUAN --}}
    <div class="pelanggan-list mt-3 px-3">
        @forelse ($pengajuan as $row)
            @php
                $status = $row->status_approve;

                $statusData = match($status) {
                    'approved' => ['text' => 'DISETUJUI', 'class' => 'badge-approved'],
                    'rejected' => ['text' => 'DITOLAK', 'class' => 'badge-rejected'],
                    default    => ['text' => 'MENUNGGU', 'class' => 'badge-pending']
                };

                $statusText = $statusData['text'];
                $badgeClass = $statusData['class'];

                $tanggalPengajuan = $row->tanggal_pengajuan
                    ? Carbon::parse($row->tanggal_pengajuan)->translatedFormat('d F Y, H:i')
                    : '-';

                $modalId = 'modal-pengajuan-' . $row->id_pengeluaran;
            @endphp

            {{-- CARD (tap -> buka modal detail) --}}
            <div
                class="pelanggan-card pengajuan-card position-relative"
                data-bs-toggle="modal"
                data-bs-target="#{{ $modalId }}"
            >
                <div class="row g-0 align-items-center w-100 m-0">
                    {{-- KIRI: info utama --}}
                    <div class="col-7">
                        <div class="d-flex flex-column mb-1">
                            <div class="fw-bold pelanggan-nama text-truncate">
                                {{ $row->nama_pengeluaran }}
                            </div>
                            <div class="text-muted small text-truncate">
                                Sales: {{ auth()->user()->name }}
                            </div>
                        </div>

                        <div class="small text-muted mt-1 text-truncate">
                            {{ $tanggalPengajuan }}
                        </div>
                    </div>

                    {{-- KANAN: status & nominal --}}
                    <div class="col-5 ps-2 harga-col d-flex flex-column justify-content-between">
                        <div class="d-flex flex-column align-items-end w-100">
                            {{-- STATUS di kanan atas --}}
                            <span class="status-badge {{ $badgeClass }} mb-1">
                                {{ $statusText }}
                            </span>

                            {{-- NOMINAL --}}
                            <div class="text-end w-100 mt-2">
                                <span class="harga-label">Rp.</span>
                                <span class="harga-value">
                                    {{ number_format($row->nominal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MODAL DETAIL PENGAJUAN (kecil, center) --}}
            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-mobile">
                    <div class="modal-content">

                        <div class="modal-header py-2">
                            <div>
                                <h6 class="modal-title mb-0">Detail Pengajuan</h6>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body p-3 small">
                            <div class="mb-3 info-box">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted tiny">Nama Pengeluaran</span>
                                    <span class="fw-semibold text-end">
                                        {{ $row->nama_pengeluaran }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted tiny">Sales</span>
                                    <span class="text-end">{{ auth()->user()->name }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted tiny">Tanggal Pengajuan</span>
                                    <span class="text-end">{{ $tanggalPengajuan }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted tiny">Status</span>
                                    <span class="text-end">{{ $statusText }}</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted tiny">Total Pengajuan</span>
                                    <span class="fw-bold text-success">
                                        Rp {{ number_format($row->nominal, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="text-muted tiny">Bukti</span>
                                    <span class="text-end">
                                        @if($row->bukti_file)
                                            <a href="{{ route('sales.pengajuan.bukti', $row->id_pengeluaran) }}"
                                               target="_blank"
                                               class="small text-decoration-none">
                                                <i class="bi bi-paperclip me-1"></i>Lihat Bukti
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer py-2">
                            @if($status === 'pending')
                                <div class="w-100 d-flex gap-2">
                                    {{-- TOMBOL EDIT --}}
                                    <a href="{{ route('sales.pengajuan.edit', $row->id_pengeluaran) }}"
                                       class="btn btn-outline-primary btn-sm w-50">
                                        Edit
                                    </a>

                                    {{-- TOMBOL HAPUS --}}
                                    <form action="{{ route('sales.pengajuan.destroy', $row->id_pengeluaran) }}"
                                          method="POST"
                                          class="w-50"
                                          onsubmit="return confirm('Yakin ingin menghapus pengajuan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            @else
                                <button class="btn btn-secondary btn-sm w-100" data-bs-dismiss="modal">
                                    Tutup
                                </button>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

        @empty
            <div class="text-center text-muted mt-4 p-4 bg-white rounded-3 shadow-sm mx-3">
                <i class="bi bi-file-earmark-text display-4 mb-2"></i>
                <p>Belum ada pengajuan pengeluaran.</p>
            </div>
        @endforelse

        {{-- PAGINATION --}}
        @if(method_exists($pengajuan, 'links'))
            <div class="mt-3">
                {{ $pengajuan->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    {{-- Footer Hint --}}
    <div class="hint-footer text-center mt-3 mb-2">
        Tap kartu untuk lihat detail pengajuan.
    </div>
</div>
@endsection

@push('styles')
<style>
    .pelanggan-page {
        background: #f1f3f6;
        min-height: 100vh;
        padding-bottom: 70px; /* ruang bottom nav */
    }

    /* HEADER */
    .pelanggan-header {
        background: #4f46e5;
        color: #fff;
        padding: 12px 16px;
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        margin: -12px -12px 12px -12px;
        gap: 12px;
    }

    .back-btn {
        color: #fff;
        text-decoration: none;
        font-size: 1.2rem;
    }

    /* FILTER BAR */
    .filter-bar {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-bar input,
    .filter-bar select {
        font-size: 0.85rem;
        border-radius: 999px;
    }

    /* LIST */
    .pelanggan-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin: 0;
    }

    /* CARD */
    .pelanggan-card {
        display: block !important;
        width: 100% !important;
        background: #ffffff;
        padding: 14px;
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .pelanggan-nama {
        font-size: 1rem;
        color: #111827;
    }

    .pelanggan-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.08);
        transition: all 0.15s;
    }

    /* KANAN (INFO & NOMINAL) */
    .harga-col {
        border-left: 1px solid #e5e7eb;
        padding-left: 12px !important;
        text-align: right;
    }

    .harga-label {
        color: #111827;
        font-weight: 700;
        font-size: 0.9rem;
        margin-right: 2px;
    }

    .harga-value {
        color: #111827;
        font-weight: 800;
        font-size: 1.1rem;
    }

    .hint-footer {
        background: #4f46e5;
        color: #fff;
        padding: 8px 12px;
        border-radius: 12px;
        font-size: 0.8rem;
        margin: 12px 12px 0 12px;
    }

    .info-box {
        background: #f9fafb;
        border-radius: 12px;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
    }

    .tiny {
        font-size: 11px;
    }

    /* MODAL KECIL, MOBILE FRIENDLY */
    .modal-mobile {
        max-width: 420px;
        margin: 0.5rem auto;
    }

    @media (max-width: 575.98px) {
        .modal-mobile {
            max-width: 95%;
            margin: 1.25rem auto;
        }
        .modal-mobile .modal-content {
            border-radius: 16px;
        }
    }

    /* Badge Status */
    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-approved { background: #e6f8e8; color: #16a34a; }
    .badge-rejected { background: #fde8e8; color: #ef4444; }
    .badge-pending  { background: #fff7e5; color: #d97706; }

    .pengajuan-actions .btn-icon {
        padding: 4px 8px;
        line-height: 1;
        border-radius: 999px;
    }

    .btn-xs {
        font-size: 0.7rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form   = document.getElementById('filter-form');
    if (!form) return;

    const inputs = form.querySelectorAll('input[name], select[name]');
    let timer    = null;

    function submitWithDebounce() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            form.submit();
        }, 300);
    }

    inputs.forEach(el => {
        const eventName =
            (el.tagName === 'INPUT' && el.type === 'text')
                ? 'input'
                : 'change';

        el.addEventListener(eventName, submitWithDebounce);
    });
});
</script>
@endpush
