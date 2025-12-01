@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">
    <h5 class="fw-bold mb-4 text-secondary">Detail Pelanggan</h5>

    {{-- CARD DETAIL PELANGGAN --}}
    <div class="card shadow-sm border-0 p-4 mb-4" style="border-radius: 14px;">
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Nama</div>
            <div class="col-md-8">{{ $pelanggan->nama }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">NIK</div>
            <div class="col-md-8">{{ $pelanggan->nik }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Alamat</div>
            <div class="col-md-8">{{ $pelanggan->alamat }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">No. HP</div>
            <div class="col-md-8">{{ $pelanggan->nomor_hp }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">IP Address</div>
            <div class="col-md-8">{{ $pelanggan->ip_address }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Tanggal Registrasi</div>
            <div class="col-md-8">{{ $pelanggan->tanggal_registrasi->format('d-m-Y') }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Status</div>
            <div class="col-md-8">
                @php
                    $status = $pelanggan->status_pelanggan_efektif;
                @endphp

                @if ($status == 'baru')
                    <span class="badge bg-warning text-dark">Baru</span>
                @elseif ($status == 'aktif')
                    <span class="badge bg-success">Aktif</span>
                @elseif ($status == 'isolir')
                    <span class="badge bg-secondary">Isolir</span>
                @else
                    <span class="badge bg-danger">Berhenti</span>
                @endif
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Area</div>
            <div class="col-md-8">{{ $pelanggan->area->nama_area ?? '-' }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Sales</div>
            <div class="col-md-8">{{ $pelanggan->sales->user->name ?? '-' }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Paket Terakhir</div>
            <div class="col-md-8">
                {{ $pelanggan->langganan->last()->paket->nama_paket ?? '-' }}
                - {{ $pelanggan->langganan->last()->paket->kecepatan ?? '-' }} Mbps
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="{{ request('from') === 'status' ? route('pelanggan.status', ['status' => request('status')]) : route('pelanggan.index') }}"
               class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>
{{-- CARD RIWAYAT PEMBAYARAN --}}
<div class="card shadow-sm border-0 p-4" style="border-radius: 14px;">
    <h6 class="fw-bold mb-3">Riwayat Pembayaran</h6>

    @php
        use Carbon\Carbon;

        // Flatten semua payment_item (per bulan) dari seluruh pembayaran pelanggan ini
        $rows = collect();

        foreach ($riwayatPembayaran as $pay) {
            foreach ($pay->items as $item) {
                $tagihan   = $item->tagihan;
                $langganan = $tagihan?->langganan;
                $paket     = $langganan?->paket;

                if (!$tagihan) {
                    continue;
                }

                $rows->push([
                    'pay'      => $pay,
                    'item'     => $item,
                    'tagihan'  => $tagihan,
                    'paket'    => $paket,
                    'tahun'    => (int) $tagihan->tahun,
                    'bulan'    => (int) $tagihan->bulan,
                ]);
            }
        }

        // Urutkan berdasarkan Bulan Tagihan (tahun*100+bulan) dari yang paling baru
        $rows = $rows->sortByDesc(function ($r) {
            return $r['tahun'] * 100 + $r['bulan'];
        })->values();
    @endphp

    @if($rows->isEmpty())
        <p class="text-muted mb-0">Belum ada pembayaran untuk pelanggan ini.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Tanggal Bayar</th>
                        <th>Bulan Tagihan</th>
                        <th>Paket</th>
                        <th>Sumber</th>
                        <th>No. Pembayaran</th>
                        <th>Nominal Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = 1;
                        $prevYear  = null;
                        $prevMonth = null;
                    @endphp

                    @foreach($rows as $row)
                        @php
                            $pay     = $row['pay'];
                            $item    = $row['item'];
                            $tagihan = $row['tagihan'];
                            $paket   = $row['paket'];
                            $tahun   = $row['tahun'];
                            $bulan   = $row['bulan'];

                            $bulanTahun = Carbon::create($tahun, $bulan, 1)
                                ->translatedFormat('F Y');

                            // Sumber (Admin / Sales)
                            $salesName = $pay->sales?->user?->name ?? $pelanggan->sales?->user?->name;
                            if (is_null($pay->id_sales)) {
                                $sumberText = 'Admin';
                                $badgeClass = 'bg-secondary';
                            } else {
                                $sumberText = 'Sales' . ($salesName ? ' - ' . $salesName : '');
                                $badgeClass = 'bg-info';
                            }

                            // ====== SISIPIN ROW "TIDAK ADA TAGIHAN" JIKA ADA GAP BULAN ======
                            if (!is_null($prevYear)) {
                                // prev = lebih baru, current = lebih lama (karena sorted DESC)
                                $prevDate = Carbon::create($prevYear, $prevMonth, 1);
                                $currDate = Carbon::create($tahun, $bulan, 1);

                                // Mulai dari bulan sebelum prev, mundur sampai > current
                                $temp = $prevDate->copy()->subMonth();

                                while ($temp->gt($currDate)) {
                                    @endphp
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>-</td>
                                            <td>
                                                {{ $temp->translatedFormat('F Y') }}<br>
                                                <small class="text-danger">Tidak ada tagihan bulan ini.</small>
                                            </td>
                                            <td>-</td>
                                            <td>
                                                <span class="badge bg-light text-muted">-</span>
                                            </td>
                                            <td>-</td>
                                            <td>Rp 0</td>
                                        </tr>
                                    @php
                                    $temp->subMonth();
                                }
                            }

                            // update "previous" ke current
                            $prevYear  = $tahun;
                            $prevMonth = $bulan;
                        @endphp

                        {{-- ROW TAGIHAN YANG BENAR-BENAR LUNAS / ADA PEMBAYARAN --}}
                        <tr>
                            <td>{{ $no++ }}</td>

                            <td>{{ optional($pay->tanggal_bayar)->format('d/m/Y H:i') }}</td>

                            <td>{{ $bulanTahun }}</td>

                            <td>{{ $paket->nama_paket ?? '-' }}</td>

                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    {{ $sumberText }}
                                </span>
                            </td>

                            <td>
                                <strong>{{ $pay->no_pembayaran }}</strong><br>
                                <small class="text-muted">ID: {{ $pay->id_pembayaran }}</small>
                            </td>

                            <td>
                                Rp {{ number_format($item->nominal_bayar, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- pagination tetap boleh dipakai, masih dari Pembayaran --}}
        <div class="mt-3">
            {{ $riwayatPembayaran->links() }}
        </div>
    @endif
</div>


@endsection
