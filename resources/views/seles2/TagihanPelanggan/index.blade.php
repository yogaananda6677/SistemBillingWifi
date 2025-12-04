@extends('seles2.layout.master')

@section('content')
    <div class="container mt-2">

        <h5 class="text-center fw-bold mb-3" style="font-size:16px;">Tagihan Pelanggan</h5>

        @php
            $tagihans = [
                [
                    'nama' => 'Yoga Ananda',
                    'no_hp' => '0812 •••• ••••',
                    'status' => 'Belum Lunas',
                    'tanggal' => '01 Dec 2025',
                    'paket' => 'Premium 30 Mbps',
                    'harga' => '150.000',
                ],
                [
                    'nama' => 'Salsa Putri',
                    'no_hp' => '0898 •••• ••••',
                    'status' => 'Lunas',
                    'tanggal' => '28 Nov 2025',
                    'paket' => 'Basic 10 Mbps',
                    'harga' => '100.000',
                ],
            ];
        @endphp

        @foreach ($tagihans as $item)
            <div class="card mb-2 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-2">

                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold" style="font-size:14px;">{{ $item['nama'] }}</h6>

                        <span
                            class="badge
                    {{ $item['status'] == 'Lunas' ? 'bg-success' : 'bg-danger' }}
                    px-2 py-1"
                            style="font-size:10px; border-radius:6px;">
                            {{ $item['status'] }}
                        </span>
                    </div>

                    <div class="mt-1" style="font-size:11px;">
                        <div class="text-muted">📞 {{ $item['no_hp'] }}</div>
                        <div class="text-muted">📦 {{ $item['paket'] }}</div>
                        <div class="text-muted">📅 {{ $item['tanggal'] }}</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="fw-bold" style="font-size:13px;">Rp {{ $item['harga'] }}</span>

                        @if ($item['status'] != 'Lunas')
                            <button class="btn btn-dark btn-sm px-2 py-1" style="font-size:11px;">
                                Bayar
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        @endforeach

    </div>
@endsection
