<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembukuanController extends Controller
{
    /**
     * Rekap semua sales per bulan (admin).
     */
    public function index(Request $request)
    {
        $tahun = (int) ($request->input('tahun') ?? now()->year);
        $bulan = (int) ($request->input('bulan') ?? now()->month);

        $rekap = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->selectRaw('
                s.id_sales,
                u.name as nama_sales,

                -- PENDAPATAN: pembayaran bulan ini oleh sales ini
                COALESCE((
                    SELECT SUM(p.nominal)
                    FROM pembayaran p
                    WHERE p.id_sales = s.id_sales
                      AND YEAR(p.tanggal_bayar) = ?
                      AND MONTH(p.tanggal_bayar) = ?
                ), 0) AS total_pendapatan,

                -- KOMISI: komisi dari pembayaran bulan ini
                COALESCE((
                    SELECT SUM(tk.nominal_komisi)
                    FROM transaksi_komisi tk
                    JOIN pembayaran p2 ON p2.id_pembayaran = tk.id_pembayaran
                    WHERE tk.id_sales = s.id_sales
                      AND YEAR(p2.tanggal_bayar) = ?
                      AND MONTH(p2.tanggal_bayar) = ?
                ), 0) AS total_komisi,

                -- PENGELUARAN: pengeluaran approved di bulan ini (tanggal_approve)
                COALESCE((
                    SELECT SUM(pg.nominal)
                    FROM pengeluaran pg
                    WHERE pg.id_sales = s.id_sales
                      AND pg.status_approve = "approved"
                      AND pg.tanggal_approve IS NOT NULL
                      AND YEAR(pg.tanggal_approve) = ?
                      AND MONTH(pg.tanggal_approve) = ?
                ), 0) AS total_pengeluaran,

                -- SETORAN: semua setoran di bulan ini (pakai tanggal_setoran)
                COALESCE((
                    SELECT SUM(st.nominal)
                    FROM setoran st
                    WHERE st.id_sales = s.id_sales
                      AND YEAR(st.tanggal_setoran) = ?
                      AND MONTH(st.tanggal_setoran) = ?
                ), 0) AS total_setoran
            ', [
                $tahun, $bulan,
                $tahun, $bulan,
                $tahun, $bulan,
                $tahun, $bulan,
            ])
            ->get()
            ->map(function ($row) {
                $row->harus_disetorkan =
                    $row->total_pendapatan - $row->total_komisi - $row->total_pengeluaran;

                $row->selisih_setoran =
                    $row->total_setoran - $row->harus_disetorkan;

                return $row;
            });

        return view('admin.pembukuan.index', [
            'rekap'        => $rekap,
            'selectedYear' => $tahun,
            'selectedMonth'=> $bulan,
        ]);
    }

    /**
     * Detail pembukuan satu sales untuk bulan tertentu.
     */
    public function show(Request $request, $salesId)
    {
        $tahun = (int) ($request->input('tahun') ?? now()->year);
        $bulan = (int) ($request->input('bulan') ?? now()->month);

        // Rekap untuk header
        $summary = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->where('s.id_sales', $salesId)
            ->selectRaw('
                s.id_sales,
                u.name as nama_sales,

                COALESCE((
                    SELECT SUM(p.nominal)
                    FROM pembayaran p
                    WHERE p.id_sales = s.id_sales
                      AND YEAR(p.tanggal_bayar) = ?
                      AND MONTH(p.tanggal_bayar) = ?
                ), 0) AS total_pendapatan,

                COALESCE((
                    SELECT SUM(tk.nominal_komisi)
                    FROM transaksi_komisi tk
                    JOIN pembayaran p2 ON p2.id_pembayaran = tk.id_pembayaran
                    WHERE tk.id_sales = s.id_sales
                      AND YEAR(p2.tanggal_bayar) = ?
                      AND MONTH(p2.tanggal_bayar) = ?
                ), 0) AS total_komisi,

                COALESCE((
                    SELECT SUM(pg.nominal)
                    FROM pengeluaran pg
                    WHERE pg.id_sales = s.id_sales
                      AND pg.status_approve = "approved"
                      AND pg.tanggal_approve IS NOT NULL
                      AND YEAR(pg.tanggal_approve) = ?
                      AND MONTH(pg.tanggal_approve) = ?
                ), 0) AS total_pengeluaran,

                COALESCE((
                    SELECT SUM(st.nominal)
                    FROM setoran st
                    WHERE st.id_sales = s.id_sales
                      AND YEAR(st.tanggal_setoran) = ?
                      AND MONTH(st.tanggal_setoran) = ?
                ), 0) AS total_setoran
            ', [
                $tahun, $bulan,
                $tahun, $bulan,
                $tahun, $bulan,
                $tahun, $bulan,
            ])
            ->first();

        if ($summary) {
            $summary->harus_disetorkan =
                $summary->total_pendapatan - $summary->total_komisi - $summary->total_pengeluaran;

            $summary->selisih_setoran =
                $summary->total_setoran - $summary->harus_disetorkan;
        }

        // DETAIL: PEMBAYARAN
        $pembayaran = DB::table('pembayaran as p')
            ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->where('p.id_sales', $salesId)
            ->whereYear('p.tanggal_bayar', $tahun)
            ->whereMonth('p.tanggal_bayar', $bulan)
            ->select(
                'p.*',
                'pl.nama as nama_pelanggan'
            )
            ->orderBy('p.tanggal_bayar')
            ->get();

        // DETAIL: KOMISI
        $komisi = DB::table('transaksi_komisi as tk')
            ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
            ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->where('tk.id_sales', $salesId)
            ->whereYear('p.tanggal_bayar', $tahun)
            ->whereMonth('p.tanggal_bayar', $bulan)
            ->select(
                'tk.*',
                'p.tanggal_bayar',
                'p.no_pembayaran',
                'pl.nama as nama_pelanggan'
            )
            ->orderBy('p.tanggal_bayar')
            ->get();

        // DETAIL: PENGELUARAN
        $pengeluaran = DB::table('pengeluaran as pg')
            ->leftJoin('admins as a', 'a.id_admin', '=', 'pg.id_admin')
            ->leftJoin('users as ua', 'ua.id', '=', 'a.user_id')
            ->where('pg.id_sales', $salesId)
            ->where('pg.status_approve', 'approved')
            ->whereNotNull('pg.tanggal_approve')
            ->whereYear('pg.tanggal_approve', $tahun)
            ->whereMonth('pg.tanggal_approve', $bulan)
            ->select(
                'pg.*',
                'ua.name as nama_admin'
            )
            ->orderBy('pg.tanggal_approve')
            ->get();

        // DETAIL: SETORAN
        $setoran = DB::table('setoran as st')
            ->join('admins as a', 'a.id_admin', '=', 'st.id_admin')
            ->join('users as ua', 'ua.id', '=', 'a.user_id')
            ->where('st.id_sales', $salesId)
            ->whereYear('st.tanggal_setoran', $tahun)
            ->whereMonth('st.tanggal_setoran', $bulan)
            ->select(
                'st.*',
                'ua.name as nama_admin'
            )
            ->orderBy('st.tanggal_setoran')
            ->get();

        return view('admin.pembukuan.show', [
            'summary'       => $summary,
            'pembayaran'    => $pembayaran,
            'komisi'        => $komisi,
            'pengeluaran'   => $pengeluaran,
            'setoran'       => $setoran,
            'selectedYear'  => $tahun,
            'selectedMonth' => $bulan,
        ]);
    }
}
