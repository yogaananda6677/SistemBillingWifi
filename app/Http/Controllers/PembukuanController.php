<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembukuanController extends Controller
{
    public function index(Request $request)
    {
        $tahun = (int) ($request->input('tahun') ?? now()->year);
        $bulan = (int) ($request->input('bulan') ?? now()->month);

        // ==========================
        // STATISTIK GLOBAL (ATAS)
        // ==========================
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

        $stat = [
            'jumlah_pelanggan'   => $jumlahPelanggan,
            'jumlah_pembayaran'  => $jumlahPembayaran,
            'jumlah_pengeluaran' => $jumlahPengeluaran,
        ];

        $rows = collect();

        // ==========================
        //  REKAP PER SALES–AREA
        // ==========================
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

            // 1. DATA BULAN INI (pendapatan, komisi, pengeluaran) PER SALES–AREA
            $detailPembayaran = DB::table('pembayaran as p')
                ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->select(
                    'p.tanggal_bayar',
                    'p.no_pembayaran',
                    'p.nominal',
                    'pl.nama as nama_pelanggan'
                )
                ->where('p.id_sales', $asg->id_sales)
                ->where('pl.id_area', $asg->id_area)
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->orderBy('p.tanggal_bayar', 'asc')
                ->get();

            $pendapatan = (float) $detailPembayaran->sum('nominal');

            $detailKomisi = DB::table('transaksi_komisi as tk')
                ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
                ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->select(
                    'p.tanggal_bayar',
                    'p.no_pembayaran',
                    'pl.nama as nama_pelanggan',
                    'tk.jumlah_komisi',
                    'tk.nominal_komisi'
                )
                ->where('tk.id_sales', $asg->id_sales)
                ->where('pl.id_area', $asg->id_area)
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->orderBy('p.tanggal_bayar', 'asc')
                ->get();

            $totalKomisi = (float) $detailKomisi->sum('nominal_komisi');

            $detailPengeluaran = DB::table('pengeluaran as pg')
                ->leftJoin('admins as ad', 'ad.id_admin', '=', 'pg.id_admin')
                ->leftJoin('users as ua', 'ua.id', '=', 'ad.user_id')
                ->select(
                    'pg.tanggal_approve',
                    'pg.nama_pengeluaran',
                    'pg.catatan',
                    'pg.nominal',
                    'ua.name as nama_admin'
                )
                ->where('pg.id_sales', $asg->id_sales)
                ->where('pg.id_area', $asg->id_area)
                ->where('pg.status_approve', 'approved')
                ->whereYear('pg.tanggal_approve', $tahun)
                ->whereMonth('pg.tanggal_approve', $bulan)
                ->orderBy('pg.tanggal_approve', 'asc')
                ->get();

            $totalPengeluaran = (float) $detailPengeluaran->sum('nominal');

            // Kewajiban bulan ini
            $totalBersih = $pendapatan - $totalKomisi - $totalPengeluaran;

            // 2. SETORAN BULAN INI (PERIODE MANUAL: kolom tahun & bulan)
            $detailSetoran = DB::table('setoran as st')
                ->join('admins as ad', 'ad.id_admin', '=', 'st.id_admin')
                ->join('users as ua', 'ua.id', '=', 'ad.user_id')
                ->select(
                    'st.id_setoran',
                    'st.tanggal_setoran',
                    'st.nominal',
                    'st.catatan',
                    'st.tahun',
                    'st.bulan',
                    'ua.name as nama_admin'
                )
                ->where('st.id_sales', $asg->id_sales)
                ->where('st.id_area', $asg->id_area)
                ->where('st.tahun', $tahun)
                ->where('st.bulan', $bulan)
                ->orderBy('st.tanggal_setoran', 'asc')
                ->get();

            $totalSetoranBulanIni = (float) $detailSetoran->sum('nominal');

            // Selisih bulan ini (positif = kelebihan, negatif = kurang)
            $selisihBulanIni = $totalSetoranBulanIni - $totalBersih;

            // 3. SALDO AKUMULASI S.D. BULAN INI (tanpa alokasi, cuma total kewajiban vs total setoran)
            //    - semua kewajiban s.d. (tahun, bulan) ini
            //    - semua setoran s.d. (tahun, bulan) ini (pakai kolom tahun/bulan setoran)
            $pendapatanTotal = DB::table('pembayaran as p')
                ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->where('p.id_sales', $asg->id_sales)
                ->where('pl.id_area', $asg->id_area)
                ->where(function ($q) use ($tahun, $bulan) {
                    $q->whereYear('p.tanggal_bayar', '<', $tahun)
                      ->orWhere(function ($qq) use ($tahun, $bulan) {
                          $qq->whereYear('p.tanggal_bayar', $tahun)
                             ->whereMonth('p.tanggal_bayar', '<=', $bulan);
                      });
                })
                ->sum('p.nominal');

            $komisiTotal = DB::table('transaksi_komisi as tk')
                ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
                ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->where('tk.id_sales', $asg->id_sales)
                ->where('pl.id_area', $asg->id_area)
                ->where(function ($q) use ($tahun, $bulan) {
                    $q->whereYear('p.tanggal_bayar', '<', $tahun)
                      ->orWhere(function ($qq) use ($tahun, $bulan) {
                          $qq->whereYear('p.tanggal_bayar', $tahun)
                             ->whereMonth('p.tanggal_bayar', '<=', $bulan);
                      });
                })
                ->sum('tk.nominal_komisi');

            $pengeluaranTotal = DB::table('pengeluaran as pg')
                ->where('pg.id_sales', $asg->id_sales)
                ->where('pg.id_area', $asg->id_area)
                ->where('pg.status_approve', 'approved')
                ->where(function ($q) use ($tahun, $bulan) {
                    $q->whereYear('pg.tanggal_approve', '<', $tahun)
                      ->orWhere(function ($qq) use ($tahun, $bulan) {
                          $qq->whereYear('pg.tanggal_approve', $tahun)
                             ->whereMonth('pg.tanggal_approve', '<=', $bulan);
                      });
                })
                ->sum('pg.nominal');

            $totalKewajibanSampaiBulanIni = $pendapatanTotal - $komisiTotal - $pengeluaranTotal;

            $totalSetoranSampaiBulanIni = DB::table('setoran as st')
                ->where('st.id_sales', $asg->id_sales)
                ->where('st.id_area', $asg->id_area)
                ->where(function ($q) use ($tahun, $bulan) {
                    $q->where('st.tahun', '<', $tahun)
                      ->orWhere(function ($qq) use ($tahun, $bulan) {
                          $qq->where('st.tahun', $tahun)
                             ->where('st.bulan', '<=', $bulan);
                      });
                })
                ->sum('st.nominal');

            $saldoGlobal = $totalSetoranSampaiBulanIni - $totalKewajibanSampaiBulanIni;

            // 4. PUSH KE COLLECTION
            $rows->push((object) [
                'jenis'              => 'sales',
                'label'              => $asg->nama_sales . ' – ' . $asg->nama_area,
                'user_id'            => $asg->user_id,
                'id_sales'           => $asg->id_sales,
                'id_area'            => $asg->id_area,

                'pendapatan'         => $pendapatan,
                'total_komisi'       => $totalKomisi,
                'total_pengeluaran'  => $totalPengeluaran,
                'total_bersih'       => $totalBersih,

                'total_setoran'      => $totalSetoranBulanIni,
                'selisih'            => $selisihBulanIni,
                'saldo_global'       => $saldoGlobal,

                'detail_pembayaran'  => $detailPembayaran,
                'detail_komisi'      => $detailKomisi,
                'detail_pengeluaran' => $detailPengeluaran,
                'detail_setoran'     => $detailSetoran,
            ]);
        }

        // ==========================
        //  REKAP PER ADMIN (pendapatan langsung, tanpa sales)
        // ==========================
        $admins = DB::table('admins as ad')
            ->join('users as u', 'u.id', '=', 'ad.user_id')
            ->select('ad.id_admin', 'u.id as user_id', 'u.name')
            ->orderBy('u.name')
            ->get()
            ->groupBy('user_id')
            ->map(function ($rows) {
                return $rows->first();
            })
            ->values();

        foreach ($admins as $ad) {
            $detailPembayaranAdmin = DB::table('pembayaran as p')
                ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->select(
                    'p.tanggal_bayar',
                    'p.no_pembayaran',
                    'p.nominal',
                    'pl.nama as nama_pelanggan'
                )
                ->whereNull('p.id_sales')
                ->where('p.id_user', $ad->user_id)
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->orderBy('p.tanggal_bayar', 'asc')
                ->get();

            $pendapatanAdmin = (float) $detailPembayaranAdmin->sum('nominal');

            $rows->push((object) [
                'jenis'              => 'admin',
                'label'              => $ad->name . ' (admin)',
                'user_id'            => $ad->user_id,
                'id_admin'           => $ad->id_admin,

                'pendapatan'         => $pendapatanAdmin,
                'total_komisi'       => 0,
                'total_pengeluaran'  => 0,
                'total_bersih'       => $pendapatanAdmin,
                'total_setoran'      => 0,
                'selisih'            => 0,
                'saldo_global'       => 0,

                'detail_pembayaran'  => $detailPembayaranAdmin,
                'detail_komisi'      => collect(),
                'detail_pengeluaran' => collect(),
                'detail_setoran'     => collect(),
            ]);
        }

        $rekap = $rows->sortBy('label')->values();

        return view('pembukuan.index', [
            'rekap'         => $rekap,
            'selectedYear'  => $tahun,
            'selectedMonth' => $bulan,
            'stat'          => $stat,
        ]);
    }

    public function show(Request $request, $id)
    {
        return redirect()->route('pembukuan.index');
    }
}
