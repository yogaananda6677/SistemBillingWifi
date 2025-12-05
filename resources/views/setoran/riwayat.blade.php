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

    @php
        $namaBulan = $namaBulan ?? now()->translatedFormat('F');

        // status bulan ini (Sisa / Pas)
        $sisaNilai   = $sisaBulan ?? 0;
        $isKelebihan = false; // di desain ini sisa >= 0 (kelebihan dicatat terpisah)
        $angkaSisa   = abs($sisaNilai);
        $classSisa   = $angkaSisa == 0
                        ? 'text-success'
                        : 'text-danger';

        // saldo global: + = kelebihan, - = masih kurang total
        $saldo = $saldoGlobal ?? 0;
        $isKelebihanGlobal = $saldo > 0;
        $jumlahGlobal = abs($saldo);
        $classGlobal = $jumlahGlobal == 0
            ? 'text-muted'
            : ($isKelebihanGlobal ? 'text-success' : 'text-danger');
    @endphp

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KARTU RIWAYAT + MODAL --}}
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

            {{-- INFO SALES + REKAP BULAN INI --}}
            <div class="mb-3">
                <div class="fw-semibold mb-1">
                    {{ $sales->nama_sales }}
                </div>

                <div class="small text-muted">
                    <div>
                        Wajib setor bulan ini:
                        <strong>Rp {{ number_format($wajibBulan, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        Setoran yang menutup kewajiban bulan ini:
                        <strong>Rp {{ number_format($alokBulanIni, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        Sisa kewajiban bulan ini:
                        <span class="{{ $classSisa }}">
                            @if($angkaSisa == 0)
                                Pas: Rp 0
                            @else
                                Sisa: Rp {{ number_format($angkaSisa, 0, ',', '.') }}
                            @endif
                        </span>
                    </div>
                    <div>
                        Kelebihan yang tercatat di bulan ini:
                        <span class="{{ $kelebihanBulan > 0 ? 'text-success' : 'text-muted' }}">
                            Rp {{ number_format($kelebihanBulan, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="mt-2 small">
                    Posisi akumulasi (semua bulan):
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

            {{-- OPSIONAL: REKAP PER BULAN (dari awal sampai sekarang) --}}
            @if(!empty($saldoPerBulan))
                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Periode</th>
                                <th class="text-end">Wajib Setor</th>
                                <th class="text-end">Sudah Dialokasikan</th>
                                <th class="text-end">Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($saldoPerBulan as $periode => $s)
                                @php
                                    $wajib   = $s['wajib']   ?? 0;
                                    $dibayar = $s['dibayar'] ?? 0;
                                    $kurang  = $wajib - $dibayar;
                                    $kurangAbs = abs($kurang);
                                    $class = $kurangAbs == 0
                                        ? 'text-success'
                                        : 'text-danger';
                                @endphp
                                <tr>
                                    <td>{{ $periode }}</td>
                                    <td class="text-end">
                                        Rp {{ number_format($wajib, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        Rp {{ number_format($dibayar, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end {{ $class }}">
                                        @if($kurangAbs == 0)
                                            Pas: Rp 0
                                        @else
                                            Sisa: Rp {{ number_format($kurangAbs, 0, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Belum ada kewajiban yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- TABEL SETORAN BULAN INI --}}
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">No</th>
                            <th>Tanggal Setor</th>
                            <th class="text-end">Nominal</th>
                            <th>Dialokasikan ke</th>
                            <th>Diterima</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($setorans as $i => $row)
                            @php
                                $alokasi = $allocDetail[$row->id_setoran] ?? [];
                            @endphp
                            <tr>
                                <td>{{ sprintf('%03d', $i+1) }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->tanggal_setoran)->format('d M Y | H:i') }}</td>
                                <td class="text-end text-success">
                                    Rp {{ number_format($row->nominal, 0, ',', '.') }}
                                </td>
                                <td>
                                    @forelse($alokasi as $aloc)
                                        @php
                                            $label = !empty($aloc['lebih'])
                                                ? 'Kelebihan di'
                                                : 'Menutup';
                                        @endphp
                                        <div class="small">
                                            {{ $label }} {{ $aloc['periode'] }} :
                                            Rp {{ number_format($aloc['nominal'], 0, ',', '.') }}
                                        </div>
                                    @empty
                                        <span class="text-muted small">
                                            Tidak ada kewajiban yang dialokasikan.
                                        </span>
                                    @endforelse
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
            <small>Posisi bulan ini:
                <span class="{{ $classSisa }}">
                    @if($angkaSisa == 0)
                        Pas: Rp 0
                    @else
                        Sisa: Rp {{ number_format($angkaSisa, 0, ',', '.') }}
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
