@extends('layouts.master')
@section('title', 'Tambah Setoran')

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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
            <span class="fw-semibold">Tambah Setoran</span>
        </div>

        <div class="card-body">

            <p class="mb-2">
                Sales: <strong>{{ $sales->nama_sales }}</strong>
            </p>

            <form action="{{ route('admin.setoran.store') }}" method="POST" class="mt-3">
                @csrf
                <input type="hidden" name="id_sales" value="{{ $sales->id_sales }}">

                <div class="mb-3">
                    <label class="form-label">Nominal</label>
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
                    @error('nominal')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan"
                              rows="3"
                              class="form-control form-control-sm"
                              placeholder="Opsional...">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.setoran.index') }}" class="btn btn-outline-secondary btn-sm">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-nalen btn-sm">
                        Tambah
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
