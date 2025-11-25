@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">

    <div class="card shadow-sm p-4">
        <h4 class="mb-4 fw-bold">Edit Sales</h4>

        <form action="{{ route('data-sales.update', $sales->id_sales) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- Nama --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Sales</label>
                    <input type="text" name="name" value="{{ $sales->user->name }}" class="form-control" required>
                </div>

                {{-- No HP --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" value="{{ $sales->user->no_hp }}" class="form-control" required>
                </div>

                {{-- Username --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username (email)</label>
                    <input type="email" name="email" value="{{ $sales->user->email }}" class="form-control" required>
                </div>

                {{-- Password --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password (opsional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Isi jika ingin mengganti password">
                </div>

                {{-- Area --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Area</label>
                    <select name="id_area" class="form-control" required>
                        <option value="">-- Pilih Area --</option>
                        @foreach($area as $a)
                            <option value="{{ $a->id_area }}"
                                {{ $sales->id_area == $a->id_area ? 'selected' : '' }}>
                                {{ $a->nama_area }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Komisi --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Komisi</label>
                    <input type="number" step="0.01" name="komisi" value="{{ $sales->komisi }}" class="form-control">
                </div>

            </div>

            <div class="mt-4 d-flex justify-content-end">
                <a href="{{ route('data-sales.index') }}" class="btn btn-secondary me-2">Kembali</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>

        </form>
    </div>

</div>
@endsection
