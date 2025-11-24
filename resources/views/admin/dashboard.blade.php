@extends('layouts.master')

@section('content')

<style>
    .progress-bar-custom {
        height: 10px;
        border-radius: 10px;
    }
    .progress-label {
        font-size: 13px;
        font-weight: 600;
    }
</style>

<div class="container-fluid p-4" style="max-height: 100vh; overflow-y: auto;">

    {{-- =================== TITLE =================== --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Dashboard</h4>
        <div class="dropdown d-inline">
    <span class="text-secondary">Default Month:</span>

    <a class="dropdown-toggle text-secondary text-decoration-none" href="#" role="button" data-bs-toggle="dropdown">
        <strong id="selectedMonth">Januari</strong>
    </a>

    <ul class="dropdown-menu">
        <li><a class="dropdown-item month-item" data-value="Januari">Januari</a></li>
        <li><a class="dropdown-item month-item" data-value="Februari">Februari</a></li>
        <li><a class="dropdown-item month-item" data-value="Maret">Maret</a></li>
        <li><a class="dropdown-item month-item" data-value="April">April</a></li>
        <li><a class="dropdown-item month-item" data-value="Mei">Mei</a></li>
        <li><a class="dropdown-item month-item" data-value="Juni">Juni</a></li>
        <li><a class="dropdown-item month-item" data-value="Juli">Juli</a></li>
        <li><a class="dropdown-item month-item" data-value="Agustus">Agustus</a></li>
        <li><a class="dropdown-item month-item" data-value="September">September</a></li>
        <li><a class="dropdown-item month-item" data-value="Oktober">Oktober</a></li>
        <li><a class="dropdown-item month-item" data-value="November">November</a></li>
        <li><a class="dropdown-item month-item" data-value="Desember">Desember</a></li>
    </ul>

    {{-- <i class="bi bi-chevron-right"></i> --}}
</div>



    </div>

    {{-- =================== TOTAL PEMBAYARAN =================== --}}
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-secondary fw-semibold">Total Pembayaran Diterima</small>
                        <h5 class="fw-bold text-success mt-1">{{ rupiah($totalPembayaranTerima) }}</h5>
                    </div>
                    <i class="bi bi-receipt-cutoff fs-2 text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-secondary fw-semibold">Total Pembayaran Terlambat</small>
                        <h5 class="fw-bold text-danger mt-1">{{ rupiah($totalPembayaranTerlambat) }}</h5>
                    </div>
                    <i class="bi bi-exclamation-octagon fs-2 text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- =================== MINI COUNTERS =================== --}}
    <div class="row g-3 mt-3">

        @foreach($counters as $c)
        <div class="col-6 col-lg-3">
            <div class="card p-3 shadow-sm border-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi {{ $c['icon'] }} fs-3 {{ $c['color'] }}"></i>
                    <div>
                        <small>{{ $c['label'] }}</small>
                        <h6 class="fw-bold">{{ number_format($c['value'], 0, ',', '.') }}</h6>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

    </div>


    {{-- =================== CHART STATUS =================== --}}
    <div class="row mt-4 g-4">

        <div class="col-12 col-lg-6">
            <div class="card p-3 shadow-sm border-0">
                <h6 class="fw-bold">Status Pembayaran</h6>
                <div class="text-center">
                    <img src="/img/chart1.png" class="img-fluid" style="max-width: 260px;">
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card p-3 shadow-sm border-0">
                <h6 class="fw-bold">Status Pelanggan</h6>
                <div class="text-center">
                    <img src="/img/chart2.png" class="img-fluid" style="max-width: 260px;">
                </div>
            </div>
        </div>

    </div>

    {{-- =================== PROGRES SALES =================== --}}
    <div class="card p-4 shadow-sm mt-4 border-0">
        <h6 class="fw-bold">Progres Penarikan Pembayaran Per Sales</h6>

        @php
            $sales = [
                ['nama'=>'Yoga Ananda','percent'=>40,'done'=>20,'total'=>50],
                ['nama'=>'Irfan','percent'=>75,'done'=>40,'total'=>50],
                ['nama'=>'Nugroho Arya','percent'=>25,'done'=>15,'total'=>50],
                ['nama'=>'Nayla','percent'=>50,'done'=>30,'total'=>50],
                ['nama'=>'Annisa','percent'=>40,'done'=>25,'total'=>50],
            ];
        @endphp

        @foreach($sales as $s)
        <div class="row my-2">
            <div class="col-4">
                <span class="progress-label">{{ $s['nama'] }}</span>
            </div>
            <div class="col-6">
                <div class="bg-light rounded">
                    <div class="bg-primary progress-bar-custom" style="width: {{ $s['percent'] }}%;"></div>
                </div>
            </div>
            <div class="col-2 text-end">
                <small class="fw-bold">{{ $s['percent'] }}%
                    <span class="text-secondary">
                        ({{ $s['done'] }}/{{ $s['total'] }} Pelanggan)
                    </span>
                </small>
            </div>
        </div>
        @endforeach
    </div>

    {{-- =================== TABEL PELANGGAN =================== --}}
    <div class="row mt-4 g-3">

        {{-- BELUM BAYAR --}}
        <div class="col-12 col-lg-6">
            <div class="card p-3 shadow-sm border-0">
                <h6 class="fw-bold">Pelanggan Belum Bayar Terbaru</h6>
                <table class="table table-sm mt-2">
                    <thead>
                        <tr>
                            <th>No</th><th>Nama</th><th>Wilayah</th><th>Tagihan</th><th>Sales</th><th>Jatuh Tempo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i=1;$i<=5;$i++)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>Yoga Ananda</td>
                            <td>Kediri Kab. Ngasem</td>
                            <td>1.980.000,00</td>
                            <td>Irfan</td>
                            <td>15-11-2015</td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
                <a href="#" class="text-primary fw-semibold">Detail →</a>
            </div>
        </div>

        {{-- SUDAH BAYAR --}}
        <div class="col-12 col-lg-6">
            <div class="card p-3 shadow-sm border-0">
                <h6 class="fw-bold">Pelanggan Sudah Bayar Terbaru</h6>
                <table class="table table-sm mt-2">
                    <thead>
                        <tr>
                            <th>No</th><th>Nama</th><th>Wilayah</th><th>Tagihan</th><th>Sales</th><th>Jatuh Tempo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i=1;$i<=5;$i++)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>Yoga Ananda</td>
                            <td>Kediri Kab. Ngasem</td>
                            <td>1.980.000,00</td>
                            <td>Irfan</td>
                            <td>15-11-2015</td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
                <a href="#" class="text-primary fw-semibold">Detail →</a>
            </div>
        </div>

    </div>

    {{-- =================== GRAFIK BAR =================== --}}
    <div class="card p-4 shadow-sm border-0 mt-4">
        <div class="d-flex justify-content-between">
            <h6 class="fw-bold">Pendapatan Bulanan</h6>
            <button class="btn btn-sm btn-light">Filter <i class="bi bi-chevron-down"></i></button>
        </div>

        <div class="text-center mt-3">
            <img src="/img/chart-bar.png" class="img-fluid">
        </div>
    </div>

</div>

<script>
document.querySelectorAll('.month-item').forEach(item => {
    item.addEventListener('click', function () {
        document.getElementById('selectedMonth').innerText = this.dataset.value;
    });
});
</script>

@endsection
