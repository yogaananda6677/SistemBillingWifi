@extends('seles2.layout.master')
@section('title', 'Riwayat Setoran')

@section('content')
@php
    use Carbon\Carbon;

    $setorans       = $setorans       ?? collect();
    $areas          = $areas          ?? collect();
    $selectedAreaId = $selectedAreaId ?? null;
    $saldoGlobal    = $saldoGlobal    ?? 0;
    $totalWajib     = $totalWajib     ?? 0;
    $totalSetoran   = $totalSetoran   ?? 0;

    // Tambahan: interpretasi saldoGlobal
    // Asumsi: + = lebih setor, - = masih kurang
    $isLebihSetor  = $saldoGlobal > 0;
    $isKurangSetor = $saldoGlobal < 0;
    $nominalSaldo  = abs($saldoGlobal);
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

            {{-- FILTER WILAYAH --}}
<div class="mb-3">
    <div class="bg-white rounded-4 shadow-sm p-3 border border-light">
        <form method="GET" action="{{ route('seles2.setoran.index') }}" class="row g-2 align-items-center">
            <div class="col-8">
                <label class="tiny text-muted mb-1">Wilayah</label>
                <select name="area_id"
                        class="form-select form-select-sm rounded-pill bg-light border-0 fw-bold text-secondary">
                    <option value="">Semua Wilayah</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->id_area }}"
                            {{ (string)$selectedAreaId === (string)$area->id_area ? 'selected' : '' }}>
                            {{ $area->nama_area }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 d-flex align-items-end">
                <button type="submit"
                        class="btn **btn-admin-yellow** btn-sm w-100 rounded-pill fw-bold **text-dark** shadow-sm">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>


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

                            // Bulan/tahun alokasi setoran (dari kolom st.tahun & st.bulan)
                            $periodeSetoran = ($st->tahun && $st->bulan)
                                ? Carbon::create($st->tahun, $st->bulan, 1)->translatedFormat('F Y')
                                : null;
                        @endphp

                        {{-- CARD SETORAN --}}
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-3">

                                {{-- Header Card: Tanggal, Penerima, Wilayah --}}
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="small text-muted mb-1">
                                            <i class="bi bi-calendar-event me-1"></i> {{ $tanggal }}
                                        </div>

                                        <div class="d-flex flex-wrap gap-1">
                                            <div class="badge bg-light text-dark border fw-normal">
                                                <i class="bi bi-person-check me-1"></i> {{ $st->nama_admin ?? '-' }}
                                            </div>

                                            <div class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 fw-normal">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                {{ $st->nama_area ?? 'Tanpa Wilayah' }}
                                            </div>

                                            @if($periodeSetoran)
                                                <div class="badge bg-warning bg-opacity-10 text-warning-dark border border-warning border-opacity-25 fw-normal">
                                                    <i class="bi bi-calendar2-month me-1"></i>
                                                    Untuk bulan {{ $periodeSetoran }}
                                                </div>
                                            @endif
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
                                        class="alert alert-warning bg-warning bg-opacity-10 border-0 p-2 rounded-3 small mb-0 text-dark">
                                        <i class="bi bi-sticky me-1 text-warning"></i> {{ $st->catatan }}
                                    </div>
                                @endif

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
                        <strong>Catatan:</strong><br>
                        Riwayat setoran di atas bisa difilter berdasarkan wilayah yang Anda pegang.
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

        .text-amber        { color: #d97706; }
        .text-warning-dark { color: #b45309; }
        .ls-1              { letter-spacing: 1px; }

        .card {
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .tiny {
            font-size: 0.75rem;
        }
    </style>
@endpush
