@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">

    <h4 class="fw-bold mb-4">Pengaturan Area</h4>

    <!-- Tombol Tambah -->
    <div class="mb-3 text-end">
        <a href="{{ route('area.create') }}" class="btn btn-warning text-white">
            <i class="bi bi-plus-circle me-2"></i> Tambah Area
        </a>
    </div>

    <!-- Tabel Area -->
    <div class="card shadow-sm border-0 p-3">
        <table class="table table-striped table-bordered">
            <thead class="table-secondary">
                <tr>
                    <th>#</th>
                    <th>Nama Area</th>
                    <th>Sales di Area</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dataArea ?? [] as $index => $area)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $area->nama_area }}</td>

                    {{-- Kolom Sales --}}
                    <td>
                        @if($area->sales->count() > 0)
                            <ul class="mb-0 ps-3">
                                @foreach($area->sales as $sales)
                                    <li>{{ $sales->user->name ?? 'Sales tanpa user' }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">Belum ada sales</span>
                        @endif
                    </td>

                    {{-- Kolom Aksi --}}
                    <td>
                        {{-- Tombol Edit SELALU ADA --}}
                        <a href="{{ route('area.edit', $area->id_area) }}" class="btn btn-primary btn-sm me-1">
                            <i class="bi bi-pencil"></i> Edit
                        </a>

                        {{-- Hapus hanya kalau tidak ada sales --}}
                        @if($area->sales->count() == 0)
                            <button type="button" class="btn btn-danger btn-sm btn-delete"
                                    data-url="{{ route('area.destroy', $area->id_area) }}">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary btn-sm" disabled
                                    title="Tidak bisa dihapus karena masih ada sales di area ini">
                                <i class="bi bi-lock"></i> Tidak dapat dihapus
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<!-- Script Modal Delete Universal -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const deleteForm = document.getElementById('deleteForm');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const url = this.dataset.url;
            deleteForm.action = url; // set action form
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });
});
</script>
@endsection
