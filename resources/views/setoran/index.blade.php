@extends('layouts.master')
@section('title', 'Sales - Setoran')

@section('content')
<style>
    .card-soft {
        border-radius: 18px;
        box-shadow: 0 6px 20px rgba(0,0,0,.06);
        border: none;
    }
    .header-strip {
        border-radius: 0 0 18px 18px;
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
    .text-link-yellow {
        color: #FFC400;
        font-weight: 600;
    }
</style>

<div class="container-fluid py-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-soft">
        <div class="card-body">

            {{-- Pencarian --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="flex-grow-1 me-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0"
                               placeholder="Cari nama sales..."
                               onkeyup="filterSales(this.value)">
                    </div>
                </div>
            </div>

            {{-- Tabel sales --}}
            <div class="table-responsive">
                <table class="table table-sm align-middle" id="tableSales">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">No</th>
                            <th>Nama Sales</th>
                            <th class="text-end">Target Setor</th>
                            <th class="text-end">Setor</th>
                            <th class="text-end">Sisa / Kelebihan</th>
                            <th class="text-center" style="width:140px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $i => $s)
                            <tr>
                                <td>{{ sprintf('%03d', $i+1) }}</td>
                                <td>{{ $s->nama_sales }}</td>

                                {{-- TARGET SETOR (sementara 0 kalau belum ada di DB) --}}
                                <td class="text-end">
                                    Rp {{ number_format($s->target_setor ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- TOTAL SETOR --}}
                                <td class="text-end text-success">
                                    Rp {{ number_format($s->total_setor, 0, ',', '.') }}
                                </td>

                                {{-- SISA / KELEBIHAN --}}
                                @php
                                    // sisa = target - total_setor (sudah dihitung di controller)
                                    $isKelebihan = $s->sisa < 0;   // jika minus berarti kelebihan
                                    $jumlah = abs($s->sisa);      // hilangkan tanda minus
                                @endphp
                                <td class="text-end
                                    @if($jumlah == 0)
                                        text-muted
                                    @elseif($isKelebihan)
                                        text-success
                                    @else
                                        text-danger
                                    @endif
                                ">
                                    @if($jumlah == 0)
                                        Pas: Rp 0
                                    @elseif($isKelebihan)
                                        Kelebihan: Rp {{ number_format($jumlah, 0, ',', '.') }}
                                    @else
                                        Sisa: Rp {{ number_format($jumlah, 0, ',', '.') }}
                                    @endif
                                </td>

                                {{-- AKSI --}}
                                <td class="text-center">
                                    <a href="{{ route('admin.setoran.riwayat', $s->id_sales) }}"
                                       class="btn btn-nalen btn-sm">
                                        Tambah Setoran
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    Belum ada data sales.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script>
    function filterSales(keyword) {
        keyword = keyword.toLowerCase();
        document.querySelectorAll('#tableSales tbody tr').forEach(function (row) {
            const nama = row.cells[1].innerText.toLowerCase();
            row.style.display = nama.includes(keyword) ? '' : 'none';
        });
    }
</script>
@endsection
