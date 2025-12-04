@extends('seles2.layout.master')

@section('content')
<div class="pelanggan-detail-page">

    {{-- HEADER --}}
    <div class="pelanggan-header d-flex align-items-center">
        <a href="{{ route('seles2.pelanggan.index') }}" class="back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h5 class="mb-0 fw-semibold">Detail Pelanggan</h5>
    </div>

    {{-- PROFILE / RINGKASAN ATAS --}}
    <div class="profile-header mt-3">
        <div class="profile-avatar">
            {{ strtoupper(substr($pelanggan->nama ?? 'P', 0, 1)) }}
        </div>
        <h5 class="profile-name mb-1">{{ $pelanggan->nama }}</h5>

        @php
            $status = $pelanggan->status_pelanggan_efektif ?? $pelanggan->status_pelanggan;

            $statusLabel = [
                'baru'     => 'Baru',
                'aktif'    => 'Aktif',
                'isolir'   => 'Isolir',
                'berhenti' => 'Berhenti',
            ][$status] ?? ucfirst($status);

            $statusClass = match ($status) {
                'baru'     => 'badge bg-warning text-dark',
                'aktif'    => 'badge bg-success',
                'isolir'   => 'badge bg-secondary',
                default    => 'badge bg-danger',
            };

            $areaName  = $pelanggan->area->nama_area ?? '-';
        @endphp

        <p class="profile-role mb-1">
            Status:
            <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
        </p>
        <p class="profile-email mb-0">
            {{ $areaName }}
        </p>
    </div>

    {{-- KARTU INFORMASI PELANGGAN --}}
    <div class="menu-section mt-3">
        <div class="section-header">
            <div class="section-icon">
                <i class="bi bi-info-circle"></i>
            </div>
            <h6 class="section-title mb-0">Informasi Pelanggan</h6>
        </div>

        <div class="p-3">
            <div class="mb-2">
                <small class="text-muted d-block">NIK</small>
                <div class="fw-semibold">{{ $pelanggan->nik ?? '-' }}</div>
            </div>

            <div class="mb-2">
                <small class="text-muted d-block">Alamat</small>
                <div>{{ $pelanggan->alamat ?? '-' }}</div>
            </div>

            {{-- No HP & IP SAMA-SAMA KIRI --}}
            <div class="row g-2 mb-2">
                <div class="col-12">
                    <small class="text-muted d-block">No. HP</small>
                    <div class="fw-semibold">
                        {{ $pelanggan->nomor_hp ?? '-' }}
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <small class="text-muted d-block">IP Address</small>
                    <div class="fw-semibold text-break">
                        {{ $pelanggan->ip_address ?? '-' }}
                    </div>
                </div>
            </div>

            {{-- TANGGAL REGISTRASI & SALES SAMA-SAMA KIRI --}}
            <div class="row g-2 mb-2">
                <div class="col-12">
                    <small class="text-muted d-block">Tanggal Registrasi</small>
                    <div>
                        {{ optional($pelanggan->tanggal_registrasi)->format('d-m-Y') ?? '-' }}
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <small class="text-muted d-block">Sales</small>
                    <div>{{ $pelanggan->sales->user->name ?? '-' }}</div>
                </div>
            </div>

            @php
                // pakai langganan terbaru
                $langgananTerakhir = $pelanggan->langganan->sortByDesc('tanggal_mulai')->first();
                $paketTerakhir     = $langgananTerakhir?->paket;
            @endphp

            <div class="mt-3">
                <small class="text-muted d-block">Paket Terakhir</small>
                @if($paketTerakhir)
                    <div class="fw-semibold">
                        {{ $paketTerakhir->nama_paket }}
                        @if(!empty($paketTerakhir->kecepatan))
                            - {{ $paketTerakhir->kecepatan }} Mbps
                        @endif
                    </div>
                    <div class="text-muted small">
                        Rp {{ number_format($paketTerakhir->harga_total ?? 0, 0, ',', '.') }} / bulan
                    </div>
                @else
                    <div>-</div>
                @endif
            </div>
        </div>
    </div>

    {{-- KARTU RINGKASAN LANGGANAN --}}
    <div class="summary-cards mt-3">
        <div class="summary-card income">
            <i class="bi bi-router"></i>
            <div>
                <p class="summary-label mb-1">Status Langganan</p>
                <p class="summary-value mb-0">
                    {{ ucfirst($langgananTerakhir->status_langganan ?? ($statusLabel ?? '-')) }}
                </p>
            </div>
        </div>
    </div>

    {{-- RIWAYAT PEMBAYARAN --}}
    <div class="menu-section mt-3">
        <div class="section-header">
            <div class="section-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <h6 class="section-title mb-0">Riwayat Pembayaran</h6>
        </div>

        @php
            use Carbon\Carbon;

            $rows = collect();

            foreach ($riwayatPembayaran as $pay) {
                foreach ($pay->items as $item) {
                    $tagihan   = $item->tagihan;
                    $langganan = $tagihan?->langganan;
                    $paket     = $langganan?->paket;

                    if (!$tagihan) {
                        continue;
                    }

                    $rows->push([
                        'pay'      => $pay,
                        'item'     => $item,
                        'tagihan'  => $tagihan,
                        'paket'    => $paket,
                        'tahun'    => (int) $tagihan->tahun,
                        'bulan'    => (int) $tagihan->bulan,
                    ]);
                }
            }

            $rows = $rows->sortByDesc(function ($r) {
                return $r['tahun'] * 100 + $r['bulan'];
            })->values();
        @endphp

        <div class="p-3">
            @if($rows->isEmpty())
                <p class="text-muted mb-0">
                    Belum ada pembayaran untuk pelanggan ini.
                </p>
            @else
                <div class="d-flex flex-column gap-2">
                    @php $no = 1; @endphp
                        @foreach($rows as $row)
                            @php
                                $pay     = $row['pay'];
                                $item    = $row['item'];
                                $tagihan = $row['tagihan'];
                                $paket   = $row['paket'];
                                $tahun   = $row['tahun'];
                                $bulan   = $row['bulan'];

                                $bulanTahun = Carbon::create($tahun, $bulan, 1)
                                    ->translatedFormat('F Y');

                                // nama sales dari pembayaran (kalau pembayaran dilakukan via sales)
                                $salesName = $pay->sales?->user?->name
                                    ?? $pelanggan->sales?->user?->name;

                                // nama admin (user yang input pembayaran)
                                $adminName = $pay->user?->name;

                                if (is_null($pay->id_sales)) {
                                    // pembayaran via admin
                                    $sumberText = 'Admin' . ($adminName ? ' - ' . $adminName : '');
                                    $badgeClass = 'bg-secondary';
                                } else {
                                    // pembayaran via sales
                                    $sumberText = 'Sales' . ($salesName ? ' - ' . $salesName : '');
                                    $badgeClass = 'bg-info';
                                }
                            @endphp


                        <div class="card shadow-sm border-0 pembayaran-card">
                            <div class="card-body py-2 px-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small text-muted">
                                            {{ $bulanTahun }}
                                        </div>
                                        <div class="fw-semibold">
                                            {{ $paket->nama_paket ?? '-' }}
                                        </div>
                                        <div class="small text-muted mt-1">
                                            Tgl Bayar:
                                            {{ optional($pay->tanggal_bayar)->format('d/m/Y H:i') ?? '-' }}
                                        </div>
                                        <div class="small mt-1">
                                            <span class="badge {{ $badgeClass }}">
                                                {{ $sumberText }}
                                            </span>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            No. Bayar: <strong>{{ $pay->no_pembayaran }}</strong>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <div class="fw-bold text-dark">
                                            Rp {{ number_format($item->nominal_bayar, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $riwayatPembayaran->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="hint-footer text-center mt-3 mb-2">
        Swipe ke atas untuk lihat riwayat pembayaran lebih banyak.
    </div>
</div>
@endsection

@push('styles')
<style>
    body {
        background: #f1f3f6;
    }

    .sales-container {
        padding-bottom: 90px;
    }

    .pelanggan-header {
        background: #4f46e5;
        color: #fff;
        padding: 12px 16px;
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        margin: -12px -12px 12px -12px;
    }

    .back-btn {
        color: #fff;
        text-decoration: none;
        font-size: 1.2rem;
        margin-right: 12px;
    }

    .pelanggan-detail-page {
        padding: 12px;
    }

    .profile-header {
        background: #ffffff;
        border-radius: 16px;
        padding: 16px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }

    .profile-avatar {
        width: 70px;
        height: 70px;
        border-radius: 20px;
        background: #4f46e5;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0 auto 10px auto;
    }

    .profile-name {
        font-weight: 700;
        color: #111827;
    }

    .profile-role,
    .profile-email {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .menu-section {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }

    .section-header {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-icon {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: #eef2ff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4f46e5;
    }

    .section-title {
        font-weight: 600;
        color: #111827;
        font-size: 0.95rem;
    }

    .summary-cards .summary-card {
        padding: 12px 14px;
    }

    .pembayaran-card {
        border-radius: 12px;
    }

    .hint-footer {
        background: #4f46e5;
        color: #fff;
        padding: 8px 12px;
        border-radius: 12px;
        font-size: 0.8rem;
        margin: 12px 0 0 0;
    }
</style>
@endpush
