@extends('layouts.master')
@section('title', 'Setoran Sales')

@section('content')
<div class="container-fluid py-4">

    <h3 class="mb-4 fw-bold">Input Setoran Sales</h3>

    {{-- FLASH MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        {{-- FORM INPUT SETORAN --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold">Form Setoran Baru</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.setoran.store') }}" method="POST">
                        @csrf

                        {{-- SALES --}}
                        <div class="mb-3">
                            <label class="form-label">Sales</label>
                            <select name="id_sales" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Sales --</option>
                                @foreach($sales as $s)
                                    <option value="{{ $s->id_sales }}"
                                        {{ old('id_sales') == $s->id_sales ? 'selected' : '' }}>
                                        {{ $s->nama_sales }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- TANGGAL SETORAN --}}
                        <div class="mb-3">
                            <label class="form-label">Tanggal Setoran</label>
                            <input type="datetime-local"
                                   name="tanggal_setoran"
                                   class="form-control form-control-sm"
                                   value="{{ old('tanggal_setoran', now()->format('Y-m-d\TH:i')) }}"
                                   required>
                        </div>

                        {{-- NOMINAL --}}
                        <div class="mb-3">
                            <label class="form-label">Nominal Setoran</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="number"
                                       name="nominal"
                                       class="form-control"
                                       min="1"
                                       step="1"
                                       value="{{ old('nominal') }}"
                                       required>
                            </div>
                        </div>

                        {{-- CATATAN --}}
                        <div class="mb-3">
                            <label class="form-label">Catatan (opsional)</label>
                            <textarea name="catatan"
                                      rows="3"
                                      class="form-control form-control-sm"
                                      placeholder="Contoh: Setor tunai untuk pendapatan bulan ini.">{{ old('catatan') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            Simpan Setoran
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIWAYAT SETORAN --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Riwayat Setoran Terbaru</h6>
                    <small class="text-muted">
                        Total: {{ $riwayat->count() }} data terakhir
                    </small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal Setoran</th>
                                    <th>Sales</th>
                                    <th>Admin Penerima</th>
                                    <th class="text-end">Nominal</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayat as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row->tanggal_setoran)->format('d/m/Y H:i') }}</td>
                                        <td>{{ $row->nama_sales }}</td>
                                        <td>{{ $row->nama_admin }}</td>
                                        <td class="text-end">
                                            Rp {{ number_format($row->nominal, 0, ',', '.') }}
                                        </td>
                                        <td>{{ $row->catatan ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            Belum ada setoran yang dicatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div> {{-- row --}}
</div>
@endsection
