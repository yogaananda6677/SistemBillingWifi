<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapSalesAreaSummarySheet implements FromArray, WithTitle
{
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

        // Header
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

        return $rows;
    }

    public function title(): string
    {
        return 'Rekap Sales-Wilayah';
    }
}
