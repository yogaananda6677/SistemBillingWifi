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

                    <div class="col-md-3 col-6 text-md-end">
                        <div class="btn-group w-100">
                            {{-- tombol export ikut bawa query + units[] --}}
                            <button type="button"
                                    class="btn btn-success btn-sm"
                                    onclick="exportLaporan('excel')">
                                Export Excel
                            </button>
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    onclick="exportLaporan('pdf')">
                                Export PDF
                            </button>
                        </div>
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
        @if(!empty($selectedUnits))
            <div class="card">
                <div class="card-header py-2">
                    <span class="fw-semibold">Rincian Transaksi (unit terpilih)</span>
                </div>
                <div class="card-body">
                    @foreach($rekap->whereIn('key', $selectedUnits) as $row)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">{{ $row->label }}</h6>

                            {{-- Pendapatan --}}
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Pembayaran</span>
                                    <span class="small text-muted">
                                        Total: Rp {{ number_format($row->pendapatan,0,',','.') }}
                                    </span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>No. Pembayaran</th>
                                                <th>Pelanggan</th>
                                                <th class="text-end">Nominal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($row->detail_pembayaran as $item)
                                                <tr>
                                                    <td>{{ $item->tanggal_bayar ? Carbon::parse($item->tanggal_bayar)->format('d/m/Y H:i') : '-' }}</td>
                                                    <td>{{ $item->no_pembayaran }}</td>
                                                    <td>{{ $item->nama_pelanggan ?? '-' }}</td>
                                                    <td class="text-end">
                                                        Rp {{ number_format($item->nominal,0,',','.') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-muted text-center">Tidak ada data.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Komisi (sales saja) --}}
                            @if($row->jenis === 'sales')
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold">Komisi</span>
                                        <span class="small text-muted">
                                            Total: Rp {{ number_format($row->total_komisi,0,',','.') }}
                                        </span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tanggal Bayar</th>
                                                    <th>No. Pembayaran</th>
                                                    <th>Pelanggan</th>
                                                    <th class="text-end">Jumlah</th>
                                                    <th class="text-end">Nominal Komisi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($row->detail_komisi as $item)
                                                    <tr>
                                                        <td>{{ $item->tanggal_bayar ? Carbon::parse($item->tanggal_bayar)->format('d/m/Y H:i') : '-' }}</td>
                                                        <td>{{ $item->no_pembayaran }}</td>
                                                        <td>{{ $item->nama_pelanggan ?? '-' }}</td>
                                                        <td class="text-end">{{ $item->jumlah_komisi }}</td>
                                                        <td class="text-end">
                                                            Rp {{ number_format($item->nominal_komisi,0,',','.') }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-muted text-center">Tidak ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Pengeluaran --}}
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold">Pengeluaran</span>
                                        <span class="small text-muted">
                                            Total: Rp {{ number_format($row->total_pengeluaran,0,',','.') }}
                                        </span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tanggal Approve</th>
                                                    <th>Nama Pengeluaran</th>
                                                    <th>Catatan</th>
                                                    <th class="text-end">Nominal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($row->detail_pengeluaran as $item)
                                                    <tr>
                                                        <td>{{ $item->tanggal_approve ? Carbon::parse($item->tanggal_approve)->format('d/m/Y H:i') : '-' }}</td>
                                                        <td>{{ $item->nama_pengeluaran }}</td>
                                                        <td>{{ $item->catatan ?? '-' }}</td>
                                                        <td class="text-end">
                                                            Rp {{ number_format($item->nominal,0,',','.') }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-muted text-center">Tidak ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Setoran --}}
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold">Setoran</span>
                                        <span class="small text-muted">
                                            Total: Rp {{ number_format($row->total_setoran,0,',','.') }}
                                        </span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tanggal Setoran</th>
                                                    <th>Admin Penerima</th>
                                                    <th>Catatan</th>
                                                    <th class="text-end">Nominal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($row->detail_setoran as $item)
                                                    <tr>
                                                        <td>{{ $item->tanggal_setoran ? Carbon::parse($item->tanggal_setoran)->format('d/m/Y H:i') : '-' }}</td>
                                                        <td>{{ $item->nama_admin ?? '-' }}</td>
                                                        <td>{{ $item->catatan ?? '-' }}</td>
                                                        <td class="text-end">
                                                            Rp {{ number_format($item->nominal,0,',','.') }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-muted text-center">Tidak ada data.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <hr>
                        </div>
                    @endforeach
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
