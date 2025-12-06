<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Carbon\Carbon;

class RekapKeuanganBulananExport implements FromArray
{
    use Exportable;

    protected int $tahun;
    protected int $bulan;
    protected Collection $assignments;

    public function __construct(int $tahun, int $bulan, Collection $assignments)
    {
        $this->tahun       = $tahun;
        $this->bulan       = $bulan;
        $this->assignments = $assignments;
    }

    public function array(): array
    {
        $rows = [];

        $namaBulan = Carbon::create($this->tahun, $this->bulan, 1)->translatedFormat('F');

        // ====== JUDUL ======
        $rows[] = ["REKAP KEUANGAN BULAN {$namaBulan} {$this->tahun}"];
        $rows[] = ['']; // spacer

        // ======================================================
        // 1) REKAP PER SALES - WILAYAH
        // ======================================================
        $rows[] = ['REKAP PER SALES - WILAYAH'];
        $rows[] = [
            'Sales',
            'Wilayah',
            'Pendapatan',
            'Komisi',
            'Pengeluaran',
            'Total Bersih',
            'Setoran',
            'Selisih (Setoran - Bersih)',
            'Keterangan', // Kurang setor / Lebih setor / Pas
        ];

        foreach ($this->assignments as $asg) {
            $idSales = $asg->id_sales;
            $idArea  = $asg->id_area;

            // Pendapatan
            $pendapatan = DB::table('pembayaran as p')
                ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->whereYear('p.tanggal_bayar', $this->tahun)
                ->whereMonth('p.tanggal_bayar', $this->bulan)
                ->where('p.id_sales', $idSales)
                ->where('pl.id_area', $idArea)
                ->sum('p.nominal');

            // Komisi
            $komisi = DB::table('transaksi_komisi as tk')
                ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
                ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->where('tk.id_sales', $idSales)
                ->where('pl.id_area', $idArea)
                ->whereYear('p.tanggal_bayar', $this->tahun)
                ->whereMonth('p.tanggal_bayar', $this->bulan)
                ->sum('tk.nominal_komisi');

            // Pengeluaran
            $pengeluaran = DB::table('pengeluaran as pg')
                ->where('pg.id_sales', $idSales)
                ->where('pg.id_area', $idArea)
                ->where('pg.status_approve', 'approved')
                ->whereYear('pg.tanggal_approve', $this->tahun)
                ->whereMonth('pg.tanggal_approve', $this->bulan)
                ->sum('pg.nominal');

            // Setoran
            $setoran = DB::table('setoran as st')
                ->where('st.id_sales', $idSales)
                ->where('st.id_area', $idArea)
                ->whereYear('st.tanggal_setoran', $this->tahun)
                ->whereMonth('st.tanggal_setoran', $this->bulan)
                ->sum('st.nominal');

            $totalBersih = $pendapatan - $komisi - $pengeluaran;
            $selisih     = $setoran - $totalBersih;

            if ($selisih > 0) {
                $ket = 'Lebih setor';
            } elseif ($selisih < 0) {
                $ket = 'Kurang setor';
            } else {
                $ket = 'Pas';
            }

            $rows[] = [
                $asg->nama_sales,
                $asg->nama_area,
                (int) $pendapatan,
                (int) $komisi,
                (int) $pengeluaran,
                (int) $totalBersih,
                (int) $setoran,
                (int) $selisih,
                $ket,
            ];
        }

        // Spacer
        $rows[] = [''];
        $rows[] = [''];

        // ======================================================
        // 2) DAFTAR PENGELUARAN
        // ======================================================
        $rows[] = ['DAFTAR PENGELUARAN'];
        $rows[] = [
            'Tanggal Approve',
            'Sales',
            'Wilayah',
            'Nama Pengeluaran',
            'Nominal',
            'Catatan',
        ];

        $salesIds = $this->assignments->pluck('id_sales')->unique()->values()->all();
        $areaIds  = $this->assignments->pluck('id_area')->unique()->values()->all();

        $pengeluaranRows = DB::table('pengeluaran as pg')
            ->join('sales as s', 's.id_sales', '=', 'pg.id_sales')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('area as a', 'a.id_area', '=', 'pg.id_area')
            ->select(
                'pg.tanggal_approve',
                'u.name as nama_sales',
                'a.nama_area',
                'pg.nama_pengeluaran',
                'pg.nominal',
                'pg.catatan'
            )
            ->where('pg.status_approve', 'approved')
            ->whereYear('pg.tanggal_approve', $this->tahun)
            ->whereMonth('pg.tanggal_approve', $this->bulan)
            ->whereIn('pg.id_sales', $salesIds)
            ->whereIn('pg.id_area', $areaIds)
            ->orderBy('pg.tanggal_approve')
            ->get();

        foreach ($pengeluaranRows as $row) {
            $rows[] = [
                $row->tanggal_approve,
                $row->nama_sales,
                $row->nama_area,
                $row->nama_pengeluaran,
                (int) $row->nominal,
                $row->catatan,
            ];
        }

        // Spacer
        $rows[] = [''];
        $rows[] = [''];

        // ======================================================
        // 3) DAFTAR SETORAN
        // ======================================================
        $rows[] = ['DAFTAR SETORAN'];
        $rows[] = [
            'Tanggal Setoran',
            'Sales',
            'Wilayah',
            'Nominal',
            'Catatan',
        ];

        $setoranRows = DB::table('setoran as st')
            ->join('sales as s', 's.id_sales', '=', 'st.id_sales')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('area as a', 'a.id_area', '=', 'st.id_area')
            ->select(
                'st.tanggal_setoran',
                'u.name as nama_sales',
                'a.nama_area',
                'st.nominal',
                'st.catatan'
            )
            ->whereYear('st.tanggal_setoran', $this->tahun)
            ->whereMonth('st.tanggal_setoran', $this->bulan)
            ->whereIn('st.id_sales', $salesIds)
            ->whereIn('st.id_area', $areaIds)
            ->orderBy('st.tanggal_setoran')
            ->get();

        foreach ($setoranRows as $row) {
            $rows[] = [
                $row->tanggal_setoran,
                $row->nama_sales,
                $row->nama_area,
                (int) $row->nominal,
                $row->catatan,
            ];
        }

        return $rows;
    }
}
