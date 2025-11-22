@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">

    <h4 class="fw-bold mb-4">Edit PPN</h4>

    <div class="card shadow-sm border-0 p-4" style="max-width: 500px;">
        <form action="{{ route('ppn.update', $ppn->id_setting) }}" method="POST">
            @csrf
            @method('PUT') {{-- Method PUT untuk update --}}

            <!-- PPN (%) -->
            <div class="mb-3">
                <label class="fw-semibold">PPN (%)</label>
                <input type="text" name="presentase_ppn"
                       class="form-control border-bottom border-2 border-secondary pb-2"
                       placeholder="PPN"
                       value="{{ old('presentase_ppn', $ppn->presentase_ppn * 100) }}" required>
                @error('presentase_ppn')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('ppn.index') }}" class="btn btn-light px-4" style="border-radius:30px; border:1px solid #ddd;">
                    Batal
                </a>
                <button type="submit" class="btn btn-warning px-4 text-white" style="border-radius:30px;">
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
