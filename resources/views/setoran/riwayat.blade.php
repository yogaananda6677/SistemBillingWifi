@extends('layouts.master')
@section('title', 'Sales - Setoran - Riwayat per Wilayah')

@section('content')
<style>
    .card-soft {
        border-radius: 18px;
        box-shadow: 0 6px 20px rgba(0,0,0,.06);
        border: none;
    }
    .btn-nalen {
        background: #FFC400;
        border: none;
        border-radius: 999px;
        font-weight: 600;
        padding-inline: 18px;
    }
    .btn-nalen:hover {
        background: #ffb000;
    }
    .text-link-yellow {
        color: #FFC400;
        font-weight: 600;
        text-decoration: none;
    }
</style>

<div class="container-fluid py-3">

    @php
        $namaBulan = $namaBulan ?? now()->translatedFormat('F');

        $saldoBulan = ($totalSetoranBulan ?? 0) - ($wajibBulan ?? 0); // + = kelebihan, - = sisa
        $isKelebihanGlobal = $saldoBulan > 0;
        $jumlahGlobal = abs($saldoBulan);
        $classGlobal = $jumlahGlobal == 0
            ? 'text-muted'
            : ($isKelebihanGlobal ? 'text-success' : 'text-danger');
    @endphp

    {{-- Bar atas --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="fs-6">
            <div>
                <span>Periode: </span>
                <a href="#" class="text-link-yellow">{{ $namaBulan }} {{ $tahun }}</a>
            </div>
            <div class="small text-muted">
                Wilayah: <strong>{{ $salesArea->nama_area }}</strong>
            </div>
        </div>
        <div class="text-end">
            <div class="fw-bold text-uppercase">
                ADMIN {{ strtoupper(Auth::user()->name ?? 'ADMIN') }}
            </div>
            <small class="text-muted">
                Sales: <strong>{{ $salesArea->nama_sales }}</strong>
            </small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KARTU RIWAYAT --}}
    <div class="card card-soft mb-3">
        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center"
             style="border-radius: 18px 18px 0 0;">
            <span class="fw-semibold">
                Riwayat Setoran – {{ $salesArea->nama_sales }} ({{ $salesArea->nama_area }})
            </span>

            <button type="button"
                    class="btn btn-nalen btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambahSetoran">
                Tambah Setoran
            </button>
        </div>

        <div class="card-body">

            {{-- STATUS BULAN INI --}}
            <div class="mb-3">
                <div>
                    Posisi bulan ini:
                    <span class="{{ $classGlobal }}">
                        @if($jumlahGlobal == 0)
                            Pas: Rp 0
                        @elseif($isKelebihanGlobal)
                            Kelebihan: Rp {{ number_format($jumlahGlobal, 0, ',', '.') }}
                        @else
                            Sisa: Rp {{ number_format($jumlahGlobal, 0, ',', '.') }}
                        @endif
                    </span>
                </div>
            </div>

            {{-- RINGKASAN BULAN INI --}}
            <div class="mb-3">
                <div>
                    Wajib setor bulan ini:
                    <strong>Rp {{ number_format($wajibBulan, 0, ',', '.') }}</strong>
                </div>
                <div>
                    Total setoran bulan ini:
                    <strong>Rp {{ number_format($totalSetoranBulan, 0, ',', '.') }}</strong>
                </div>
                <div>
                    Kelebihan bulan ini:
                    <strong class="text-success">
                        Rp {{ number_format($kelebihanBulan ?? 0, 0, ',', '.') }}
                    </strong>
                </div>
                <div>
                    Sisa kewajiban bulan ini:
                    <span class="{{ $sisaBulan > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($sisaBulan, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- TABEL RIWAYAT SETORAN BULAN INI --}}
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">No</th>
                            <th>Tanggal Setor</th>
                            <th class="text-end">Nominal</th>
                            <th>Diterima</th>
                            <th>Catatan</th>
                            <th class="text-center" style="width:160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($setorans as $i => $row)
                            <tr>
                                <td>{{ sprintf('%03d', $i+1) }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->tanggal_setoran)->format('d M Y') }}</td>
                                <td class="text-end text-success">
                                    Rp {{ number_format($row->nominal, 0, ',', '.') }}
                                </td>
                                <td>{{ $row->nama_admin }}</td>
                                <td>{{ $row->catatan ?? '-' }}</td>
                                <td class="text-center">
                                    {{-- EDIT --}}
                                    <a href="{{ route('admin.setoran.edit', [
                                            'id_setoran' => $row->id_setoran,
                                            'tahun'      => $tahun,
                                            'bulan'      => $bulan,
                                        ]) }}"
                                       class="btn btn-warning btn-sm">
                                        Edit
                                    </a>

                                    {{-- HAPUS --}}
                                    <form action="{{ route('admin.setoran.destroy', $row->id_setoran) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus setoran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                                        <input type="hidden" name="bulan" value="{{ $bulan }}">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    Belum ada setoran di bulan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <a href="{{ route('admin.setoran.index', [
            'tahun' => $tahun,
            'bulan' => $bulan,
        ]) }}" class="btn btn-outline-secondary btn-sm">
        &laquo; Kembali ke daftar wilayah
    </a>
</div>

{{-- MODAL TAMBAH SETORAN --}}
<div class="modal fade" id="modalTambahSetoran" tabindex="-1" aria-labelledby="modalTambahSetoranLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 18px;">
      <div class="modal-header bg-warning text-white" style="border-radius: 18px 18px 0 0;">
        <h6 class="modal-title" id="modalTambahSetoranLabel">
            Tambah Setoran – {{ $salesArea->nama_sales }} ({{ $salesArea->nama_area }})
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="{{ route('admin.setoran.store') }}" method="POST">
        @csrf
        <input type="hidden" name="id_sales" value="{{ $salesArea->id_sales }}">
        <input type="hidden" name="id_area"  value="{{ $salesArea->id_area }}">

        <div class="modal-body">
          <p class="mb-2">
            <small>Posisi bulan ini:
                <span class="{{ $classGlobal }}">
                    @if($jumlahGlobal == 0)
                        Pas: Rp 0
                    @elseif($isKelebihanGlobal)
                        Kelebihan: Rp {{ number_format($jumlahGlobal, 0, ',', '.') }}
                    @else
                        Sisa: Rp {{ number_format($jumlahGlobal, 0, ',', '.') }}
                    @endif
                </span>
            </small>
          </p>

          {{-- Periode Setoran --}}
          <div class="mb-3">
            <label class="form-label small">Periode Setoran</label>
            <div class="d-flex gap-2">
                <select name="bulan" class="form-select form-select-sm" style="max-width: 140px;">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ (int)$bulan === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
                <select name="tahun" class="form-select form-select-sm" style="max-width: 110px;">
                    @foreach (range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Nominal</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text">Rp</span>
              <input type="number" name="nominal" class="form-control"
                     min="1" step="1" value="{{ old('nominal') }}" required>
            </div>
            @error('nominal')
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="mb-2">
            <label class="form-label">Catatan</label>
            <textarea name="catatan" rows="3"
                      class="form-control form-control-sm"
                      placeholder="Opsional...">{{ old('catatan') }}</textarea>
            @error('catatan')
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

        </div>

        <div class="modal-footer d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
            Batal
          </button>
          <button type="submit" class="btn btn-nalen btn-sm">
            Tambah
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection
