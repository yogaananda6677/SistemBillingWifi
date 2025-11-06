<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
// Jika nanti sudah pakai Excel & PDF:
// use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\LaporanGlobalExport;
// use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tahun = (int) ($request->input('tahun') ?? now()->year);
        $bulan = (int) ($request->input('bulan') ?? now()->month);

        // unit yg dipilih (sales-area key)
        $selectedUnits = $request->input('units', []); // contoh: ['sales-3-area-1', 'sales-5-area-2']

        $awalBulan  = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $akhirBulan = (clone $awalBulan)->endOfMonth();

        // ====================== STAT GLOBAL ATAS ======================

        $jumlahPelanggan = DB::table('pembayaran as p')
            ->whereYear('p.tanggal_bayar', $tahun)
            ->whereMonth('p.tanggal_bayar', $bulan)
            ->distinct('p.id_pelanggan')
            ->count('p.id_pelanggan');

        $jumlahPembayaran = DB::table('pembayaran as p')
            ->whereYear('p.tanggal_bayar', $tahun)
            ->whereMonth('p.tanggal_bayar', $bulan)
            ->sum('p.nominal');

        $jumlahPengeluaran = DB::table('pengeluaran as pg')
            ->where('pg.status_approve', 'approved')
            ->whereYear('pg.tanggal_approve', $tahun)
            ->whereMonth('pg.tanggal_approve', $bulan)
            ->sum('pg.nominal');

        $jumlahKomisi = DB::table('transaksi_komisi as tk')
            ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
            ->whereYear('p.tanggal_bayar', $tahun)
            ->whereMonth('p.tanggal_bayar', $bulan)
            ->sum('tk.nominal_komisi');

        $labaKotor = $jumlahPembayaran - $jumlahPengeluaran - $jumlahKomisi;

        $stat = [
            'jumlah_pelanggan'   => $jumlahPelanggan,
            'jumlah_pembayaran'  => $jumlahPembayaran,
            'jumlah_pengeluaran' => $jumlahPengeluaran,
            'jumlah_komisi'      => $jumlahKomisi,
            'laba_kotor'         => $labaKotor,
            'last_updated'       => now(),
        ];

        $rows = collect();

        // ======================
        // REKAP PER SALES + WILAYAH (AREA)
        // ======================

        // mapping sales ke area (wilayah)
        $assignments = DB::table('area_sales as asg')
            ->join('sales as s', 's.id_sales', '=', 'asg.id_sales')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->join('area as a', 'a.id_area', '=', 'asg.id_area')
            ->select(
                'asg.id_area',
                'a.nama_area',
                's.id_sales',
                'u.id as user_id',
                'u.name as nama_sales'
            )
            ->orderBy('u.name')
            ->orderBy('a.nama_area')
            ->get();

        foreach ($assignments as $asg) {
            $idSales = $asg->id_sales;
            $idArea  = $asg->id_area;

            // ---------- PENDAPATAN: pembayaran per sales + area ----------
            $pendapatan = DB::table('pembayaran as p')
                ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->where('p.id_sales', $idSales)
                ->where('pl.id_area', $idArea)
                ->sum('p.nominal');

            // ---------- KOMISI: transaksi_komisi per sales + area ----------
            $komisi = DB::table('transaksi_komisi as tk')
                ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
                ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->where('tk.id_sales', $idSales)
                ->where('pl.id_area', $idArea)
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->sum('tk.nominal_komisi');

            // ---------- PENGELUARAN: pengeluaran approved per sales + area ----------
            $pengeluaran = DB::table('pengeluaran as pg')
                ->where('pg.id_sales', $idSales)
                ->where('pg.id_area', $idArea)
                ->where('pg.status_approve', 'approved')
                ->whereYear('pg.tanggal_approve', $tahun)
                ->whereMonth('pg.tanggal_approve', $bulan)
                ->sum('pg.nominal');

            // ---------- SETORAN: per sales + area ----------
            $setoran = DB::table('setoran as st')
                ->where('st.id_sales', $idSales)
                ->where('st.id_area', $idArea)
                ->whereYear('st.tanggal_setoran', $tahun)
                ->whereMonth('st.tanggal_setoran', $bulan)
                ->sum('st.nominal');

            // perhitungan bersih & lebih/kurang setor (bulan ini saja)
            $totalBersih = $pendapatan - $komisi - $pengeluaran;
            $selisih     = $setoran - $totalBersih; // >0 = lebih setor, <0 = kurang setor

            // ---------- DETAIL PEMBAYARAN ----------
            $detailPembayaran = DB::table('pembayaran as p')
                ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->select(
                    'p.no_pembayaran',
                    'p.tanggal_bayar',
                    'p.nominal',
                    'pl.nama as nama_pelanggan'
                )
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->where('p.id_sales', $idSales)
                ->where('pl.id_area', $idArea)
                ->orderBy('p.tanggal_bayar')
                ->get();

            // ---------- DETAIL KOMISI ----------
            $detailKomisi = DB::table('transaksi_komisi as tk')
                ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
                ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->select(
                    'p.no_pembayaran',
                    'p.tanggal_bayar',
                    'tk.nominal_komisi',
                    'tk.jumlah_komisi',
                    'pl.nama as nama_pelanggan'
                )
                ->where('tk.id_sales', $idSales)
                ->where('pl.id_area', $idArea)
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->orderBy('p.tanggal_bayar')
                ->get();

            // ---------- DETAIL PENGELUARAN ----------
            $detailPengeluaran = DB::table('pengeluaran as pg')
                ->select(
                    'pg.nama_pengeluaran',
                    'pg.tanggal_approve',
                    'pg.nominal',
                    'pg.catatan'
                )
                ->where('pg.id_sales', $idSales)
                ->where('pg.id_area', $idArea)
                ->where('pg.status_approve', 'approved')
                ->whereYear('pg.tanggal_approve', $tahun)
                ->whereMonth('pg.tanggal_approve', $bulan)
                ->orderBy('pg.tanggal_approve')
                ->get();

            // ---------- DETAIL SETORAN ----------
            $detailSetoran = DB::table('setoran as st')
                ->join('admins as a', 'a.id_admin', '=', 'st.id_admin')
                ->join('users as ua', 'ua.id', '=', 'a.user_id')
                ->select(
                    'st.tanggal_setoran',
                    'st.nominal',
                    'st.catatan',
                    'ua.name as nama_admin'
                )
                ->where('st.id_sales', $idSales)
                ->where('st.id_area', $idArea)
                ->whereYear('st.tanggal_setoran', $tahun)
                ->whereMonth('st.tanggal_setoran', $bulan)
                ->orderBy('st.tanggal_setoran')
                ->get();

            // key unik per sales + area
            $key = 'sales-' . $idSales . '-area-' . $idArea;

            $rows->push((object) [
                'key'                => $key,
                'jenis'              => 'sales',
                'label'              => $asg->nama_sales . ' – ' . $asg->nama_area,
                'user_id'            => $asg->user_id,
                'id_sales'           => $idSales,
                'id_area'            => $idArea,

                'pendapatan'         => $pendapatan,
                'total_komisi'       => $komisi,
                'total_pengeluaran'  => $pengeluaran,
                'total_bersih'       => $totalBersih,
                'total_setoran'      => $setoran,
                'selisih'            => $selisih,

                'detail_pembayaran'  => $detailPembayaran,
                'detail_komisi'      => $detailKomisi,
                'detail_pengeluaran' => $detailPengeluaran,
                'detail_setoran'     => $detailSetoran,
            ]);
        }

        // TIDAK ADA ADMIN DI SINI
        $rekap = $rows->sortBy('label')->values();

        return view('laporan.index', [
            'rekap'          => $rekap,
            'selectedYear'   => $tahun,
            'selectedMonth'  => $bulan,
            'stat'           => $stat,
            'selectedUnits'  => $selectedUnits,
        ]);
    }

    // ========== EXPORT (boleh diisi nanti kalau sudah siap package) ==========

    public function exportExcel(Request $request)
    {
        // Nanti kalau sudah install Maatwebsite/Excel, isi di sini.
        return back()->with('error', 'Export Excel belum diaktifkan.');
    }

    public function exportPdf(Request $request)
    {
        // Nanti kalau sudah install dompdf, isi di sini.
        return back()->with('error', 'Export PDF belum diaktifkan.');
    }
}
