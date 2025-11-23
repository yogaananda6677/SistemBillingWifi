@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">

    <h4 class="fw-bold mb-4">Edit Area</h4>

    <div class="card shadow-sm border-0 p-4" style="max-width: 500px;">
        <form action="{{ route('area.update', $area->id_area) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Nama Area -->
            <div class="mb-3">
                <label class="fw-semibold">Nama Area</label>
                <input type="text" name="nama_area" class="form-control border-bottom border-2 border-secondary pb-2"
                       placeholder="Nama Area" value="{{ old('nama_area', $area->nama_area) }}" required>
                @error('nama_area')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('area.index') }}" class="btn btn-light px-4" style="border-radius:30px; border:1px solid #ddd;">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary px-4 text-white" style="border-radius:30px;">
                    Update
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
