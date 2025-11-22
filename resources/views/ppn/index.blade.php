@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">

    <h4 class="fw-bold mb-4">Pengaturan PPN</h4>

    <div class="card shadow-sm border-0 p-4">
        @if($ppn) {{-- Jika data PPN sudah ada --}}
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5>{{ $ppn->nama_ppn }}</h5>
                    <p>Persentase: <strong>{{ $ppn->presentase_ppn * 100 }}%</strong></p>
                    {{-- <p>Status:
                        @if($ppn->status == 'aktif')
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </p> --}}
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('ppn.edit', $ppn->id_setting) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                <button type="button" class="btn btn-danger btn-delete"
                        data-url="{{ route('ppn.destroy', $ppn->id_setting) }}">
                    Hapus
                </button>


                </div>
            </div>
        @else {{-- Jika data PPN belum ada --}}
            <div class="text-center">
                <p>Belum ada data PPN.</p>
                <a href="{{ route('ppn.create') }}" class="btn btn-warning text-white">
                    <i class="bi bi-plus-circle me-2"></i> Tambah PPN
                </a>
            </div>
        @endif
    </div>

</div>

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
