@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">

    <div class="card shadow-sm p-4">
        <h4 class="mb-4 fw-bold">Tambah Sales</h4>

        {{-- FORM --}}
        <form action="{{ route('data-sales.store') }}" method="POST">
            @csrf

            <div class="row">

                {{-- Nama --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Sales</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                {{-- No HP --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" class="form-control" required>
                </div>

                {{-- Username/Email --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username (email)</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                {{-- Password --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                {{-- Area --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Area</label>
                    <select name="id_area" class="form-control" required>
                        <option value="">-- Pilih Area --</option>
                        @foreach($area as $a)
                            <option value="{{ $a->id_area }}">{{ $a->nama_area }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Komisi --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Komisi (Nominal)</label>
                    <input type="number" name="komisi" class="form-control" placeholder="Contoh: 5000">
                </div>

            </div>

            {{-- BUTTON --}}
            <div class="mt-4 d-flex justify-content-end">
                <a href="{{ route('data-sales.index') }}" class="btn btn-secondary me-2">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>

        </form>
    </div>

</div>
@endsection
