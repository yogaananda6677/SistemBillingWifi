@extends('seles2.layout.master')
@section('title', 'Riwayat Setoran')

@section('content')
@php
    use Carbon\Carbon;

    $setorans    = $setorans ?? collect();
    $allocDetail = $allocDetail ?? [];
@endphp

<style>
    .setoran-page {
        background: #f1f3f6;
        min-height: 100vh;
        padding: 12px 0 80px 0;
    }

    .setoran-header {
        background: #ffffff;
        padding: 10px 16px;
        margin-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .setoran-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.05rem;
    }

    .setoran-list {
        margin: 0 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .setoran-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 14px 16px;
        box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    }

    .setoran-top {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
    }

    .setoran-label {
        font-size: 0.8rem;
        color: #6b7280;
    }

    .setoran-nominal {
        font-size: 1rem;
        font-weight: 700;
        color: #16a34a;
    }

    .setoran-meta {
        font-size: 0.8rem;
        color: #6b7280;
    }

    .setoran-admin {
        font-size: 0.8rem;
        color: #111827;
        font-weight: 500;
    }

    .alloc-list {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid #e5e7eb;
    }

    .alloc-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 4px;
        font-size: 0.8rem;
    }

    .alloc-periode {
        color: #4b5563;
    }

    .alloc-nominal {
        font-weight: 600;
    }

    .alloc-badge-lebih {
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 999px;
        background: #e0f2fe;
        color: #0369a1;
        margin-left: 4px;
    }

    .note-box {
        margin: 16px;
        font-size: 0.8rem;
        color: #6b7280;
    }

    .text-danger { color: #ef4444 !important; }
    .text-success { color: #16a34a !important; }
</style>

<div class="setoran-page">

    <div class="setoran-header">
        <h5>Riwayat Setoran</h5>
        {{-- jika mau, bisa tambah tombol filter di sini nanti --}}
    </div>

    @if($setorans->isEmpty())
        <div class="note-box">
            Belum ada setoran yang tercatat untuk akun Anda.
        </div>
    @else
        <div class="setoran-list">
            @foreach($setorans as $st)
                @php
                    $tanggal = $st->tanggal_setoran
                        ? Carbon::parse($st->tanggal_setoran)->translatedFormat('d M Y, H:i')
                        : '-';

                    $detail = $allocDetail[$st->id_setoran] ?? [];
                @endphp

                <div class="setoran-card">
                    {{-- Atas: nominal & tanggal --}}
                    <div class="setoran-top">
                        <div>
                            <div class="setoran-label">Nominal Setor</div>
                            <div class="setoran-nominal">
                                Rp {{ number_format($st->nominal, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="setoran-label">Tanggal</div>
                            <div class="setoran-meta">
                                {{ $tanggal }}
                            </div>
                        </div>
                    </div>

                    {{-- Admin & catatan --}}
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="setoran-label">Diterima oleh</div>
                        <div class="setoran-admin">
                            {{ $st->nama_admin ?? '-' }}
                        </div>
                    </div>

                    @if($st->catatan)
                        <div class="setoran-meta mb-1">
                            Catatan: {{ $st->catatan }}
                        </div>
                    @endif

                    {{-- Rincian alokasi --}}
                    <div class="alloc-list">
                        <div class="setoran-label mb-1">
                            Rincian alokasi per bulan:
                        </div>

                        @if(empty($detail))
                            <div class="alloc-row">
                                <span class="alloc-periode">Tidak ada rincian alokasi.</span>
                            </div>
                        @else
                            @foreach($detail as $al)
                                @php
                                    $periodeText = \Carbon\Carbon::createFromFormat('Y-m-d', $al['periode'].'-01')
                                        ->translatedFormat('F Y');
                                @endphp
                                <div class="alloc-row">
                                    <div class="alloc-periode">
                                        {{ $periodeText }}
                                        @if(!empty($al['lebih']))
                                            <span class="alloc-badge-lebih">Kelebihan</span>
                                        @endif
                                    </div>
                                    <div class="alloc-nominal">
                                        Rp {{ number_format($al['nominal'], 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    @endif

    <div class="note-box">
        <strong>Catatan perhitungan:</strong><br>
        Setiap setoran dialokasikan mulai dari bulan dengan kewajiban tertua yang masih kurang.
        Contoh: jika bulan Januari kurang Rp 50.000 lalu bulan Februari setor Rp 100.000,
        maka sistem menganggap Rp 50.000 menutup kekurangan Januari dan Rp 50.000 untuk Februari.
        Kelebihan setoran setelah semua kewajiban tertutup akan ditandai sebagai <span class="text-success">kelebihan</span>
        di bulan setoran.
    </div>

</div>
@endsection
