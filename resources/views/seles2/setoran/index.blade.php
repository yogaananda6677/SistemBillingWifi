@extends('seles2.layout.master')
@section('title', 'Riwayat Setoran')

@section('content')
    @php
        use Carbon\Carbon;

        $setorans = $setorans ?? collect();
        $allocDetail = $allocDetail ?? [];
    @endphp

    <div class="pelanggan-page">

        {{-- 1. HEADER (Gradient Amber) --}}
        <div class="pelanggan-header d-flex align-items-center justify-content-center px-3 pt-3 pb-5">
            <a href="{{ route('seles2.pembukuan.index') }}" class="back-btn position-absolute start-0 ms-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="mb-0 fw-bold text-center text-white">Riwayat Setoran</h5>
        </div>

        {{-- 2. CONTENT CONTAINER (Floating Up) --}}
        <div class="px-3" style="margin-top: -35px; position: relative; z-index: 20;">

            @if ($setorans->isEmpty())
                <div class="bg-white rounded-4 shadow-sm p-5 text-center border border-light">
                    <div class="mb-3">
                        <i class="bi bi-wallet2 text-muted opacity-25" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="fw-bold text-muted">Belum ada setoran</h6>
                    <p class="small text-muted mb-0">Riwayat penyetoran uang ke admin akan muncul di sini.</p>
                </div>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach ($setorans as $st)
                        @php
                            $tanggal = $st->tanggal_setoran
                                ? Carbon::parse($st->tanggal_setoran)->translatedFormat('d F Y, H:i')
                                : '-';

                            $detail = $allocDetail[$st->id_setoran] ?? [];
                        @endphp

                        {{-- CARD SETORAN --}}
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-3">

                                {{-- Header Card: Tanggal & Penerima --}}
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="small text-muted mb-1">
                                            <i class="bi bi-calendar-event me-1"></i> {{ $tanggal }}
                                        </div>
                                        <div class="badge bg-light text-dark border fw-normal">
                                            <i class="bi bi-person-check me-1"></i> {{ $st->nama_admin ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">Nominal Setor</small>
                                        <h5 class="fw-bold text-success mb-0">
                                            Rp {{ number_format($st->nominal, 0, ',', '.') }}
                                        </h5>
                                    </div>
                                </div>

                                {{-- Catatan (Jika Ada) --}}
                                @if ($st->catatan)
                                    <div
                                        class="alert alert-warning bg-warning bg-opacity-10 border-0 p-2 rounded-3 small mb-3 text-dark">
                                        <i class="bi bi-sticky me-1 text-warning"></i> {{ $st->catatan }}
                                    </div>
                                @endif

                                {{-- Rincian Alokasi (Box Abu-abu) --}}
                                <div class="bg-light rounded-3 p-3 mt-3 border border-light">
                                    <h6 class="small fw-bold text-secondary mb-2 text-uppercase ls-1">Alokasi Dana</h6>

                                    @if (empty($detail))
                                        <div class="text-muted small fst-italic">Tidak ada rincian alokasi.</div>
                                    @else
                                        <div class="d-flex flex-column gap-2">
                                            @foreach ($detail as $al)
                                                @php
                                                    $periodeText = \Carbon\Carbon::createFromFormat(
                                                        'Y-m-d',
                                                        $al['periode'] . '-01',
                                                    )->translatedFormat('F Y');
                                                @endphp
                                                <div
                                                    class="d-flex justify-content-between align-items-center border-bottom border-white pb-1">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="small fw-medium text-dark">{{ $periodeText }}</span>
                                                        @if (!empty($al['lebih']))
                                                            <span
                                                                class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill"
                                                                style="font-size: 0.6rem;">
                                                                Kelebihan
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <span class="small fw-bold text-secondary">
                                                        Rp {{ number_format($al['nominal'], 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Info Box --}}
            <div class="mt-4 mb-5 p-3 rounded-3 bg-white border border-light shadow-sm">
                <div class="d-flex gap-2">
                    <i class="bi bi-info-circle-fill text-amber fs-5"></i>
                    <div class="small text-muted">
                        <strong>Catatan Sistem:</strong><br>
                        Setoran dialokasikan otomatis mulai dari kewajiban bulan tertua.
                        Sisa uang setelah semua kewajiban tertutup akan dicatat sebagai
                        <span class="text-info fw-bold">kelebihan</span> di bulan tersebut.
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Global Page Style */
        .pelanggan-page {
            background: #f9fafb;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
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
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            justify-content: center;
            text-decoration: none;
        }

        .text-amber {
            color: #d97706;
        }

        /* Typography */
        .ls-1 {
            letter-spacing: 1px;
        }

        /* Card Hover Effect */
        .card {
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
        }
    </style>
@endpush
