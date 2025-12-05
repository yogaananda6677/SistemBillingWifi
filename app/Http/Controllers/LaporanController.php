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

        $awalBulan  = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $akhirBulan = (clone $awalBulan)->endOfMonth();

        // ======================
        // STAT GLOBAL ATAS
        // ======================

        // Pelanggan yang punya pembayaran di bulan ini
        $jumlahPelanggan = DB::table('pembayaran as p')
            ->whereYear('p.tanggal_bayar', $tahun)
            ->whereMonth('p.tanggal_bayar', $bulan)
            ->distinct('p.id_pelanggan')
            ->count('p.id_pelanggan');

        // Total nominal pembayaran bulan ini (semua: admin + sales)
        $jumlahPembayaran = DB::table('pembayaran as p')
            ->whereYear('p.tanggal_bayar', $tahun)
            ->whereMonth('p.tanggal_bayar', $bulan)
            ->sum('p.nominal');

        // Total pengeluaran approve bulan ini
        $jumlahPengeluaran = DB::table('pengeluaran as pg')
            ->where('pg.status_approve', 'approved')
            ->whereYear('pg.tanggal_approve', $tahun)
            ->whereMonth('pg.tanggal_approve', $bulan)
            ->sum('pg.nominal');

        // Total komisi bulan ini
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
        // REKAP PER SALES
        // ======================

        $sales = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->select('s.id_sales', 'u.id as user_id', 'u.name')
            ->orderBy('u.name')
            ->get();

        foreach ($sales as $s) {
            // Pendapatan: pembayaran bulan ini dimana id_sales = sales ini
            $pendapatan = DB::table('pembayaran as p')
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->where('p.id_sales', $s->id_sales)
                ->sum('p.nominal');

            // Komisi: dari transaksi_komisi bulan ini untuk sales ini
            $komisi = DB::table('transaksi_komisi as tk')
                ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
                ->where('tk.id_sales', $s->id_sales)
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->sum('tk.nominal_komisi');

            // Pengeluaran: pengeluaran approved bulan ini untuk sales ini
            $pengeluaran = DB::table('pengeluaran as pg')
                ->where('pg.id_sales', $s->id_sales)
                ->where('pg.status_approve', 'approved')
                ->whereYear('pg.tanggal_approve', $tahun)
                ->whereMonth('pg.tanggal_approve', $bulan)
                ->sum('pg.nominal');

            // Setoran oleh sales ini bulan ini
            $setoran = DB::table('setoran as st')
                ->where('st.id_sales', $s->id_sales)
                ->whereYear('st.tanggal_setoran', $tahun)
                ->whereMonth('st.tanggal_setoran', $bulan)
                ->sum('st.nominal');

            $totalBersih = $pendapatan - $komisi - $pengeluaran;
            $selisih     = $setoran - $totalBersih;

            // DETAIL PEMBAYARAN
            $detailPembayaran = DB::table('pembayaran as p')
                ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->select(
                    'p.no_pembayaran',
                    'p.tanggal_bayar',
                    'p.nominal',
                    'pl.nama as nama_pelanggan'
                )
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->where('p.id_sales', $s->id_sales)
                ->orderBy('p.tanggal_bayar')
                ->get();

            // DETAIL KOMISI
            $detailKomisi = DB::table('transaksi_komisi as tk')
                ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
                ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->select(
                    'p.no_pembayaran',
                    'p.tanggal_bayar',
                    'tk.nominal_komisi',
                    'tk.jumlah_komisi',
                    'pl.nama as nama_pelanggan'
                )
                ->where('tk.id_sales', $s->id_sales)
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->orderBy('p.tanggal_bayar')
                ->get();

            // DETAIL PENGELUARAN
            $detailPengeluaran = DB::table('pengeluaran as pg')
                ->select(
                    'pg.nama_pengeluaran',
                    'pg.tanggal_approve',
                    'pg.nominal',
                    'pg.catatan'
                )
                ->where('pg.id_sales', $s->id_sales)
                ->where('pg.status_approve', 'approved')
                ->whereYear('pg.tanggal_approve', $tahun)
                ->whereMonth('pg.tanggal_approve', $bulan)
                ->orderBy('pg.tanggal_approve')
                ->get();

            // DETAIL SETORAN
            $detailSetoran = DB::table('setoran as st')
                ->join('admins as a', 'a.id_admin', '=', 'st.id_admin')
                ->join('users as ua', 'ua.id', '=', 'a.user_id')
                ->select(
                    'st.tanggal_setoran',
                    'st.nominal',
                    'st.catatan',
                    'ua.name as nama_admin'
                )
                ->where('st.id_sales', $s->id_sales)
                ->whereYear('st.tanggal_setoran', $tahun)
                ->whereMonth('st.tanggal_setoran', $bulan)
                ->orderBy('st.tanggal_setoran')
                ->get();

            $rows->push((object) [
                'jenis'              => 'sales',
                'label'              => $s->name . ' (sales)',
                'user_id'            => $s->user_id,
                'id_ref'             => $s->id_sales,
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

        // ======================
        // REKAP PER ADMIN
        // ======================

        $admins = DB::table('admins as a')
            ->join('users as u', 'u.id', '=', 'a.user_id')
            ->select(
                'u.id as user_id',
                'u.name',
                DB::raw('MIN(a.id_admin) as id_admin')
            )
            ->groupBy('u.id', 'u.name')
            ->orderBy('u.name')
            ->get();

        foreach ($admins as $a) {
            // Pendapatan: pembayaran bulan ini yang diinput oleh admin ini (p.id_user)
            $pendapatan = DB::table('pembayaran as p')
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->where('p.id_user', $a->user_id)
                ->sum('p.nominal');

            // DETAIL PEMBAYARAN
            $detailPembayaran = DB::table('pembayaran as p')
                ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->select(
                    'p.no_pembayaran',
                    'p.tanggal_bayar',
                    'p.nominal',
                    'pl.nama as nama_pelanggan'
                )
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->where('p.id_user', $a->user_id)
                ->orderBy('p.tanggal_bayar')
                ->get();

            $rows->push((object) [
                'jenis'              => 'admin',
                'label'              => $a->name . ' (admin)',
                'user_id'            => $a->user_id,
                'id_ref'             => $a->id_admin,
                'pendapatan'         => $pendapatan,
                'total_komisi'       => 0,
                'total_pengeluaran'  => 0,
                'total_bersih'       => $pendapatan,   // admin dianggap langsung setor
                'total_setoran'      => $pendapatan,   // otomatis sudah disetor
                'selisih'            => 0,
                'detail_pembayaran'  => $detailPembayaran,
                'detail_komisi'      => collect(),
                'detail_pengeluaran' => collect(),
                'detail_setoran'     => collect(),
            ]);
        }

        $rekap = $rows->sortBy('label')->values();

        return view('laporan.index', [
            'rekap'         => $rekap,
            'selectedYear'  => $tahun,
            'selectedMonth' => $bulan,
            'stat'          => $stat,
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
