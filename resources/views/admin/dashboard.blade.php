@extends('layouts.master')

@section('content')

<style>
    .progress-bar-custom {
        height: 10px;
        border-radius: 10px;
        transition: width 0.8s ease-in-out;
        position: relative;
        overflow: hidden;
    }
    .progress-bar-custom::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background-image: linear-gradient(
            -45deg,
            rgba(255, 255, 255, 0.2) 25%,
            transparent 25%,
            transparent 50%,
            rgba(255, 255, 255, 0.2) 50%,
            rgba(255, 255, 255, 0.2) 75%,
            transparent 75%,
            transparent
        );
        background-size: 50px 50px;
        animation: move 2s linear infinite;
        border-radius: 10px;
        opacity: 0;
    }
    .progress-bar-custom.animated::after {
        opacity: 1;
    }
    @keyframes move {
        0% { background-position: 0 0; }
        100% { background-position: 50px 50px; }
    }

    .progress-label { font-size: 13px; font-weight: 600; }

    .card { transition: all 0.3s ease; }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    .fade-in {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease;
    }
    .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .bi { transition: all 0.3s ease; }
    .card:hover .bi { transform: scale(1.1); }

    .table tbody tr { transition: all 0.3s ease; }
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transform: scale(1.01);
    }

    .dropdown-menu { animation: slideDown 0.3s ease; }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .counter-animate { font-variant-numeric: tabular-nums; }

    .chart-loading { position: relative; overflow: hidden; }
    .chart-loading::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        animation: loading 1.5s infinite;
    }
    @keyframes loading {
        0%   { left: -100%; }
        100% { left: 100%; }
    }

    .pulse { animation: pulse 2s infinite; }
    @keyframes pulse {
        0%   { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
        70%  { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }

    .text-primary { transition: all 0.3s ease; }
    .text-primary:hover { letter-spacing: 0.5px; }
</style>

<div class="container-fluid p-4" style="max-height: 100vh; overflow-y: auto;">

    {{-- =================== TITLE + FILTER BULAN =================== --}}
    <div class="d-flex justify-content-between align-items-center mb-3 fade-in">
    <h4 class="fw-bold">Dashboard</h4>

    @php
        $monthNames = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        $currentMonthNumber = $startDate->format('m');
        $currentYear        = $startDate->format('Y');
        $currentMonthName   = $monthNames[$currentMonthNumber] ?? 'Bulan';

        // nilai terpilih dari controller
        $selMonth = $selectedMonth ?? $currentMonthNumber;
        $selYear  = $selectedYear  ?? $currentYear;

        // range tahun (silakan sesuaikan)
        $yearNow = now()->year;
        $years   = range($yearNow - 5, $yearNow + 1);
    @endphp

    <form method="GET" action="{{ route('dashboard-admin') }}"
          class="d-flex align-items-center flex-wrap gap-2">
        <span class="text-secondary">Periode:</span>

        {{-- PILIH BULAN --}}
        <select name="bulan" class="form-select form-select-sm" style="width:auto;">
            @foreach($monthNames as $num => $name)
                <option value="{{ $num }}" {{ $num == $selMonth ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>

        {{-- PILIH TAHUN --}}
        <select name="tahun" class="form-select form-select-sm" style="width:auto;">
            @foreach($years as $y)
                <option value="{{ $y }}" {{ $y == $selYear ? 'selected' : '' }}>
                    {{ $y }}
                </option>
            @endforeach
        </select>

        <button class="btn btn-sm btn-primary" type="submit">
            Terapkan
        </button>

        <a href="{{ route('dashboard-admin') }}" class="btn btn-sm btn-outline-secondary">
            Reset
        </a>
    </form>
</div>



    {{-- =================== TOTAL PEMBAYARAN =================== --}}
    <div class="row g-3">
        <div class="col-12 col-lg-6 fade-in">
            <div class="card shadow-sm border-0 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-secondary fw-semibold">
                            Total Pembayaran Diterima ({{ $currentMonthName }} {{ $currentYear }})
                        </small>

                        <h5 class="fw-bold text-success mt-1 counter-animate">
                            {{ rupiah($totalPembayaranTerima) }}
                        </h5>
                    </div>
                    <i class="bi bi-receipt-cutoff fs-2 text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 fade-in">
            <div class="card shadow-sm border-0 p-3 pulse">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-secondary fw-semibold">
                            Total Pembayaran Terlambat ({{ $currentMonthName }} {{ $currentYear }})
                        </small>

                        <h5 class="fw-bold text-danger mt-1 counter-animate">
                            {{ rupiah($totalPembayaranTerlambat) }}
                        </h5>
                    </div>
                    <i class="bi bi-exclamation-octagon fs-2 text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- =================== MINI COUNTERS =================== --}}
<div class="row g-3 mt-3">
    @foreach($counters as $index => $c)
        <div class="col-6 col-lg-2 fade-in" style="transition-delay: {{ $index * 0.1 }}s">
            <div class="card p-3 shadow-sm border-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi {{ $c['icon'] }} fs-3 {{ $c['color'] }}"></i>
                    <div>
                        <small>{{ $c['label'] }}</small>
                        <h6 class="fw-bold counter-animate">{{ number_format($c['value'], 0, ',', '.') }}</h6>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>


    {{-- =================== CHART STATUS (STATIC IMG) =================== --}}
<div class="row mt-4 g-4">
    {{-- PIE STATUS PEMBAYARAN --}}
<div class="col-12 col-lg-6 fade-in">
    <div class="card p-3 shadow-sm border-0">
        <h6 class="fw-bold">Status Pembayaran ({{ $currentMonthName }} {{ $currentYear }})</h6>

        @if(($statusPembayaran['lunas'] ?? 0) == 0 && ($statusPembayaran['belum_lunas'] ?? 0) == 0)
            <div class="text-center text-muted py-5">
                Tidak ada data pembayaran.
            </div>
        @else
            <div class="d-flex justify-content-center">
                <div style="position: relative; width: 260px; height: 260px;">
                    <canvas id="statusPembayaranChart"></canvas>
                </div>
            </div>
        @endif
    </div>
</div>


    {{-- PIE STATUS PELANGGAN --}}
<div class="col-12 col-lg-6 fade-in">
    <div class="card p-3 shadow-sm border-0">
        <h6 class="fw-bold">Status Pelanggan</h6>

        @if(
            ($statusCounts['aktif'] ?? 0) == 0 &&
            ($statusCounts['isolir'] ?? 0) == 0 &&
            ($statusCounts['berhenti'] ?? 0) == 0 &&
            ($statusCounts['baru'] ?? 0) == 0
        )
            <div class="text-center text-muted py-5">
                Tidak ada data pelanggan.
            </div>
        @else
            <div class="d-flex justify-content-center">
                <div style="position: relative; width: 260px; height: 260px;">
                    <canvas id="statusPelangganChart"></canvas>
                </div>
            </div>
        @endif
    </div>
</div>

</div>



    {{-- =================== PROGRES SALES =================== --}}
    <div class="card p-4 shadow-sm mt-4 border-0 fade-in">
 <h6 class="fw-bold">Progres Penarikan Pembayaran Per Sales ({{ $currentMonthName }} {{ $currentYear }})</h6>

@forelse($salesProgress as $index => $s)
    <div class="row my-2 progress-item"
         data-percent="{{ $s['percent'] }}"
         data-done="{{ $s['done'] }}"
         data-total="{{ $s['total'] }}">
        <div class="col-4">
            <span class="progress-label">{{ $s['nama'] }}</span>
        </div>
        <div class="col-6">
            <div class="bg-light rounded">
                <div class="bg-primary progress-bar-custom" style="width: 0%;"></div>
            </div>
        </div>
        <div class="col-2 text-end">
            <small class="fw-bold">
                <span class="percent-text">0%</span>
                <span class="text-secondary">
                    (<span class="done-text">0</span>/{{ $s['total'] }} Tagihan)
                </span>
            </small>
        </div>
    </div>
@empty
    <p class="text-muted mt-3">Belum ada data sales.</p>
@endforelse

    </div>

    {{-- =================== TABEL PELANGGAN =================== --}}
    <div class="row mt-4 g-3">
        {{-- BELUM BAYAR --}}
        <div class="col-12 col-lg-6 fade-in">
            <div class="card p-3 shadow-sm border-0">
                <h6 class="fw-bold">Pelanggan Belum Bayar (Top 5)</h6>
                <table class="table table-sm mt-2">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Wilayah</th>
                            <th>Tagihan</th>
                            <th>Sales</th>
                            <th>Jatuh Tempo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tagihanBelumBayar as $index => $t)
                            @php
                                $pelanggan = $t->langganan->pelanggan ?? null;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $pelanggan->nama ?? '-' }}</td>
                                <td>{{ optional($pelanggan->area)->nama_area ?? '-' }}</td>
                                <td>{{ rupiah($t->total_tagihan) }}</td>
                                <td>{{ optional(optional($pelanggan->sales)->user)->name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($t->jatuh_tempo)->format('d-m-Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{-- Bisa diarahkan ke halaman detail kalau sudah ada --}}
                {{-- <a href="{{ route('tagihan.index') }}" class="text-primary fw-semibold text-decoration-none">Detail →</a> --}}
            </div>
        </div>

        {{-- SUDAH BAYAR --}}
        <div class="col-12 col-lg-6 fade-in">
            <div class="card p-3 shadow-sm border-0">
                <h6 class="fw-bold">Pelanggan Sudah Bayar (Top 5)</h6>
                <table class="table table-sm mt-2">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Wilayah</th>
                            <th>Tagihan</th>
                            <th>Sales</th>
                            <th>Jatuh Tempo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tagihanSudahBayar as $index => $t)
                            @php
                                $pelanggan = $t->langganan->pelanggan ?? null;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $pelanggan->nama ?? '-' }}</td>
                                <td>{{ optional($pelanggan->area)->nama_area ?? '-' }}</td>
                                <td>{{ rupiah($t->total_tagihan) }}</td>
                                <td>{{ optional(optional($pelanggan->sales)->user)->name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($t->jatuh_tempo)->format('d-m-Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- =================== GRAFIK BAR (MASIH GAMBAR STATIS) =================== --}}
    <div class="card p-4 shadow-sm border-0 mt-4 fade-in">
        <div class="d-flex justify-content-between">
            <h6 class="fw-bold">Pendapatan Bulanan</h6>
            <button class="btn btn-sm btn-light">Filter <i class="bi bi-chevron-down"></i></button>
        </div>
        <div class="text-center mt-3 chart-loading">
            <img src="/img/chart-bar.png" class="img-fluid"
                 onload="this.parentElement.classList.remove('chart-loading')">
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ==== DATA DARI LARAVEL ====
    const statusPembayaran = @json($statusPembayaran ?? []);
    const statusCounts     = @json($statusCounts ?? []);

    // ================================
    // PIE CHART STATUS PEMBAYARAN
    // ================================
    const ctxPembayaran = document.getElementById('statusPembayaranChart');

    if (ctxPembayaran && statusPembayaran) {
        const lunas       = Number(statusPembayaran.lunas ?? 0);
        const belumLunas  = Number(statusPembayaran.belum_lunas ?? 0);
        const totalPemb   = lunas + belumLunas;

        if (totalPemb > 0) {
            // Ada data -> render chart
            new Chart(ctxPembayaran.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: ['Lunas', 'Belum Lunas'],
                    datasets: [{
                        data: [lunas, belumLunas],
                        backgroundColor: ['#28a745', '#dc3545'], // hijau, merah
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // penting biar ikut 260x260
                    plugins: {
                        legend: { position: 'bottom' },
                    }
                }
            });
        } else {
            // Tidak ada data -> ganti canvas dengan teks
            const wrapper = ctxPembayaran.parentElement; // div 260x260
            if (wrapper) {
                wrapper.innerHTML = `
                    <div class="text-muted py-5 text-center">
                        Tidak ada data pembayaran.
                    </div>
                `;
            }
        }
    }

    // ================================
    // PIE CHART STATUS PELANGGAN
    // ================================
    const ctxPelanggan = document.getElementById('statusPelangganChart');

    if (ctxPelanggan && statusCounts) {
        const aktif    = Number(statusCounts.aktif    ?? 0);
        const isolir   = Number(statusCounts.isolir   ?? 0);
        const berhenti = Number(statusCounts.berhenti ?? 0);
        const baru     = Number(statusCounts.baru     ?? 0);
        const totalPel = aktif + isolir + berhenti + baru;

        if (totalPel > 0) {
            // Ada data -> render chart
            new Chart(ctxPelanggan.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: ['Aktif', 'Isolir', 'Berhenti', 'Baru (bulan ini)'],
                    datasets: [{
                        data: [aktif, isolir, berhenti, baru],
                        backgroundColor: [
                            '#0d6efd', // biru
                            '#ffc107', // kuning
                            '#dc3545', // merah
                            '#20c997'  // hijau tosca
                        ],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                    }
                }
            });
        } else {
            // Tidak ada data -> ganti canvas dengan teks
            const wrapper = ctxPelanggan.parentElement; // div 260x260
            if (wrapper) {
                wrapper.innerHTML = `
                    <div class="text-muted py-5 text-center">
                        Tidak ada data pelanggan.
                    </div>
                `;
            }
        }
    }

    // === FILTER BULAN: RELOAD DENGAN ?bulan=YYYY-MM ===
    document.querySelectorAll('.month-item').forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();

            const month = this.dataset.month; // "01", "02", ...
            const year  = this.dataset.year;  // "2025"

            const label = this.textContent.trim();
            const selectedLabel = document.getElementById('selectedMonth');
            if (selectedLabel) {
                selectedLabel.innerText = label;
            }

            const url = new URL(window.location.href);
            url.searchParams.set('bulan', `${year}-${month}`);
            window.location.href = url.toString();
        });
    });

    // Fade in animation
    const fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach(element => element.classList.add('visible'));

    // Animate progress bars (pakai data-* dari progress-item)
    const progressItems = document.querySelectorAll('.progress-item');
    const progressObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;

            const item        = entry.target;
            const progressBar = item.querySelector('.progress-bar-custom');
            const percentText = item.querySelector('.percent-text');
            const doneText    = item.querySelector('.done-text');

            const targetPercent = parseInt(item.dataset.percent || '0', 10);
            const targetDone    = parseInt(item.dataset.done || '0', 10);

            let currentPercent = 0;

            const animateProgress = () => {
                if (currentPercent < targetPercent) {
                    currentPercent++;

                    const currentDone = targetPercent > 0
                        ? Math.round((currentPercent / targetPercent) * targetDone)
                        : 0;

                    progressBar.style.width   = currentPercent + '%';
                    percentText.textContent   = currentPercent + '%';
                    doneText.textContent      = currentDone.toString();

                    progressBar.classList.add('animated');
                    setTimeout(animateProgress, 20);
                } else {
                    setTimeout(() => progressBar.classList.remove('animated'), 500);
                }
            };

            animateProgress();
            progressObserver.unobserve(item);
        });
    }, { threshold: 0.5 });

    progressItems.forEach(item => progressObserver.observe(item));

    // Number counting animation for counters
    const counters = document.querySelectorAll('.counter-animate');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;

            const element = entry.target;
            const text    = element.textContent;

            if (text.includes('Rp')) {
                element.style.opacity = '0';
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        element.style.transform = 'scale(1)';
                    }, 300);
                }, 200);
            } else {
                const target = parseInt(text.replace(/\./g, ''), 10) || 0;
                let current  = 0;
                const increment = target / 50;

                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        element.textContent = formatNumber(target);
                        clearInterval(timer);
                    } else {
                        element.textContent = formatNumber(Math.floor(current));
                    }
                }, 30);
            }

            counterObserver.unobserve(element);
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => counterObserver.observe(counter));

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Hover effect untuk cards (backup)
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
});
</script>
@endsection
