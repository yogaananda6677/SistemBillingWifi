@extends('seles2.layout.master')
@section('title', 'Pembukuan')

@section('content')
    @php
        use Carbon\Carbon;

        $selectedMonth = $selectedMonth ?? now()->month;
        $selectedYear = $selectedYear ?? now()->year;

        $rekap = $rekap ?? null;

        $pendapatanPelanggan = $rekap->total_pendapatan ?? 0;
        $komisi = $rekap->total_komisi ?? 0;
        $pengeluaran = $rekap->total_pengeluaran ?? 0;
        $total = $rekap->pendapatan_bersih ?? $pendapatanPelanggan - $komisi - $pengeluaran;
        $sudahSetor = $rekap->total_setoran ?? 0;
        $wajibSetor = $rekap->wajib_setor_bulan_ini ?? $total;
        $uangBelumSetor = $rekap->uang_belum_disetor_bulan_ini ?? max($wajibSetor - $sudahSetor, 0);

        $pembayaranList = $pembayaranList ?? collect();
        $komisiList = $komisiList ?? collect();
        $pengeluaranList = $pengeluaranList ?? collect();
        $setoranList = $setoranList ?? collect();
    @endphp

    <div class="pelanggan-page">

        {{-- 1. HEADER (Gradient Amber) --}}
        <div class="pelanggan-header d-flex align-items-center justify-content-center px-3 pt-3 pb-5">
            <a href="{{ route('dashboard-sales') }}" class="back-btn position-absolute start-0 ms-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="mb-0 fw-bold text-center text-white">Pembukuan</h5>
        </div>

        {{-- 2. FILTER PERIODE (Floating Card) --}}
        <div class="px-3" style="margin-top: -25px; position: relative; z-index: 20;">
            <div class="bg-white rounded-4 shadow-sm p-3 border border-light">
                <form method="GET" action="{{ route('seles2.pembukuan.index') }}" class="row g-2 align-items-center">
                    <div class="col-5">
                        <select name="bulan"
                            class="form-select form-select-sm rounded-pill bg-light border-0 fw-bold text-secondary">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ (int) $selectedMonth === $m ? 'selected' : '' }}>
                                    {{ Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <select name="tahun"
                            class="form-select form-select-sm rounded-pill bg-light border-0 fw-bold text-secondary">
                            @foreach (range(now()->year - 2, now()->year + 1) as $y)
                                <option value="{{ $y }}" {{ (int) $selectedYear === $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-amber btn-sm w-100 rounded-pill fw-bold text-white shadow-sm">
                            <i class="bi bi-search"></i> Cek
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-3 px-3 pb-5">
            @if (!$rekap)
                <div class="text-center text-muted mt-5">
                    <div class="mb-3">
                        <i class="bi bi-journal-x display-1 opacity-25"></i>
                    </div>
                    <h6 class="fw-bold text-muted">Data Kosong</h6>
                    <p class="small">Belum ada data pembukuan untuk periode ini.</p>
                </div>
            @else
                {{-- KARTU 1: ARUS KAS (Pendapatan - Potongan) --}}
                <div class="section-label mb-2 mt-2 text-muted fw-bold small px-1">ARUS KAS BULAN INI</div>

                <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
                    <div class="card-body p-0">

                        {{-- 1. Pendapatan (Masuk) --}}
                        <div class="p-3 clickable hover-bg" data-bs-toggle="modal" data-bs-target="#modalPendapatan">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="icon-box bg-success bg-opacity-10 text-success rounded-circle">
                                        <i class="bi bi-arrow-down-left"></i>
                                    </div>
                                    <div>
                                        <div class="small text-dark fw-bold">Pendapatan Kotor</div>
                                        <div class="tiny text-muted">Total dari pelanggan</div>
                                    </div>
                                </div>
                                <div class="fw-bold text-dark fs-6">
                                    Rp {{ number_format($pendapatanPelanggan, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <div class="border-top border-light mx-3"></div>

                        {{-- 2. Komisi (Keluar) --}}
                        <div class="p-3 clickable hover-bg" data-bs-toggle="modal" data-bs-target="#modalKomisi">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-circle">
                                        <i class="bi bi-percent"></i>
                                    </div>
                                    <div>
                                        <div class="small text-dark fw-bold">Komisi Sales</div>
                                        <div class="tiny text-muted">Potongan langsung</div>
                                    </div>
                                </div>
                                <div class="fw-bold text-danger">
                                    - Rp {{ number_format($komisi, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <div class="border-top border-light mx-3"></div>

                        {{-- 3. Pengeluaran (Keluar) --}}
                        <div class="p-3 clickable hover-bg" data-bs-toggle="modal" data-bs-target="#modalPengeluaran">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-circle">
                                        <i class="bi bi-cart"></i>
                                    </div>
                                    <div>
                                        <div class="small text-dark fw-bold">Pengeluaran</div>
                                        <div class="tiny text-muted">Operasional disetujui</div>
                                    </div>
                                </div>
                                <div class="fw-bold text-danger">
                                    - Rp {{ number_format($pengeluaran, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        {{-- TOTAL NETTO --}}
                        <div class="bg-light p-3 border-top border-secondary border-opacity-10">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-secondary small text-uppercase ls-1">PENDAPATAN BERSIH</span>
                                <span class="fw-bold text-dark fs-5">
                                    Rp {{ number_format($total, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KARTU 2: STATUS SETORAN --}}
                <div class="section-label mb-2 mt-4 text-muted fw-bold small px-1">STATUS SETORAN</div>

                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body p-3">

                        {{-- Alert Belum Setor --}}
                        <div
                            class="d-flex align-items-center justify-content-between p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 mb-3">
                            <div>
                                <div class="small fw-bold text-warning-dark">Uang Belum Disetor</div>
                                <div class="tiny text-muted">Sisa di tangan Anda</div>
                            </div>
                            <div class="fw-bold text-warning-dark fs-5">
                                Rp {{ number_format($uangBelumSetor, 0, ',', '.') }}
                            </div>
                        </div>

                        {{-- Sudah Setor --}}
                        <div class="d-flex align-items-center justify-content-between px-2 clickable hover-scale"
                            data-bs-toggle="modal" data-bs-target="#modalSetoran">
                            <div class="d-flex align-items-center gap-2">
                                <div class="icon-box-sm bg-success text-white rounded-circle">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <span class="small text-dark fw-bold">Sudah Disetor (Bulan Ini)</span>
                            </div>
                            <span class="fw-bold text-success">
                                Rp {{ number_format($sudahSetor, 0, ',', '.') }}
                                <i class="bi bi-chevron-right small text-muted ms-1"></i>
                            </span>
                        </div>

                    </div>
                </div>
            @endif

            <div class="text-center mt-4">
                <small class="text-muted fst-italic" style="font-size: 0.7rem;">
                    * Data ditampilkan berdasarkan tanggal transaksi pada bulan terpilih.
                </small>
            </div>
        </div>
    </div>

    {{-- ================== MODAL DETAIL (MODERN STYLE) ================== --}}

    {{-- 1. Modal Pendapatan --}}
    <div class="modal fade" id="modalPendapatan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-mobile">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Rincian Pendapatan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    @if ($pembayaranList->isEmpty())
                        <div class="text-center py-3 text-muted">Belum ada data.</div>
                    @else
                        <div class="d-flex flex-column gap-2">
                            @foreach ($pembayaranList as $p)
                                <div class="bg-light p-2 rounded-3 border border-light">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-dark">{{ $p->nama_pelanggan ?? '-' }}</span>
                                        <span class="fw-bold text-success">Rp
                                            {{ number_format($p->nominal, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1 text-muted tiny">
                                        <span>{{ $p->tanggal_bayar ? Carbon::parse($p->tanggal_bayar)->format('d M H:i') : '-' }}</span>
                                        <span>#{{ $p->no_pembayaran }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Modal Komisi --}}
    <div class="modal fade" id="modalKomisi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-mobile">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Rincian Komisi</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    @if ($komisiList->isEmpty())
                        <div class="text-center py-3 text-muted">Belum ada data.</div>
                    @else
                        <div class="d-flex flex-column gap-2">
                            @foreach ($komisiList as $k)
                                <div class="bg-light p-2 rounded-3 border border-light">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-dark">{{ $k->nama_pelanggan ?? '-' }}</span>
                                        <span class="fw-bold text-danger">- Rp
                                            {{ number_format($k->nominal_komisi, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1 text-muted tiny">
                                        <span>Dari Bayar: Rp {{ number_format($k->nominal_bayar, 0, ',', '.') }}</span>
                                        <span>{{ $k->tanggal_bayar ? Carbon::parse($k->tanggal_bayar)->format('d M') : '-' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Modal Pengeluaran --}}
    <div class="modal fade" id="modalPengeluaran" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-mobile">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Rincian Pengeluaran</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    @if ($pengeluaranList->isEmpty())
                        <div class="text-center py-3 text-muted">Belum ada data.</div>
                    @else
                        <div class="d-flex flex-column gap-2">
                            @foreach ($pengeluaranList as $pg)
                                <div class="bg-light p-2 rounded-3 border border-light">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-dark">{{ $pg->nama_pengeluaran }}</span>
                                        <span class="fw-bold text-danger">- Rp
                                            {{ number_format($pg->nominal, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="mt-1 text-muted tiny">
                                        {{ $pg->tanggal_approve ? Carbon::parse($pg->tanggal_approve)->format('d M Y') : '-' }}
                                        @if ($pg->catatan)
                                            <br> <i class="bi bi-info-circle me-1"></i> {{ $pg->catatan }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Modal Setoran --}}
    <div class="modal fade" id="modalSetoran" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-mobile">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Riwayat Setoran (Bulan Ini)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 small">
                    @if ($setoranList->isEmpty())
                        <div class="text-center py-3 text-muted">Belum ada data setoran.</div>
                    @else
                        <div class="d-flex flex-column gap-2">
                            @foreach ($setoranList as $st)
                                <div class="bg-light p-2 rounded-3 border border-light">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted fw-medium">
                                            {{ $st->tanggal_setoran ? Carbon::parse($st->tanggal_setoran)->translatedFormat('d M Y, H:i') : '-' }}
                                        </span>
                                        <span class="fw-bold text-success">
                                            Rp {{ number_format($st->nominal, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    @if ($st->catatan)
                                        <div class="mt-1 text-muted tiny fst-italic">
                                            "{{ $st->catatan }}"
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
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

        /* BUTTON AMBER */
        .btn-amber {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
        }

        .btn-amber:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        }

        /* ICON BOX */
        .icon-box {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .icon-box-sm {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        /* TEXT UTILS */
        .tiny {
            font-size: 0.75rem;
        }

        .text-warning-dark {
            color: #b45309;
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        /* HOVER EFFECTS */
        .hover-bg:hover {
            background-color: #f8f9fa;
            cursor: pointer;
            transition: 0.2s;
        }

        .clickable:active {
            transform: scale(0.98);
        }

        /* MODAL */
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
    </style>
@endpush
