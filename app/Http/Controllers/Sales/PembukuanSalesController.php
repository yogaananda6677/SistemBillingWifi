<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembukuanSalesController extends Controller
{
    public function index(Request $request)
    {
        $tahun = (int) ($request->input('tahun') ?? now()->year);
        $bulan = (int) ($request->input('bulan') ?? now()->month);

        $userId = Auth::id();

        // Cari sales untuk user ini
        $sales = DB::table('sales')
            ->where('user_id', $userId)
            ->first();

        if (!$sales) {
            // Kalau belum terdaftar sebagai sales
            return view('seles2.pembukuan.index', [
                'rekap'           => null,
                'selectedYear'    => $tahun,
                'selectedMonth'   => $bulan,
                'pembayaranList'  => collect(),
                'komisiList'      => collect(),
                'pengeluaranList' => collect(),
                'setoranList'     => collect(),
            ]);
        }

        $salesId = $sales->id_sales;

        // =========================
        // DETAIL LIST PER BULAN
        // =========================

        // 1) Pembayaran dari pelanggan (pendapatan pelanggan)
        $pembayaranList = DB::table('pembayaran as p')
            ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->select(
                'p.id_pembayaran',
                'p.tanggal_bayar',
                'p.nominal',
                'pl.nama as nama_pelanggan',
                'p.no_pembayaran'
            )
            ->where('p.id_sales', $salesId)
            ->whereYear('p.tanggal_bayar', $tahun)
            ->whereMonth('p.tanggal_bayar', $bulan)
            ->orderBy('p.tanggal_bayar', 'desc')
            ->get();

        // 2) Komisi (berdasarkan pembayaran di bulan ini)
        $komisiList = DB::table('transaksi_komisi as tk')
            ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
            ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->select(
                'tk.id_komisi',
                'tk.nominal_komisi',
                'tk.jumlah_komisi',
                'p.tanggal_bayar',
                'p.nominal as nominal_bayar',
                'pl.nama as nama_pelanggan',
                'p.no_pembayaran'
            )
            ->where('p.id_sales', $salesId)
            ->whereYear('p.tanggal_bayar', $tahun)
            ->whereMonth('p.tanggal_bayar', $bulan)
            ->orderBy('p.tanggal_bayar', 'desc')
            ->get();

        // 3) Pengeluaran approved bulan ini
        $pengeluaranList = DB::table('pengeluaran as pg')
            ->select(
                'pg.id_pengeluaran',
                'pg.nama_pengeluaran',
                'pg.tanggal_approve',
                'pg.nominal',
                'pg.catatan',
                'pg.status_approve'
            )
            ->where('pg.id_sales', $salesId)
            ->where('pg.status_approve', 'approved')
            ->whereYear('pg.tanggal_approve', $tahun)
            ->whereMonth('pg.tanggal_approve', $bulan)
            ->orderBy('pg.tanggal_approve', 'desc')
            ->get();

        // 4) Setoran bulan ini (riwayat setoran bulan ini)
        $setoranList = DB::table('setoran as st')
            ->select(
                'st.id_setoran',
                'st.tanggal_setoran',
                'st.nominal',
                'st.catatan'
            )
            ->where('st.id_sales', $salesId)
            ->whereYear('st.tanggal_setoran', $tahun)
            ->whereMonth('st.tanggal_setoran', $bulan)
            ->orderBy('st.tanggal_setoran', 'desc')
            ->get();

        // =========================
        // HITUNG TOTALAN BULAN INI
        // =========================

        $pendapatanPelanggan = (float) $pembayaranList->sum('nominal');
        $komisi              = (float) $komisiList->sum('nominal_komisi');
        $pengeluaran         = (float) $pengeluaranList->sum('nominal');
        $totalSetoran        = (float) $setoranList->sum('nominal');

        // Pendapatan bersih bulan ini
        $pendapatanBersih = $pendapatanPelanggan - $komisi - $pengeluaran;

        // Wajib setor bulan ini = pendapatan bersih bulan ini
        $wajibSetor = $pendapatanBersih;

        // Uang belum disetor bulan ini (kalau negatif, jadikan 0)
        $uangBelumDisetor = max($wajibSetor - $totalSetoran, 0);

        // Objek rekap sederhana biar gampang dipakai di Blade
        $rekap = (object) [
            'total_pendapatan'            => $pendapatanPelanggan,
            'total_komisi'                => $komisi,
            'total_pengeluaran'           => $pengeluaran,
            'total_setoran'               => $totalSetoran,
            'pendapatan_bersih'           => $pendapatanBersih,
            'wajib_setor_bulan_ini'       => $wajibSetor,
            'uang_belum_disetor_bulan_ini'=> $uangBelumDisetor,
        ];

        return view('seles2.pembukuan.index', [
            'rekap'           => $rekap,
            'selectedYear'    => $tahun,
            'selectedMonth'   => $bulan,
            'pembayaranList'  => $pembayaranList,
            'komisiList'      => $komisiList,
            'pengeluaranList' => $pengeluaranList,
            'setoranList'     => $setoranList,
        ]);
    }
}
