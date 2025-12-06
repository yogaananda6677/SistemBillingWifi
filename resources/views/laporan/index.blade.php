@extends('layouts.master')
@section('title', 'Pembukuan Global')

@section('content')
@php use Carbon\Carbon; @endphp

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h3 class="fw-bold mb-0">Pembukuan Global (Sales & Admin)</h3>
        <div class="text-muted small">
            Data per: <strong>{{ ($stat['last_updated'] ?? now())->format('d/m/Y H:i') }}</strong>
        </div>
    </div>

    {{-- FORM UTAMA : FILTER + PILIH CARD + EXPORT --}}
    <form id="laporanForm" method="GET" action="{{ route('laporan.index') }}">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3 col-6">
                        <label class="form-label mb-1">Bulan</label>
                        <select name="bulan" class="form-select form-select-sm">
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ (int)$selectedMonth === $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-6">
                        <label class="form-label mb-1">Tahun</label>
                        <select name="tahun" class="form-select form-select-sm">
                            @foreach(range(now()->year - 3, now()->year + 1) as $y)
                                <option value="{{ $y }}" {{ (int)$selectedYear === $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-6">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            Tampilkan Rincian
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- INFO PERIODE + STAT KECIL --}}
        <div class="mb-3 text-muted d-flex justify-content-between flex-wrap gap-2">
            <div>
                Periode:
                <strong>{{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}</strong>
            </div>
            @isset($stat)
            <div class="small">
                <span class="me-3">
                    Pelanggan Bayar:
                    <strong>{{ number_format($stat['jumlah_pelanggan'] ?? 0,0,',','.') }}</strong>
                </span>
                <span class="me-3">
                    Pembayaran:
                    <strong>Rp {{ number_format($stat['jumlah_pembayaran'] ?? 0,0,',','.') }}</strong>
                </span>
                <span class="me-3">
                    Pengeluaran:
                    <strong>Rp {{ number_format($stat['jumlah_pengeluaran'] ?? 0,0,',','.') }}</strong>
                </span>
                <span class="me-3">
                    Komisi:
                    <strong>Rp {{ number_format($stat['jumlah_komisi'] ?? 0,0,',','.') }}</strong>
                </span>
                <span>
                    Laba Kotor:
                    <strong>Rp {{ number_format($stat['laba_kotor'] ?? 0,0,',','.') }}</strong>
                </span>
            </div>
            @endisset
        </div>

        {{-- GRID CARD REKAP --}}
        <div class="card mb-4">
            <div class="card-header py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Pilih Sales / Admin</span>
                    <div class="small">
                        <a href="javascript:void(0)" onclick="toggleAll(true)">Centang semua</a> •
                        <a href="javascript:void(0)" onclick="toggleAll(false)">Kosongkan</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($rekap->isEmpty())
                    <p class="text-muted mb-0">Belum ada data untuk periode ini.</p>
                @else
                    <div class="row g-3">
                        @foreach($rekap as $row)
                            @php
                                $checked = in_array($row->key, $selectedUnits ?? []);
                            @endphp
                            <div class="col-md-3 col-sm-6">
                                <div class="card h-100 shadow-sm border-{{ $checked ? 'primary' : 'light' }} selectable-card">
                                    <div class="card-body">
                                        <div class="form-check mb-1">
                                            <input  class="form-check-input unit-checkbox"
                                                    type="checkbox"
                                                    name="units[]"
                                                    id="unit-{{ $row->key }}"
                                                    value="{{ $row->key }}"
                                                    {{ $checked ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="unit-{{ $row->key }}">
                                                {{ $row->label }}
                                            </label>
                                        </div>
                                        <hr class="my-2">
                                        <div class="small">
                                            <div>Pendapatan:
                                                <strong>Rp {{ number_format($row->pendapatan,0,',','.') }}</strong>
                                            </div>
                                            <div>Komisi:
                                                <strong class="text-danger">Rp {{ number_format($row->total_komisi,0,',','.') }}</strong>
                                            </div>
                                            <div>Pengeluaran:
                                                <strong class="text-danger">Rp {{ number_format($row->total_pengeluaran,0,',','.') }}</strong>
                                            </div>
                                            <div>Total Bersih:
                                                <strong class="text-success">Rp {{ number_format($row->total_bersih,0,',','.') }}</strong>
                                            </div>
                                            <div>Setoran:
                                                <strong class="text-success">Rp {{ number_format($row->total_setoran,0,',','.') }}</strong>
                                            </div>
                                            <div>Selisih:
                                                <strong class="{{ $row->selisih < 0 ? 'text-danger' : 'text-success' }}">
                                                    Rp {{ number_format($row->selisih,0,',','.') }}
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- RINCIAN UNTUK UNIT YANG DIPILIH --}}
{{-- ========== RINCIAN SIMPLE TOTAL GABUNGAN UNTUK UNIT TERPILIH ========== --}}
@if(!empty($selectedUnits))
    @php
        $selectedRows = $rekap->whereIn('key', $selectedUnits);

        $totalPendapatan  = $selectedRows->sum('pendapatan');
        $totalKomisi      = $selectedRows->sum('total_komisi');
        $totalPengeluaran = $selectedRows->sum('total_pengeluaran');
        $totalSetoran     = $selectedRows->sum('total_setoran');

        // total bersih gabungan
        $totalBersih      = $selectedRows->sum('total_bersih'); 
        // atau bisa juga: $totalPendapatan - $totalKomisi - $totalPengeluaran

        // posisi setoran gabungan
        // + = lebih setor, - = masih kurang
        $selisihGabungan  = $totalSetoran - $totalBersih;
        $isKurangSetor    = $selisihGabungan < 0;
        $isLebihSetor     = $selisihGabungan > 0;
        $nominalSelisih   = abs($selisihGabungan);
    @endphp

    <div class="card">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Rincian Gabungan</span>
            <small class="text-muted">
                {{ $selectedRows->count() }} unit dipilih
            </small>
        </div>

        <div class="card-body fs-6">

            {{-- Rincian Dasar --}}
            <div class="d-flex justify-content-between mb-2">
                <span>Total Pemasukan</span>
                <span class="fw-bold text-success">
                    Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                </span>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span>Total Komisi</span>
                <span class="fw-bold text-danger">
                    Rp {{ number_format($totalKomisi, 0, ',', '.') }}
                </span>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span>Total Pengeluaran</span>
                <span class="fw-bold text-danger">
                    Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                </span>
            </div>

            <hr class="my-2">

            {{-- Total Bersih --}}
            <div class="d-flex justify-content-between mb-2">
                <span>Total Bersih<br>
                    <small class="text-muted">
                        (Pemasukan - Komisi - Pengeluaran)
                    </small>
                </span>
                <span class="fw-bold {{ $totalBersih >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($totalBersih, 0, ',', '.') }}
                </span>
            </div>

            {{-- Sudah Disetor --}}
            <div class="d-flex justify-content-between mb-2">
                <span>Sudah Disetor</span>
                <span class="fw-bold text-primary">
                    Rp {{ number_format($totalSetoran, 0, ',', '.') }}
                </span>
            </div>

            {{-- Uang Belum / Lebih Disetor --}}
            <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                @if($isKurangSetor)
                    <span class="fw-semibold text-muted">Uang Belum Disetor</span>
                    <span class="fw-bold text-warning">
                        Rp {{ number_format($nominalSelisih, 0, ',', '.') }}
                    </span>
                @elseif($isLebihSetor)
                    <span class="fw-semibold text-muted">Lebih Setor</span>
                    <span class="fw-bold text-success">
                        Rp {{ number_format($nominalSelisih, 0, ',', '.') }}
                    </span>
                @else
                    <span class="fw-semibold text-muted">Posisi Setoran</span>
                    <span class="fw-bold text-muted">
                        Rp 0 (pas)
                    </span>
                @endif
            </div>

        </div>
    </div>
@endif


    </form>
</div>

{{-- JS kecil untuk centang semua + export --}}
@push('scripts')
<script>
    function toggleAll(state) {
        document.querySelectorAll('.unit-checkbox').forEach(cb => {
            cb.checked = !!state;
        });
    }

    function exportLaporan(type) {
        const form = document.getElementById('laporanForm');
        const actionBase = type === 'excel'
            ? "{{ route('laporan.export.excel') }}"
            : "{{ route('laporan.export.pdf') }}";

        // bikin form sementara utk submit GET dengan query yg sama + units[]
        const tempForm = document.createElement('form');
        tempForm.method = 'GET';
        tempForm.action = actionBase;

        // copy bulan & tahun
        ['bulan', 'tahun'].forEach(name => {
            const input = form.querySelector(`[name="${name}"]`);
            if (input) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = name;
                hidden.value = input.value;
                tempForm.appendChild(hidden);
            }
        });

        // copy units[]
        form.querySelectorAll('.unit-checkbox:checked').forEach(cb => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'units[]';
            hidden.value = cb.value;
            tempForm.appendChild(hidden);
        });

        document.body.appendChild(tempForm);
        tempForm.submit();
    }
</script>
@endpush

@endsection
