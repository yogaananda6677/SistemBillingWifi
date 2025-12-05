@extends('layouts.master')
@section('title', 'Sales - Setoran - Riwayat')

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

    {{-- Bar atas: bulan & admin --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="fs-6">
            <span>Default Mounth: </span>
            <a href="#" class="text-link-yellow">{{ $namaBulan }}</a>
        </div>
        <div class="fw-bold text-uppercase">
            ADMIN {{ strtoupper(Auth::user()->name ?? 'ADMIN') }}
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-soft mb-3">
        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center"
             style="border-radius: 18px 18px 0 0;">
            <span class="fw-semibold">Riwayat Setoran</span>

            {{-- TOMBOL PEMICU MODAL --}}
            <button type="button"
                    class="btn btn-nalen btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambahSetoran">
                Tambah Setoran
            </button>
        </div>

        <div class="card-body">

            @php
                $isKelebihanGlobal = $sisa < 0;
                $jumlahGlobal = abs($sisa);
                $classGlobal = $jumlahGlobal == 0
                    ? 'text-muted'
                    : ($isKelebihanGlobal ? 'text-success' : 'text-danger');
            @endphp

            {{-- INFO SALES + STATUS GLOBAL --}}
            <div class="mb-3">
                <div class="fw-semibold">{{ $sales->nama_sales }}</div>
                <div>
                    Status bulan ini:
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
                <div class="small text-muted">
                    Total setor bulan ini: Rp {{ number_format($totalSetor, 0, ',', '.') }}
                </div>
            </div>

            {{-- TABEL RIWAYAT --}}
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">No</th>
                            <th>Tanggal Setor</th>
                            <th class="text-end">Nominal</th>
                            <th class="text-end">Sisa/Kelebihan (saat ini)</th>
                            <th>Diterima</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // status global dipakai ulang per baris (saat ini)
                            $labelGlobal = $jumlahGlobal == 0
                                ? 'Pas'
                                : ($isKelebihanGlobal ? 'Kelebihan' : 'Sisa');
                        @endphp

                        @forelse($riwayat as $i => $row)
                            <tr>
                                <td>{{ sprintf('%03d', $i+1) }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->tanggal_setoran)->format('d M Y | H:i') }}</td>
                                <td class="text-end text-success">
                                    Rp {{ number_format($row->nominal, 0, ',', '.') }}
                                </td>
                                <td class="text-end {{ $classGlobal }}">
                                    @if($jumlahGlobal == 0)
                                        {{ $labelGlobal }}: Rp 0
                                    @else
                                        {{ $labelGlobal }}: Rp {{ number_format($jumlahGlobal, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td>{{ $row->nama_admin }}</td>
                                <td>{{ $row->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    Belum ada setoran pada bulan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <a href="{{ route('admin.setoran.index') }}" class="btn btn-outline-secondary btn-sm">
        &laquo; Kembali ke data sales
    </a>
</div>

{{-- MODAL TAMBAH SETORAN --}}
<div class="modal fade" id="modalTambahSetoran" tabindex="-1" aria-labelledby="modalTambahSetoranLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 18px;">
      <div class="modal-header bg-warning text-white" style="border-radius: 18px 18px 0 0;">
        <h6 class="modal-title" id="modalTambahSetoranLabel">Tambah Setoran</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="{{ route('admin.setoran.store') }}" method="POST">
        @csrf
        <input type="hidden" name="id_sales" value="{{ $sales->id_sales }}">

        <div class="modal-body">

          <p class="mb-2">
            Sales: <strong>{{ $sales->nama_sales }}</strong><br>
            <small>Status bulan ini:
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
