@extends('layouts.master')
@section('title', 'Laporan & Rekap')

@section('content')

{{-- Style tetap sama --}}
<style>
    /* --- ADMIN YELLOW THEME --- */
    :root {
        --theme-yellow: #ffc107;
        --theme-yellow-dark: #e0a800;
        --theme-yellow-soft: #fff9e6;
        --text-dark: #212529;
        --card-radius: 12px;
    }
    .page-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.5px;
    }
    .card-admin {
        background: #fff;
        border: none;
        border-radius: var(--card-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border-top: 4px solid var(--theme-yellow);
        width: 100%;
    }
    .form-control-admin {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
    }
    .form-control-admin:focus {
        border-color: var(--theme-yellow);
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
    }
    .btn-admin-yellow {
        background-color: var(--theme-yellow);
        color: var(--text-dark);
        font-weight: 600;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 13px;
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.3);
        transition: all 0.2s ease;
    }
    .btn-admin-yellow:hover {
        background-color: var(--theme-yellow-dark);
        color: var(--text-dark);
        transform: translateY(-2px);
    }
    .filter-label {
        font-size: 11px;
        font-weight: 700;
        color: #6c757d;
        margin-bottom: 4px;
        display: block;
    }
    .selectable-card {
        transition: all 0.2s;
        border: 1px solid #eee;
    }
    .selectable-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .selectable-card.selected {
        border-color: var(--theme-yellow);
        background-color: #fffdf5;
    }
</style>

<div class="container-fluid p-4">

    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-clipboard-data-fill text-warning me-2"></i>Laporan & Rekap
            </h4>
            <div class="text-muted small">
                Data per: <strong>{{ ($stat['last_updated'] ?? now())->format('d/m/Y H:i') }}</strong>
            </div>
        </div>
    </div>

    <form id="laporanForm" method="GET" action="{{ route('laporan.index') }}">
        
        {{-- FILTER CARD --}}
        <div class="card-admin p-3 mb-3">
            <div class="row g-2 align-items-end">
                {{-- Bagian Select Bulan (DIPERBAIKI) --}}
                <div class="col-6 col-md-3">
                    <span class="filter-label">Bulan</span>
                    <select name="bulan" class="form-select form-control-admin">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" @if((int)$selectedMonth == $m) selected @endif>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Bagian Select Tahun (DIPERBAIKI) --}}
                <div class="col-6 col-md-3">
                    <span class="filter-label">Tahun</span>
                    <select name="tahun" class="form-select form-control-admin">
                        @foreach(range(now()->year - 3, now()->year + 1) as $y)
                            <option value="{{ $y }}" @if((int)$selectedYear == $y) selected @endif>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>
<div class="col-md-3 col-6">
    {{-- Tombol Tampilkan Rincian (Warna Yellow - Aksi Utama) --}}
    <button type="submit" class="btn btn-admin-yellow w-100">
        <i class="bi bi-search me-1"></i> Tampilkan Rincian
    </button>
</div>

<div class="col-md-3 col-6 text-md-end">
    {{-- Grup tombol Ekspor Laporan (Sejajar/Horizontal) --}}
    <div class="btn-group w-100" role="group">
        
        {{-- Tombol 1: Pelanggan (Warna Yellow - Tema Utama) --}}
        <button type="button"
                class="btn **btn-admin-yellow btn-sm**"
                onclick="exportLaporan('excel')">
            <i class="bi bi-file-earmark-excel"></i>Data Pelanggan
        </button>

        {{-- Tombol 2: Rekap (Warna Hijau - Warna Sekunder yang Jelas) --}}
        <a href="{{ route('laporan.exportRekapHarianBulanan', ['tahun' => $selectedYear]) }}"
           class="btn **btn-success btn-sm**"
           target="_blank">
           <i class="bi bi-calendar-range"></i> Data Harian
        </a>
        
    </div>
</div>

                </div>
            </div>

            <hr class="my-3 text-muted" style="opacity: 0.1">

            {{-- INFO STATISTIK --}}
            <div class="d-flex flex-wrap gap-4 align-items-center small text-muted">
                <div>
                    <span class="filter-label">Periode</span>
                    <span class="fw-bold text-dark">{{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}</span>
                </div>
                @isset($stat)
                    <div class="vr opacity-25 d-none d-md-block"></div>
                    <div>
                        <span class="filter-label">Pelanggan Bayar</span>
                        <strong class="text-dark">{{ number_format($stat['jumlah_pelanggan'] ?? 0,0,',','.') }}</strong>
                    </div>
                    <div class="vr opacity-25 d-none d-md-block"></div>
                    <div>
                        <span class="filter-label">Pembayaran</span>
                        <strong class="text-success">Rp {{ number_format($stat['jumlah_pembayaran'] ?? 0,0,',','.') }}</strong>
                    </div>
                    <div class="vr opacity-25 d-none d-md-block"></div>
                    <div>
                        <span class="filter-label">Pengeluaran</span>
                        <strong class="text-danger">Rp {{ number_format($stat['jumlah_pengeluaran'] ?? 0,0,',','.') }}</strong>
                    </div>
                    <div class="vr opacity-25 d-none d-md-block"></div>
                    <div>
                        <span class="filter-label">Komisi</span>
                        <strong class="text-danger">Rp {{ number_format($stat['jumlah_komisi'] ?? 0,0,',','.') }}</strong>
                    </div>
                @endisset
            </div>
        </div>

        {{-- GRID CARD REKAP --}}
        <div class="card-admin p-0 mb-4" style="overflow: hidden;">
            <div class="bg-light p-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i class="bi bi-check2-square me-2 text-warning"></i>Pilih Sales / Admin</span>
                <div class="small">
                    <a href="javascript:void(0)" onclick="toggleAll(true)" class="text-decoration-none fw-bold text-warning me-2">Centang semua</a>
                    <a href="javascript:void(0)" onclick="toggleAll(false)" class="text-decoration-none text-muted">Kosongkan</a>
                </div>
            </div>
            
            <div class="p-3">
                @if($rekap->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">Belum ada data untuk periode ini.</p>
                @else
                    <div class="row g-3">
                        @foreach($rekap as $row)
                            @php
                                $checked = in_array($row->key, $selectedUnits ?? []);
                            @endphp
                            <div class="col-md-3 col-sm-6">
                                <div class="card h-100 shadow-sm selectable-card {{ $checked ? 'selected' : '' }}">
                                    <div class="card-body p-3">
                                        <div class="form-check mb-2">
                                            {{-- Perbaikan cara checked agar aman --}}
                                            <input class="form-check-input unit-checkbox"
                                                   type="checkbox"
                                                   name="units[]"
                                                   id="unit-{{ $row->key }}"
                                                   value="{{ $row->key }}"
                                                   @if($checked) checked @endif
                                                   onchange="this.closest('.card').classList.toggle('selected', this.checked)">
                                            <label class="form-check-label fw-bold text-dark" for="unit-{{ $row->key }}" style="font-size: 13px;">
                                                {{ $row->label }}
                                            </label>
                                        </div>
                                        <hr class="my-2 opacity-10">
                                        <div style="font-size: 11px;">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted">Pendapatan</span>
                                                <strong class="text-dark">Rp {{ number_format($row->pendapatan,0,',','.') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted">Komisi</span>
                                                <strong class="text-danger">Rp {{ number_format($row->total_komisi,0,',','.') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted">Pengeluaran</span>
                                                <strong class="text-danger">Rp {{ number_format($row->total_pengeluaran,0,',','.') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted">Total Bersih</span>
                                                <strong class="text-success">Rp {{ number_format($row->total_bersih,0,',','.') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted">Setoran</span>
                                                <strong class="text-primary">Rp {{ number_format($row->total_setoran,0,',','.') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between pt-1 border-top mt-1">
                                                <span class="text-muted">Selisih</span>
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

        {{-- RINCIAN GABUNGAN (JIKA ADA YG DIPILIH) --}}
        @if(!empty($selectedUnits))
            @php
                $selectedRows = $rekap->whereIn('key', $selectedUnits);
                $totalPendapatan  = $selectedRows->sum('pendapatan');
                $totalKomisi      = $selectedRows->sum('total_komisi');
                $totalPengeluaran = $selectedRows->sum('total_pengeluaran');
                $totalSetoran     = $selectedRows->sum('total_setoran');
                $totalBersih      = $selectedRows->sum('total_bersih'); 
                
                $selisihGabungan  = $totalSetoran - $totalBersih;
                $isKurangSetor    = $selisihGabungan < 0;
                $isLebihSetor     = $selisihGabungan > 0;
                $nominalSelisih   = abs($selisihGabungan);
            @endphp

            <div class="card-admin p-0">
                <div class="bg-warning bg-opacity-10 p-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-calculator me-2"></i>Rincian Gabungan</span>
                    <span class="badge bg-warning text-dark">{{ $selectedRows->count() }} unit dipilih</span>
                </div>

                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Pemasukan</span>
                                <span class="fw-bold text-success">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Komisi</span>
                                <span class="fw-bold text-danger">Rp {{ number_format($totalKomisi, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Pengeluaran</span>
                                <span class="fw-bold text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="col-md-6 ps-md-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Bersih <small class="text-muted" style="font-size: 10px;">(Pemasukan - Komisi - Pengeluaran)</small></span>
                                <span class="fw-bold {{ $totalBersih >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($totalBersih, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sudah Disetor</span>
                                <span class="fw-bold text-primary">Rp {{ number_format($totalSetoran, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mt-3 pt-2 border-top bg-light p-2 rounded">
                                @if($isKurangSetor)
                                    <span class="fw-bold text-muted">Status: <span class="text-danger">Kurang Setor</span></span>
                                    <span class="fw-bold text-danger fs-5">Rp {{ number_format($nominalSelisih, 0, ',', '.') }}</span>
                                @elseif($isLebihSetor)
                                    <span class="fw-bold text-muted">Status: <span class="text-success">Lebih Setor</span></span>
                                    <span class="fw-bold text-success fs-5">Rp {{ number_format($nominalSelisih, 0, ',', '.') }}</span>
                                @else
                                    <span class="fw-bold text-muted">Status: Pas</span>
                                    <span class="fw-bold text-success fs-5">Rp 0</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </form>
</div>

@endsection

@push('scripts')
<script>
    function toggleAll(state) {
        const checkboxes = document.querySelectorAll('.unit-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = !!state;
            cb.closest('.card').classList.toggle('selected', cb.checked);
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
        if (!form) {
            alert('Form laporan tidak ditemukan');
            return;
        }

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