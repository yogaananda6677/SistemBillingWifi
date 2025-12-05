@extends('seles2.layout.master')

@section('content')
    <div class="pelanggan-detail-page">

        {{-- HEADER (Tema Amber) --}}
        <div class="pelanggan-header d-flex align-items-center">
            <a href="{{ route('seles2.pelanggan.index') }}" class="back-btn">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="mb-0 fw-bold">Detail Pelanggan</h5>
        </div>

        {{-- PROFILE / RINGKASAN ATAS --}}
        <div class="profile-header mt-4">
            <div class="profile-avatar-wrapper">
                <div class="profile-avatar">
                    {{ strtoupper(substr($pelanggan->nama ?? 'P', 0, 1)) }}
                </div>
            </div>

            <h5 class="profile-name mb-1">{{ $pelanggan->nama }}</h5>

            @php
                $status = $pelanggan->status_pelanggan_efektif ?? $pelanggan->status_pelanggan;

                $statusLabel =
                    [
                        'baru' => 'Baru',
                        'aktif' => 'Aktif',
                        'isolir' => 'Isolir',
                        'berhenti' => 'Berhenti',
                    ][$status] ?? ucfirst($status);

                $statusClass = match ($status) {
                    'baru' => 'badge bg-warning text-dark',
                    'aktif' => 'badge bg-success',
                    'isolir' => 'badge bg-secondary',
                    default => 'badge bg-danger',
                };

                $areaName = $pelanggan->area->nama_area ?? '-';
            @endphp

            <div class="d-flex justify-content-center gap-2 mb-2">
                <span class="{{ $statusClass }} rounded-pill px-3 py-1 fw-normal border border-white shadow-sm">
                    {{ $statusLabel }}
                </span>
            </div>

            <p class="profile-email mb-0 text-muted small">
                <i class="bi bi-geo-alt-fill text-warning me-1"></i> {{ $areaName }}
            </p>
        </div>

        {{-- KARTU INFORMASI PELANGGAN --}}
        <div class="menu-section mt-3">
            <div class="section-header">
                <div class="section-icon">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <h6 class="section-title mb-0">Informasi Pelanggan</h6>
            </div>

            <div class="p-3">
                <div class="row g-3">
                    {{-- NIK & Alamat --}}
                    <div class="col-12 border-bottom pb-2">
                        <small class="text-muted d-block mb-1">NIK</small>
                        <div class="fw-semibold text-dark">{{ $pelanggan->nik ?? '-' }}</div>
                    </div>

                    <div class="col-12 border-bottom pb-2">
                        <small class="text-muted d-block mb-1">Alamat Pemasangan</small>
                        <div class="text-dark">{{ $pelanggan->alamat ?? '-' }}</div>
                    </div>

                    {{-- Kontak & Teknis --}}
                    <div class="col-6">
                        <small class="text-muted d-block mb-1">No. WhatsApp</small>
                        <div class="fw-semibold text-dark">
                            {{ $pelanggan->nomor_hp ?? '-' }}
                        </div>
                    </div>

                    <div class="col-6">
                        <small class="text-muted d-block mb-1">IP Address</small>
                        <div class="fw-semibold text-dark text-break font-monospace small bg-light p-1 rounded">
                            {{ $pelanggan->ip_address ?? '-' }}
                        </div>
                    </div>

                    {{-- Info Akun --}}
                    <div class="col-6">
                        <small class="text-muted d-block mb-1">Tgl Registrasi</small>
                        <div class="text-dark">
                            {{ optional($pelanggan->tanggal_registrasi)->format('d/m/Y') ?? '-' }}
                        </div>
                    </div>

                    <div class="col-6">
                        <small class="text-muted d-block mb-1">Sales</small>
                        <div class="text-dark">{{ $pelanggan->sales->user->name ?? '-' }}</div>
                    </div>
                </div>

                @php
                    // pakai langganan terbaru
                    $langgananTerakhir = $pelanggan->langganan->sortByDesc('tanggal_mulai')->first();
                    $paketTerakhir = $langgananTerakhir?->paket;
                @endphp

                {{-- Info Paket --}}
                <div class="mt-3 p-3 bg-light rounded-3 border border-warning border-opacity-25">
                    <small class="text-warning fw-bold d-block mb-1">PAKET SAAT INI</small>
                    @if ($paketTerakhir)
                        <div class="fw-bold text-dark fs-5">
                            {{ $paketTerakhir->nama_paket }}
                            @if (!empty($paketTerakhir->kecepatan))
                                <span class="fs-6 fw-normal text-muted">/ {{ $paketTerakhir->kecepatan }} Mbps</span>
                            @endif
                        </div>
                        <div class="text-muted small mt-1">
                            Biaya Bulanan: <span class="fw-bold text-dark">Rp
                                {{ number_format($paketTerakhir->harga_total ?? 0, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <div>-</div>
                    @endif

                    <div
                        class="mt-2 pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                        <small class="text-muted">Status Langganan</small>
                        <span class="badge bg-white text-dark border shadow-sm">
                            {{ ucfirst($langgananTerakhir->status_langganan ?? ($statusLabel ?? '-')) }}
                        </span>
                    </div>
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
                        $tagihan = $item->tagihan;
                        $langganan = $tagihan?->langganan;
                        $paket = $langganan?->paket;

                        if (!$tagihan) {
                            continue;
                        }

                        $rows->push([
                            'pay' => $pay,
                            'item' => $item,
                            'tagihan' => $tagihan,
                            'paket' => $paket,
                            'tahun' => (int) $tagihan->tahun,
                            'bulan' => (int) $tagihan->bulan,
                        ]);
                    }
                }

                $rows = $rows
                    ->sortByDesc(function ($r) {
                        return $r['tahun'] * 100 + $r['bulan'];
                    })
                    ->values();
            @endphp

            <div class="p-3 bg-light">
                @if ($rows->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-receipt text-muted opacity-25" style="font-size: 2.5rem;"></i>
                        <p class="text-muted mb-0 small mt-2">
                            Belum ada riwayat pembayaran.
                        </p>
                    </div>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach ($rows as $row)
                            @php
                                $pay = $row['pay'];
                                $item = $row['item'];
                                $tagihan = $row['tagihan'];
                                $paket = $row['paket'];
                                $tahun = $row['tahun'];
                                $bulan = $row['bulan'];

                                $bulanTahun = Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y');

                                // nama sales dari pembayaran (kalau pembayaran dilakukan via sales)
                                $salesName = $pay->sales?->user?->name ?? $pelanggan->sales?->user?->name;

                                // nama admin (user yang input pembayaran)
                                $adminName = $pay->user?->name;

                                if (is_null($pay->id_sales)) {
                                    // pembayaran via admin
                                    $sumberText = 'Admin' . ($adminName ? ' - ' . $adminName : '');
                                    $badgeClass =
                                        'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25';
                                } else {
                                    // pembayaran via sales
                                    $sumberText = 'Sales' . ($salesName ? ' - ' . $salesName : '');
                                    $badgeClass =
                                        'bg-info bg-opacity-10 text-info border border-info border-opacity-25';
                                }
                            @endphp

                            <div class="card shadow-sm border-0 pembayaran-card bg-white">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-success bg-opacity-10 text-success rounded p-1">
                                                <i class="bi bi-check-lg"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark" style="line-height: 1.2;">
                                                    {{ $bulanTahun }}</div>
                                                <small class="text-muted" style="font-size: 0.7rem;">
                                                    {{ $paket->nama_paket ?? '-' }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-dark fs-6">
                                                Rp {{ number_format($item->nominal_bayar, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-end border-top pt-2 mt-1">
                                        <div>
                                            <div class="small text-muted" style="font-size: 0.7rem;">
                                                {{ optional($pay->tanggal_bayar)->format('d/m/Y H:i') ?? '-' }}
                                            </div>
                                            <div class="small text-muted mt-1" style="font-size: 0.7rem;">
                                                No: <span class="font-monospace">{{ $pay->no_pembayaran }}</span>
                                            </div>
                                        </div>
                                        <span class="badge {{ $badgeClass }} rounded-pill fw-normal"
                                            style="font-size: 0.65rem;">
                                            {{ $sumberText }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 px-2">
                        {{ $riwayatPembayaran->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div class="hint-footer text-center mt-3 mb-2 mx-3 shadow-sm">
            <i class="bi bi-arrow-up-circle me-1"></i> Swipe untuk melihat lebih banyak riwayat
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Global Background */
        body {
            background: #f9fafb;
        }

        .pelanggan-detail-page {
            padding-bottom: 20px;
        }

        /* 1. HEADER (Gradient Kuning Emas) */
        .pelanggan-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 20px 20px 30px 20px;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.25);
            margin: -16px -16px 0 -16px;
            position: relative;
            z-index: 10;
            gap: 12px;
        }

        .back-btn {
            color: white;
            text-decoration: none;
            font-size: 1.4rem;
            background: rgba(255, 255, 255, 0.2);
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: 0.2s;
        }

        .back-btn:active {
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0.9);
        }

        /* 2. PROFILE HEADER */
        .profile-header {
            background: #ffffff;
            border-radius: 20px;
            padding: 0 20px 20px 20px;
            /* Padding top 0 karena avatar floating */
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-top: 15px !important;
            /* Naik ke atas menimpa header */
            position: relative;
            z-index: 11;
            margin-left: 10px;
            margin-right: 10px;
            border: 1px solid #f3f4f6;
        }

        .profile-avatar-wrapper {
            display: flex;
            justify-content: center;
            margin-top: -40px;
            /* Floating effect */
            margin-bottom: 10px;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 24px;
            /* Squircle */
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: 700;
            border: 4px solid #ffffff;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
        }

        .profile-name {
            font-weight: 800;
            color: #1f2937;
            font-size: 1.2rem;
        }

        /* 3. MENU SECTION */
        .menu-section {
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            margin-bottom: 16px;
        }

        .section-header {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
        }

        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: #fffbeb;
            /* Kuning Muda */
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d97706;
            /* Text Amber Gelap */
            font-size: 1.1rem;
        }

        .section-title {
            font-weight: 700;
            color: #1f2937;
            font-size: 0.95rem;
        }

        /* 4. PEMBAYARAN CARD */
        .pembayaran-card {
            border-radius: 12px;
            border: 1px solid #f3f4f6 !important;
            transition: transform 0.2s;
        }

        .pembayaran-card:active {
            transform: scale(0.98);
            background-color: #fdfdfd;
        }

        /* 5. FOOTER HINT */
        .hint-footer {
            background: #fffbeb;
            color: #d97706;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid #fcd34d;
        }
    </style>
@endpush
