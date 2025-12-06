{{-- resources/views/laporan/export.blade.php --}}

@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Export Laporan Pelanggan & Pembayaran</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 25px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
        }

        th {
            background: #d9e1f2;
            font-weight: bold;
        }

        .subhead {
            background: #f1f1f1;
            font-weight: bold;
        }

        .left {
            text-align: left !important;
        }

        .right {
            text-align: right !important;
        }
    </style>
</head>
<body>

{{-- JUDUL --}}
<h3 style="text-align:center; margin-bottom:5px;">
    LAPORAN PELANGGAN & PEMBAYARAN - TAHUN {{ $tahun }}
</h3>

<p style="text-align:center; margin-top:0; margin-bottom:15px;">
    <strong>Sales:</strong> {{ $salesName }} &nbsp; | &nbsp;
    <strong>Wilayah:</strong> {{ $areaName }}
</p>
{{-- ================== TABEL UTAMA PER PELANGGAN ================== --}}
<table>
    <tr>
        <th rowspan="2">NO</th>
        <th rowspan="2">NAMA PELANGGAN</th>
        <th rowspan="2">IP</th>
        <th rowspan="2">NOMINAL</th>
        <th colspan="12">BULAN ({{ $tahun }})</th>
    </tr>
    <tr class="subhead">
        <th>JAN</th>
        <th>FEB</th>
        <th>MAR</th>
        <th>APR</th>
        <th>MEI</th>
        <th>JUN</th>
        <th>JUL</th>
        <th>AGT</th>
        <th>SEP</th>
        <th>OKT</th>
        <th>NOV</th>
        <th>DES</th>
    </tr>

    @foreach ($pelanggan as $i => $p)
        @php
            // Semua tagihan pelanggan di tahun ini (dari semua langganan)
            $semuaTagihan = $p->langganan
                ? $p->langganan
                    ->flatMap(fn ($l) => $l->tagihan->where('tahun', $tahun))
                : collect();

            // Tanggal daftar dari tabel pelanggan
            $regDate = $p->tanggal_registrasi
                ? Carbon::parse($p->tanggal_registrasi)
                : null;

            // Cari tanggal berhenti terakhir dari semua langganan yang punya tanggal_berhenti
            $stopDate = null;
            if ($p->langganan && $p->langganan->count()) {
                $stopLangganan = $p->langganan
                    ->filter(fn ($l) => !empty($l->tanggal_berhenti))
                    ->sortByDesc('tanggal_berhenti')
                    ->first();

                if ($stopLangganan) {
                    $stopDate = Carbon::parse($stopLangganan->tanggal_berhenti);
                }
            }

            // Hitung tarif (nominal) dari paket langganan terbaru
            $tarif = 0;
            if ($p->langganan && $p->langganan->count()) {
                $langAktif = $p->langganan->sortByDesc('tanggal_mulai')->first();
                if ($langAktif && $langAktif->paket) {
                    $tarif = (int) $langAktif->paket->harga_total;
                }
            }

            $bulanStatus = [];

            foreach (range(1, 12) as $b) {
                $cell = '';

                // Jika sudah berhenti di tahun ini dan bulan > bulan berhenti -> kosong
                if ($stopDate && $stopDate->year == $tahun && $b > $stopDate->month) {
                    $bulanStatus[$b] = '';
                    continue;
                }

                // "daftar" di bulan registrasi
                if ($regDate && $regDate->year == $tahun && $regDate->month == $b) {
                    $cell = 'daftar';
                }

                // "berhenti" di bulan berhenti (override status lain)
                if ($stopDate && $stopDate->year == $tahun && $stopDate->month == $b) {
                    $bulanStatus[$b] = 'berhenti';
                    continue;
                }

                // Cari tagihan bulan ini
                $tg = $semuaTagihan->first(function ($t) use ($b) {
                    return (int) $t->bulan === (int) $b;
                });

                if ($tg) {
                    $statusTagihan = strtolower($tg->status_tagihan ?? '');

                    if ($statusTagihan === 'lunas') {
                        // Di DB tidak ada tanggal_bayar di tagihan → cukup centang
                        $cell = '✓';
                    } else {
                        // belum lunas
                        $cell = '!';
                    }
                }

                $bulanStatus[$b] = $cell;
            }
        @endphp

        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="left">{{ $p->nama }}</td>
            <td>{{ $p->ip_address ?? '-' }}</td>
            <td class="right">
                {{ number_format($tarif, 0, ',', '.') }}
            </td>

            @foreach ($bulanStatus as $isi)
                <td>{{ $isi }}</td>
            @endforeach
        </tr>
    @endforeach
</table>

{{-- ================== RINGKASAN PEMASUKAN / PENGELUARAN ================== --}}
<h4 style="margin-bottom:5px;">Ringkasan Pemasukan &amp; Pengeluaran</h4>

<table>
    <tr class="subhead">
        <th>Bulan</th>
        <th>Pemasukan</th>
        <th>Pengeluaran</th>
        <th>Bersih</th>
    </tr>

    @foreach ($ringkasanBulanan as $b => $data)
        <tr>
            <td class="left">
                {{ Carbon::create($tahun, $b, 1)->translatedFormat('F') }}
            </td>
            <td class="right">
                {{ number_format($data['pemasukan'], 0, ',', '.') }}
            </td>
            <td class="right">
                {{ number_format($data['pengeluaran'], 0, ',', '.') }}
            </td>
            <td class="right">
                {{ number_format($data['bersih'], 0, ',', '.') }}
            </td>
        </tr>
    @endforeach

    <tr>
        <th class="left">TOTAL</th>
        <th class="right">
            {{ number_format(collect($ringkasanBulanan)->sum('pemasukan'), 0, ',', '.') }}
        </th>
        <th class="right">
            {{ number_format(collect($ringkasanBulanan)->sum('pengeluaran'), 0, ',', '.') }}
        </th>
        <th class="right">
            {{ number_format(collect($ringkasanBulanan)->sum('bersih'), 0, ',', '.') }}
        </th>
    </tr>
</table>

{{-- OPSIONAL: INFO TOTAL SETORAN & SELISIH --}}
@if(isset($totalSetoran, $totalBersihTahun, $selisihSetoran))
    <table>
        <tr class="subhead">
            <th class="left">Total Bersih Setahun</th>
            <th class="left">Total Setoran</th>
            <th class="left">Selisih Setoran</th>
        </tr>
        <tr>
            <td class="right">
                {{ number_format($totalBersihTahun, 0, ',', '.') }}
            </td>
            <td class="right">
                {{ number_format($totalSetoran, 0, ',', '.') }}
            </td>
            <td class="right">
                {{ number_format($selisihSetoran, 0, ',', '.') }}
            </td>
        </tr>
    </table>
@endif

</body>
</html>
