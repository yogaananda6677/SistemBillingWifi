@extends('layouts.master')

@section('content')

<style>
    .progress-bar-custom {
        height: 10px;
        border-radius: 10px;
        transition: width 0.8s ease-in-out;
    }
    .progress-label {
        font-size: 13px;
        font-weight: 600;
    }
    
    /* Animasi untuk cards */
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    
    /* Animasi untuk progress bars */
    .progress-bar-custom {
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
    
    @keyframes move {
        0% {
            background-position: 0 0;
        }
        100% {
            background-position: 50px 50px;
        }
    }
    
    .progress-bar-custom.animated::after {
        opacity: 1;
    }
    
    /* Animasi fade in untuk elements */
    .fade-in {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease;
    }
    
    .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Animasi untuk icons */
    .bi {
        transition: all 0.3s ease;
    }
    
    .card:hover .bi {
        transform: scale(1.1);
    }
    
    /* Animasi untuk table rows */
    .table tbody tr {
        transition: all 0.3s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transform: scale(1.01);
    }
    
    /* Animasi untuk dropdown - SIMPLE FIX */
    .dropdown-menu {
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Animasi untuk numbers */
    .counter-animate {
        font-variant-numeric: tabular-nums;
    }
    
    /* Loading animation untuk charts */
    .chart-loading {
        position: relative;
        overflow: hidden;
    }
    
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
        0% {
            left: -100%;
        }
        100% {
            left: 100%;
        }
    }
    
    /* Pulse animation untuk important items */
    .pulse {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }
    
    /* Hover effect untuk links */
    .text-primary {
        transition: all 0.3s ease;
    }
    
    .text-primary:hover {
        letter-spacing: 0.5px;
    }
</style>

<div class="container-fluid p-4" style="max-height: 100vh; overflow-y: auto;">

    {{-- =================== TITLE =================== --}}
    <div class="d-flex justify-content-between align-items-center mb-3 fade-in">
        <h4 class="fw-bold">Dashboard</h4>
        <div class="dropdown">
            <span class="text-secondary">Default Month:</span>
            <a class="dropdown-toggle text-secondary text-decoration-none fw-bold" href="#" role="button" 
               data-bs-toggle="dropdown" aria-expanded="false">
                <strong id="selectedMonth">Januari</strong>
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item month-item" href="#" data-value="Januari">Januari</a></li>
                <li><a class="dropdown-item month-item" href="#" data-value="Februari">Februari</a></li>
                <li><a class="dropdown-item month-item" href="#" data-value="Maret">Maret</a></li>
                <li><a class="dropdown-item month-item" href="#" data-value="April">April</a></li>
                <li><a class="dropdown-item month-item" href="#" data-value="Mei">Mei</a></li>
                <li><a class="dropdown-item month-item" href="#" data-value="Juni">Juni</a></li>
                <li><a class="dropdown-item month-item" href="#" data-value="Juli">Juli</a></li>
                <li><a class="dropdown-item month-item" href="#" data-value="Agustus">Agustus</a></li>
                <li><a class="dropdown-item month-item" href="#" data-value="September">September</a></li>
                <li><a class="dropdown-item month-item" href="#" data-value="Oktober">Oktober</a></li>
                <li><a class="dropdown-item month-item" href="#" data-value="November">November</a></li>
                <li><a class="dropdown-item month-item" href="#" data-value="Desember">Desember</a></li>
            </ul>
        </div>
    </div>

    {{-- =================== TOTAL PEMBAYARAN =================== --}}
    <div class="row g-3">
        <div class="col-12 col-lg-6 fade-in">
            <div class="card shadow-sm border-0 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-secondary fw-semibold">Total Pembayaran Diterima</small>
                        <h5 class="fw-bold text-success mt-1 counter-animate">{{ rupiah($totalPembayaranTerima) }}</h5>
                    </div>
                    <i class="bi bi-receipt-cutoff fs-2 text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 fade-in">
            <div class="card shadow-sm border-0 p-3 pulse">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-secondary fw-semibold">Total Pembayaran Terlambat</small>
                        <h5 class="fw-bold text-danger mt-1 counter-animate">{{ rupiah($totalPembayaranTerlambat) }}</h5>
                    </div>
                    <i class="bi bi-exclamation-octagon fs-2 text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- =================== MINI COUNTERS =================== --}}
    <div class="row g-3 mt-3">
        @foreach($counters as $index => $c)
        <div class="col-6 col-lg-3 fade-in" style="transition-delay: {{ $index * 0.1 }}s">
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

    {{-- =================== CHART STATUS =================== --}}
    <div class="row mt-4 g-4">
        <div class="col-12 col-lg-6 fade-in">
            <div class="card p-3 shadow-sm border-0 chart-loading">
                <h6 class="fw-bold">Status Pembayaran</h6>
                <div class="text-center">
                    <img src="/img/chart1.png" class="img-fluid" style="max-width: 260px;" onload="this.parentElement.parentElement.classList.remove('chart-loading')">
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 fade-in">
            <div class="card p-3 shadow-sm border-0 chart-loading">
                <h6 class="fw-bold">Status Pelanggan</h6>
                <div class="text-center">
                    <img src="/img/chart2.png" class="img-fluid" style="max-width: 260px;" onload="this.parentElement.parentElement.classList.remove('chart-loading')">
                </div>
            </div>
        </div>
    </div>

    {{-- =================== PROGRES SALES =================== --}}
    <div class="card p-4 shadow-sm mt-4 border-0 fade-in">
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

        @foreach($sales as $index => $s)
        <div class="row my-2 progress-item">
            <div class="col-4">
                <span class="progress-label">{{ $s['nama'] }}</span>
            </div>
            <div class="col-6">
                <div class="bg-light rounded">
                    <div class="bg-primary progress-bar-custom" 
                         data-percent="{{ $s['percent'] }}"
                         style="width: 0%;">
                    </div>
                </div>
            </div>
            <div class="col-2 text-end">
                <small class="fw-bold">
                    <span class="percent-text">0%</span>
                    <span class="text-secondary">
                        (<span class="done-text">0</span>/{{ $s['total'] }} Pelanggan)
                    </span>
                </small>
            </div>
        </div>
        @endforeach
    </div>

    {{-- =================== TABEL PELANGGAN =================== --}}
    <div class="row mt-4 g-3">
        {{-- BELUM BAYAR --}}
        <div class="col-12 col-lg-6 fade-in">
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
                <a href="#" class="text-primary fw-semibold text-decoration-none">Detail →</a>
            </div>
        </div>

        {{-- SUDAH BAYAR --}}
        <div class="col-12 col-lg-6 fade-in">
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
                <a href="#" class="text-primary fw-semibold text-decoration-none">Detail →</a>
            </div>
        </div>
    </div>

    {{-- =================== GRAFIK BAR =================== --}}
    <div class="card p-4 shadow-sm border-0 mt-4 fade-in">
        <div class="d-flex justify-content-between">
            <h6 class="fw-bold">Pendapatan Bulanan</h6>
            <button class="btn btn-sm btn-light">Filter <i class="bi bi-chevron-down"></i></button>
        </div>
        <div class="text-center mt-3 chart-loading">
            <img src="/img/chart-bar.png" class="img-fluid" onload="this.parentElement.classList.remove('chart-loading')">
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Month dropdown functionality - SIMPLE FIX
    document.querySelectorAll('.month-item').forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('selectedMonth').innerText = this.dataset.value;
        });
    });

    // Fade in animation for elements
    const fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach(element => {
        element.classList.add('visible');
    });

    // Animate progress bars
    const progressItems = document.querySelectorAll('.progress-item');
    
    const progressObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressBar = entry.target.querySelector('.progress-bar-custom');
                const percentText = entry.target.querySelector('.percent-text');
                const doneText = entry.target.querySelector('.done-text');
                const targetPercent = progressBar.getAttribute('data-percent');
                const targetDone = progressBar.parentElement.nextElementSibling.querySelector('.text-secondary').textContent.match(/\d+/g)[0];
                
                let currentPercent = 0;
                let currentDone = 0;
                
                const animateProgress = () => {
                    if (currentPercent < targetPercent) {
                        currentPercent++;
                        currentDone = Math.round((currentPercent / targetPercent) * targetDone);
                        
                        progressBar.style.width = currentPercent + '%';
                        percentText.textContent = currentPercent + '%';
                        doneText.textContent = currentDone;
                        
                        // Add shimmer effect when animating
                        progressBar.classList.add('animated');
                        
                        setTimeout(animateProgress, 20);
                    } else {
                        // Remove shimmer effect after animation completes
                        setTimeout(() => {
                            progressBar.classList.remove('animated');
                        }, 500);
                    }
                };
                
                animateProgress();
                progressObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    progressItems.forEach(item => {
        progressObserver.observe(item);
    });

    // Number counting animation for counters
    const counters = document.querySelectorAll('.counter-animate');
    
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const text = element.textContent;
                
                // Check if it's a currency value
                if (text.includes('Rp')) {
                    // For currency values, we'll just add a subtle effect
                    element.style.opacity = '0';
                    setTimeout(() => {
                        element.style.opacity = '1';
                        element.style.transform = 'scale(1.1)';
                        setTimeout(() => {
                            element.style.transform = 'scale(1)';
                        }, 300);
                    }, 200);
                } else {
                    // For regular numbers, do counting animation
                    const target = parseInt(text.replace(/\./g, ''));
                    let current = 0;
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
                
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => {
        counterObserver.observe(counter);
    });

    // Helper function to format numbers with dots
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Hover effect for cards
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