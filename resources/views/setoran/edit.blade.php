@extends('layouts.master')
@section('title', 'Edit Setoran Wilayah')

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
</style>

<div class="container-fluid py-3">
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-soft">
        <div class="card-header bg-warning text-white" style="border-radius: 18px 18px 0 0;">
            <span class="fw-semibold">
                Edit Setoran – {{ $setoran->nama_sales }} ({{ $setoran->nama_area }})
            </span>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.setoran.update', $setoran->id_setoran) }}" method="POST" class="mt-3">
                @csrf
                @method('PUT')

                {{-- PERIODE SETORAN --}}
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

                <div class="mb-2 small text-muted">
                    Tanggal setor tersimpan: {{ \Carbon\Carbon::parse($setoran->tanggal_setoran)->format('d M Y') }}
                </div>

                <div class="mb-3">
                    <label class="form-label">Nominal</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number"
                               name="nominal"
                               class="form-control"
                               min="1"
                               step="1"
                               value="{{ old('nominal', $setoran->nominal) }}"
                               required>
                    </div>
                    @error('nominal')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan"
                              rows="3"
                              class="form-control form-control-sm"
                              placeholder="Opsional...">{{ old('catatan', $setoran->catatan) }}</textarea>
                    @error('catatan')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.setoran.riwayat', [
                            'id_sales' => $setoran->id_sales,
                            'id_area'  => $setoran->id_area,
                            'tahun'    => $tahun,
                            'bulan'    => $bulan,
                        ]) }}"
                       class="btn btn-outline-secondary btn-sm">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-nalen btn-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
