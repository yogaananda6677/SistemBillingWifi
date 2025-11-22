@extends('layouts.master')

@section('content')
<style>
    .page-title {
        font-size: 22px;
        font-weight: 700;
        color: #222;
    }

    .table-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    table thead th {
        background: #f8f9fa;
        font-size: 13px;
        font-weight: 600;
        padding: 10px;
    }

    table tbody td {
        font-size: 13px;
        padding: 10px;
    }

    table tbody tr:hover {
        background: #f4f4f4;
    }

    .no-results {
        text-align: center;
        padding: 20px;
        color: #6c757d;
        font-style: italic;
    }
</style>

<div class="container-fluid p-4">

    {{-- TITLE --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">Daftar Paket</h4>
        <a href="{{ route('paket-layanan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i> Tambah Paket
        </a>
    </div>

    {{-- TABLE --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Paket</th>
                        <th>Kecepatan</th>
                        <th>Harga Dasar</th>
                        <th>PPN Nominal</th>
                        <th>Harga Total</th>

                        {{-- <th>Status</th> --}}
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataPaket as $index => $p)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $p->nama_paket }}</td>
                            <td>{{ $p->kecepatan }}</td>
                            <td>{{ number_format($p->harga_dasar,0,',','.') }}</td>
                            {{-- <td>{{ $p->ppn_nominal }}%</td> --}}
                            <td>{{ number_format($p->ppn_nominal,0,',','.') }}</td>
                            <td>{{ number_format($p->harga_total,0,',','.') }}</td>
                            {{-- <td>
                                @if($p->status == 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td> --}}
                            <td>
                                <a href="{{ route('paket-layanan.edit', $p->id_paket) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger btn-delete"
                                        data-url="{{ route('paket-layanan.destroy', $p->id_paket) }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center no-results">
                                Belum ada data paket.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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


