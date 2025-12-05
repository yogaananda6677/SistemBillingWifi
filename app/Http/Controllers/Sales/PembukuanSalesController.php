<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembukuanSalesController extends Controller
{
    public function index(Request $request)
    {
        $tahun = (int) ($request->input('tahun') ?? now()->year);
        $bulan = (int) ($request->input('bulan') ?? now()->month);

        $rekap = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
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
            ->get()
            ->map(function ($row) {
                $row->harus_disetorkan =
                    $row->total_pendapatan - $row->total_komisi - $row->total_pengeluaran;

                $row->selisih_setoran =
                    $row->total_setoran - $row->harus_disetorkan;

                return $row;
            });

        // variabel yang DIKIRIM ke view
        return view('seles2.pembukuan.index', [
            'rekap'         => $rekap,
            'selectedYear'  => $tahun,
            'selectedMonth' => $bulan,
        ]);
    }
}
