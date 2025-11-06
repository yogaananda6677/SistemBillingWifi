<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetoranSalesController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Ambil data sales dari user yang login
        $sales = DB::table('sales')
            ->where('user_id', $userId)
            ->first();

        if (!$sales) {
            return view('seles2.setoran.index', [
                'sales'          => null,
                'setorans'       => collect(),
                'areas'          => collect(),
                'selectedAreaId' => null,
                'totalWajib'     => 0,
                'totalSetoran'   => 0,
                'saldoGlobal'    => 0,
            ]);
        }

        $salesId       = $sales->id_sales;
        $selectedAreaId = $request->input('area_id'); // boleh null => semua area

        // =========================
        // LIST AREA YANG DIPEGANG SALES
        // =========================
        $areas = DB::table('area_sales as asg')
            ->join('area as a', 'a.id_area', '=', 'asg.id_area')
            ->select('a.id_area', 'a.nama_area')
            ->where('asg.id_sales', $salesId)
            ->orderBy('a.nama_area')
            ->get();

        // =========================
        // RIWAYAT SETORAN (dengan filter area)
        // =========================
        $setoranQuery = DB::table('setoran as st')
            ->join('admins as ad', 'ad.id_admin', '=', 'st.id_admin')
            ->join('users as ua', 'ua.id', '=', 'ad.user_id')
            ->leftJoin('area as a', 'a.id_area', '=', 'st.id_area')
            ->select(
                'st.id_setoran',
                'st.tanggal_setoran',
                'st.nominal',
                'st.catatan',
                'st.tahun',
                'st.bulan',
                'ua.name as nama_admin',
                'a.nama_area',
                'a.id_area as area_id'
            )
            ->where('st.id_sales', $salesId)
            ->orderBy('st.tanggal_setoran', 'desc');

        if ($selectedAreaId) {
            $setoranQuery->where('st.id_area', $selectedAreaId);
        }

        $setorans = $setoranQuery->get();

        // =========================
        // RINGKASAN GLOBAL (SESUAI FILTER AREA)
        // =========================

        // Total setoran
        $totalSetoran = (float) DB::table('setoran as st')
            ->where('st.id_sales', $salesId)
            ->when($selectedAreaId, function ($q) use ($selectedAreaId) {
                $q->where('st.id_area', $selectedAreaId);
            })
            ->sum('st.nominal');

        // Total pendapatan
        $pendapatanQuery = DB::table('pembayaran as p')
            ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->where('p.id_sales', $salesId);

        if ($selectedAreaId) {
            $pendapatanQuery->where('pl.id_area', $selectedAreaId);
        }

        $pendapatanTotal = (float) $pendapatanQuery->sum('p.nominal');

        // Total komisi
        $komisiQuery = DB::table('transaksi_komisi as tk')
            ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
            ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->where('p.id_sales', $salesId);

        if ($selectedAreaId) {
            $komisiQuery->where('pl.id_area', $selectedAreaId);
        }

        $komisiTotal = (float) $komisiQuery->sum('tk.nominal_komisi');

        // Total pengeluaran
        $pengeluaranQuery = DB::table('pengeluaran as pg')
            ->where('pg.id_sales', $salesId)
            ->where('pg.status_approve', 'approved');

        if ($selectedAreaId) {
            $pengeluaranQuery->where('pg.id_area', $selectedAreaId);
        }

        $pengeluaranTotal = (float) $pengeluaranQuery->sum('pg.nominal');

        $totalWajib  = $pendapatanTotal - $komisiTotal - $pengeluaranTotal;
        // + = lebih setor, - = masih kurang
        $saldoGlobal = $totalSetoran - $totalWajib;

        return view('seles2.setoran.index', [
            'sales'          => $sales,
            'setorans'       => $setorans,
            'areas'          => $areas,
            'selectedAreaId' => $selectedAreaId,
            'totalWajib'     => $totalWajib,
            'totalSetoran'   => $totalSetoran,
            'saldoGlobal'    => $saldoGlobal,
        ]);
    }
}
