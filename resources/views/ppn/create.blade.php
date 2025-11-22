@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">

    <h4 class="fw-bold mb-4">Tambah PPN</h4>

    <div class="card shadow-sm border-0 p-4" style="max-width: 500px;">
        <form action="{{ route('ppn.store') }}" method="POST">
            @csrf

            <!-- Nama PPN -->
            <div class="mb-3">
                <label class="fw-semibold">PPN (%)</label>
                <input type="text" name="presentase_ppn" class="form-control border-bottom border-2 border-secondary pb-2"
                       placeholder="PPN" value="{{ old('presentase_ppn') }}" required>
                @error('presentase_ppn')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- <!-- Persentase -->
            <div class="mb-3">
                <label class="fw-semibold">Persentase (%)</label>
                <input type="number" name="persentase" class="form-control border-0 border-bottom  border-secondary pb-2"
                       placeholder="10" value="{{ old('persentase') }}" required min="0" max="100" step="0.01">
                @error('persentase')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div> --}}

            {{-- <!-- Status -->
            <div class="mb-3">
                <label class="fw-semibold">Status</label>
                <select name="status" class="form-select border-0 border-bottom border-2 border-secondary pb-2" required>
                    <option value="" disabled selected>Pilih Status</option>
                    <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @error('status')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div> --}}

            <!-- Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('ppn.index') }}" class="btn btn-light px-4" style="border-radius:30px; border:1px solid #ddd;">
                    Batal
                </a>
                <button type="submit" class="btn btn-warning px-4 text-white" style="border-radius:30px;">
                    Simpan
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
