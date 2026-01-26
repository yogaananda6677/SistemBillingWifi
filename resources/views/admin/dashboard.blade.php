@extends('layouts.master')

@section('content')

{{-- =================================================================== --}}
{{-- 1. LOGIKA PHP & CSS MINIMALIS                                       --}}
{{-- =================================================================== --}}
@php
    $monthNames = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
    ];
    
    // Default Tanggal
    $dateObj = $startDate ?? now();
    $currMon = $dateObj->format('m');
    $currYear = $dateObj->format('Y');
    $currMonName = $monthNames[$currMon] ?? 'Bulan';

    // Filter
    $selMonth = $selectedMonth ?? $currMon;
    $selYear  = $selectedYear  ?? $currYear;
    $yearNow  = now()->year;
@endphp

<style>
    /* --- TEMA MINIMALIS (CLEAN & CRISP) --- */
    :root {
        --acc-gold: #fbbf24;    /* Kuning Emas */
        --acc-green: #10b981;   /* Hijau Sukses */
        --acc-red: #ef4444;     /* Merah Error */
        --bg-body: #f9fafb;     /* Abu sangat muda */
        --border: #e5e7eb;      /* Garis tipis */
        --text-main: #1f2937;   /* Hitam lembut */
        --text-sub: #6b7280;    /* Abu teks */
    }

    body { background-color: var(--bg-body); }

    /* CARD STYLE: Flat, Bordered, No Shadow */
    .card-clean {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        height: 100%;
        transition: border-color 0.2s;
    }
    .card-clean:hover {
        border-color: var(--acc-gold); /* Efek hover simpel */
    }

    /* TYPOGRAPHY */
    .label-k {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 700;
        color: var(--text-sub);
        display: block;
        margin-bottom: 8px;
    }
    .angka-besar {
        font-size: 28px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.5px;
        line-height: 1.2;
    }
    
    /* INPUT FILTER */
    .input-clean {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 14px;
        color: var(--text-main);
        cursor: pointer;
    }
    .btn-clean {
        background: var(--acc-gold);
        color: #000;
        font-weight: 600;
        border: none;
        padding: 7px 18px;
        border-radius: 8px;
        font-size: 14px;
    }

    /* ICON BULAT */
    .icon-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-bottom: 12px;
    }
    .ic-green { background: #d1fae5; color: var(--acc-green); }
    .ic-red { background: #fee2e2; color: var(--acc-red); }
    .ic-blue { background: #dbeafe; color: #3b82f6; }
    .ic-gold { background: #fef3c7; color: #d97706; }
    
    /* PROGRESS BAR */
    .progress-thin {
        height: 6px;
        background: #f3f4f6;
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        background: var(--acc-gold);
        width: 0%;
        transition: width 1s ease;
    }
</style>

<div class="container-fluid p-4">

    {{-- =================================================================== --}}
    {{-- 2. HEADER & FILTER                                                  --}}
    {{-- =================================================================== --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-end mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Dashboard</h3>
            <p class="text-muted small mb-0">Laporan periode <strong>{{ $monthNames[$selMonth] }} {{ $selYear }}</strong></p>
        </div>

        <form action="{{ route('dashboard-admin') }}" method="GET" class="d-flex gap-2 mt-3 mt-md-0">
            <select name="bulan" class="input-clean">
                @foreach($monthNames as $num => $name)
                    <option value="{{ $num }}" {{ $num == $selMonth ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <select name="tahun" class="input-clean">
                @foreach(range($yearNow - 2, $yearNow + 1) as $y)
                    <option value="{{ $y }}" {{ $y == $selYear ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-clean">Filter</button>
        </form>
    </div>

    {{-- =================================================================== --}}
    {{-- 3. KARTU KEUANGAN (MONEY STATS)                                     --}}
    {{-- =================================================================== --}}
    <div class="row g-3 mb-4">
        {{-- Uang Masuk --}}
        <div class="col-md-6">
            <div class="card-clean d-flex align-items-center justify-content-between">
                <div>
                    <span class="label-k">
                        PEMBAYARAN LUNAS ({{ $currentMonthName }} {{ $currentYear }})
                    </span>

                    <div class="angka-besar text-success counter-anim">
                        {{ rupiah($totalPembayaranTerima) }}
                    </div>
                </div>
                <div class="icon-circle ic-green"><i class="bi bi-wallet2"></i></div>
            </div>
        </div>
        
        {{-- Uang Macet --}}
        <div class="col-md-6">
            <div class="card-clean d-flex align-items-center justify-content-between">
                <div>
                    <span class="label-k">
                        PEMBAYARAN TERLAMBAT ({{ $currentMonthName }} {{ $currentYear }})
                    </span>
                    <div class="angka-besar text-danger counter-anim">
                        {{ rupiah($totalPembayaranTerlambat) }}
                    </div>
                </div>
                <div class="icon-circle ic-red"><i class="bi bi-exclamation-lg"></i></div>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- 4. KARTU STATISTIK PELANGGAN (FIXED: 5 KOLOM 1 BARIS)               --}}
    {{-- =================================================================== --}}
    {{-- Menggunakan row-cols-md-5 untuk membagi layar menjadi 5 bagian rata --}}
    <div class="row row-cols-2 row-cols-md-5 g-3 mb-4">
        @foreach($counters as $c)
            @php
                $icColor = 'ic-blue';
                if(str_contains($c['color'], 'success')) $icColor = 'ic-green';
                if(str_contains($c['color'], 'warning')) $icColor = 'ic-gold';
                if(str_contains($c['color'], 'danger'))  $icColor = 'ic-red';
            @endphp
            <div class="col">
                <div class="card-clean p-3 h-100">
                    <div class="icon-circle {{ $icColor }}" style="width: 36px; height: 36px; font-size: 16px;">
                        <i class="bi {{ $c['icon'] }}"></i>
                    </div>
                    <div class="mt-2">
                        <div class="fs-4 fw-bold text-dark counter-anim">{{ $c['value'] }}</div>
                        <div class="text-muted small text-truncate" style="font-size: 12px;" title="{{ $c['label'] }}">
                            {{ $c['label'] }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- =================================================================== --}}
    {{-- 5. GRAFIK & LIST (2 KOLOM)                                          --}}
    {{-- =================================================================== --}}
    <div class="row g-3">
        
        {{-- KIRI: Grafik & Progress --}}
        <div class="col-lg-8">
            <div class="row g-3">
                {{-- Grafik Pembayaran --}}
                <div class="col-md-6">
                    <div class="card-clean text-center">
                        <span class="label-k mb-3">STATUS PEMBAYARAN</span>
                        <div style="height: 180px; display: flex; justify-content: center;">
                            @if(($statusPembayaran['lunas'] ?? 0) == 0 && ($statusPembayaran['belum_lunas'] ?? 0) == 0)
                                <span class="text-muted align-self-center small">Tidak ada data.</span>
                            @else
                                <canvas id="chartBayar"></canvas>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Grafik Pelanggan --}}
                <div class="col-md-6">
                    <div class="card-clean text-center">
                        <span class="label-k mb-3">STATUS PELANGGAN</span>
                        <div style="height: 180px; display: flex; justify-content: center;">
                            <canvas id="chartPelanggan"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Progress Sales --}}
                <div class="col-12">
                    <div class="card-clean">
                        <span class="label-k mb-4">PERFORMA SALES (PENAGIHAN)</span>
                        
                        @forelse($salesProgress as $s)
                            <div class="mb-3 item-progress" data-percent="{{ $s['percent'] }}">
                                <div class="d-flex justify-content-between mb-1" style="font-size: 13px;">
                                    <span class="fw-bold">{{ $s['nama'] }}</span>
                                    <span class="text-muted">{{ $s['done'] }} / {{ $s['total'] }}</span>
                                </div>
                                <div class="progress-thin">
                                    <div class="progress-fill"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small">Belum ada data sales.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: List Tagihan (Top 5) --}}
        <div class="col-lg-4">
            <div class="card-clean h-100">
                <span class="label-k mb-3 text-danger">BELUM BAYAR TERBESAR</span>
                
                <div class="d-flex flex-column gap-3">
                    @forelse($tagihanBelumBayar as $t)
                        @php $p = $t->langganan->pelanggan ?? null; @endphp
                        <div class="pb-2 border-bottom">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold" style="font-size: 13px;">{{ $p->nama ?? '-' }}</span>
                                <span class="text-danger fw-bold" style="font-size: 13px;">{{ rupiah($t->total_tagihan) }}</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                                <span>{{ optional($p->area)->nama_area }}</span>
                                <span>Jatuh Tempo: {{ \Carbon\Carbon::parse($t->jatuh_tempo)->format('d/m') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">Tidak ada tunggakan.</div>
                    @endforelse
                </div>

                <div class="mt-4 pt-2">
                    <a href="{{ route('tagihan.index', ['status' => 'belum_lunas']) }}" 
                       class="btn btn-light btn-sm w-100 text-muted" style="font-size: 12px;">Lihat Semua</a>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- =================================================================== --}}
{{-- 6. JAVASCRIPT LOGIC (Chart & Animation)                             --}}
{{-- =================================================================== --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. CHART: Status Pembayaran
    const dataBayar = @json($statusPembayaran ?? []);
    const ctxBayar = document.getElementById('chartBayar');
    if (ctxBayar && (dataBayar.lunas || dataBayar.belum_lunas)) {
        new Chart(ctxBayar, {
            type: 'doughnut',
            data: {
                labels: ['Lunas', 'Belum'],
                datasets: [{
                    data: [dataBayar.lunas, dataBayar.belum_lunas],
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 11 } } } },
                cutout: '75%'
            }
        });
    }

    // 2. CHART: Status Pelanggan
    const dataPel = @json($statusCounts ?? []);
    const ctxPel = document.getElementById('chartPelanggan');
    if (ctxPel) {
        new Chart(ctxPel, {
            type: 'doughnut',
            data: {
                labels: ['Aktif', 'Isolir', 'Stop', 'Baru'],
                datasets: [{
                    data: [dataPel.aktif, dataPel.isolir, dataPel.berhenti, dataPel.baru],
                    backgroundColor: ['#3b82f6', '#f59e0b', '#ef4444', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }, // Sembunyikan legend biar bersih
                cutout: '75%'
            }
        });
    }

    // 3. ANIMASI ANGKA (Counter)
    document.querySelectorAll('.counter-anim').forEach(el => {
        const txt = el.innerText.replace(/[^0-9]/g, '');
        const target = parseInt(txt) || 0;
        if(target === 0) return;
        
        let start = 0;
        const duration = 800;
        const step = 20;
        const inc = target / (duration / step);

        const timer = setInterval(() => {
            start += inc;
            if(start >= target) {
                el.innerText = el.innerText.includes('Rp') 
                    ? 'Rp ' + target.toLocaleString('id-ID') 
                    : target.toLocaleString('id-ID');
                clearInterval(timer);
            } else {
                el.innerText = el.innerText.includes('Rp') 
                    ? 'Rp ' + Math.floor(start).toLocaleString('id-ID') 
                    : Math.floor(start).toLocaleString('id-ID');
            }
        }, step);
    });

    // 4. ANIMASI PROGRESS BAR
    document.querySelectorAll('.item-progress').forEach(el => {
        const pct = el.dataset.percent + '%';
        const bar = el.querySelector('.progress-fill');
        setTimeout(() => { bar.style.width = pct; }, 300);
    });
});
</script>
@endsection