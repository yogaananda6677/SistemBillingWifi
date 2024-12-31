<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

use App\Exports\LaporanSalesAreaExport;
use App\Exports\RekapKeuanganBulananExport;
use App\Exports\RekapHarianBulananExport;

class LaporanController extends Controller
{

    public function index(Request $request)
    {
        $tahun = (int) ($request->input('tahun') ?? now()->year);
        $bulan = (int) ($request->input('bulan') ?? now()->month);

        // unit yg dipilih (sales-area key)
        $selectedUnits = $request->input('units', []); // contoh: ['sales-3-area-1', 'sales-5-area-2']

        // ====================== STAT GLOBAL ATAS (PER BULAN) ======================

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


$rows = collect();

foreach ($assignments as $asg) {
    $idSales = $asg->id_sales;
    $idArea  = $asg->id_area;

    // =========================
    // 1. DATA BULAN INI
    // =========================

    // PENDAPATAN BULAN INI
    $detailPembayaran = DB::table('pembayaran as p')
        ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
        ->select(
            'p.tanggal_bayar',
            'p.no_pembayaran',
            'p.nominal',
            'pl.nama as nama_pelanggan'
        )
        ->where('p.id_sales', $idSales)
        ->where('pl.id_area', $idArea)
        ->whereYear('p.tanggal_bayar', $tahun)
        ->whereMonth('p.tanggal_bayar', $bulan)
        ->orderBy('p.tanggal_bayar', 'asc')
        ->get();

    $pendapatan = (float) $detailPembayaran->sum('nominal');

    // KOMISI BULAN INI
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
        ->where('tk.id_sales', $idSales)
        ->where('pl.id_area', $idArea)
        ->whereYear('p.tanggal_bayar', $tahun)
        ->whereMonth('p.tanggal_bayar', $bulan)
        ->orderBy('p.tanggal_bayar', 'asc')
        ->get();

    $totalKomisi = (float) $detailKomisi->sum('nominal_komisi');

    // PENGELUARAN BULAN INI
    $detailPengeluaran = DB::table('pengeluaran as pg')
        ->select(
            'pg.tanggal_approve',
            'pg.nama_pengeluaran',
            'pg.catatan',
            'pg.nominal'
        )
        ->where('pg.id_sales', $idSales)
        ->where('pg.id_area', $idArea)
        ->where('pg.status_approve', 'approved')
        ->whereYear('pg.tanggal_approve', $tahun)
        ->whereMonth('pg.tanggal_approve', $bulan)
        ->orderBy('pg.tanggal_approve', 'asc')
        ->get();

    $totalPengeluaran = (float) $detailPengeluaran->sum('nominal');

    // KEWAJIBAN BERSIH BULAN INI
    $totalBersih = $pendapatan - $totalKomisi - $totalPengeluaran;

    // SETORAN BULAN INI (PAKAI KOLOM tahun & bulan, SAMA SEPERTI PembukuanController)
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
        ->where('st.id_sales', $idSales)
        ->where('st.id_area', $idArea)
        ->where('st.tahun', $tahun)
        ->where('st.bulan', $bulan)
        ->orderBy('st.tanggal_setoran', 'asc')
        ->get();

    $totalSetoranBulanIni = (float) $detailSetoran->sum('nominal');

    // SELISIH BULAN INI
    $selisihBulanIni = $totalSetoranBulanIni - $totalBersih; // >0 = lebih, <0 = kurang


    // =========================
    // 2. SALDO AKUMULASI S.D BULAN INI
    // =========================

    // TOTAL PENDAPATAN SAMPAI BULAN INI
    $pendapatanTotal = DB::table('pembayaran as p')
        ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
        ->where('p.id_sales', $idSales)
        ->where('pl.id_area', $idArea)
        ->where(function ($q) use ($tahun, $bulan) {
            $q->whereYear('p.tanggal_bayar', '<', $tahun)
              ->orWhere(function ($qq) use ($tahun, $bulan) {
                  $qq->whereYear('p.tanggal_bayar', $tahun)
                     ->whereMonth('p.tanggal_bayar', '<=', $bulan);
              });
        })
        ->sum('p.nominal');

    // TOTAL KOMISI SAMPAI BULAN INI
    $komisiTotal = DB::table('transaksi_komisi as tk')
        ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
        ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
        ->where('tk.id_sales', $idSales)
        ->where('pl.id_area', $idArea)
        ->where(function ($q) use ($tahun, $bulan) {
            $q->whereYear('p.tanggal_bayar', '<', $tahun)
              ->orWhere(function ($qq) use ($tahun, $bulan) {
                  $qq->whereYear('p.tanggal_bayar', $tahun)
                     ->whereMonth('p.tanggal_bayar', '<=', $bulan);
              });
        })
        ->sum('tk.nominal_komisi');

    // TOTAL PENGELUARAN SAMPAI BULAN INI
    $pengeluaranTotal = DB::table('pengeluaran as pg')
        ->where('pg.id_sales', $idSales)
        ->where('pg.id_area', $idArea)
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

    // TOTAL SETORAN SAMPAI BULAN INI (PAKAI tahun/bulan – BUKAN YEAR/MONTH(tanggal_setoran))
    $totalSetoranSampaiBulanIni = DB::table('setoran as st')
        ->where('st.id_sales', $idSales)
        ->where('st.id_area', $idArea)
        ->where(function ($q) use ($tahun, $bulan) {
            $q->where('st.tahun', '<', $tahun)
              ->orWhere(function ($qq) use ($tahun, $bulan) {
                  $qq->where('st.tahun', $tahun)
                     ->where('st.bulan', '<=', $bulan);
              });
        })
        ->sum('st.nominal');

    $saldoGlobal = $totalSetoranSampaiBulanIni - $totalKewajibanSampaiBulanIni;


    // =========================
    // 3. PUSH KE COLLECTION
    // =========================

    $key = 'sales-' . $idSales . '-area-' . $idArea;

    $rows->push((object) [
        'key'                => $key,
        'jenis'              => 'sales',
        'label'              => $asg->nama_sales . ' – ' . $asg->nama_area,
        'user_id'            => $asg->user_id,
        'id_sales'           => $idSales,
        'id_area'            => $idArea,

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

$rekap = $rows->sortBy('label')->values();


        return view('laporan.index', [
            'rekap'          => $rekap,
            'selectedYear'   => $tahun,
            'selectedMonth'  => $bulan,
            'stat'           => $stat,
            'selectedUnits'  => $selectedUnits,
        ]);
    }

    /**
     * EXPORT EXCEL – JANGKANYA 1 TAHUN PENUH
     * Tiap sheet = 1 Sales + 1 Wilayah, kolom Jan–Des (diatur di export class).
     */
    public function exportExcel(Request $request)
    {
        $tahun = (int) ($request->input('tahun') ?? now()->year);
        $selectedUnits = $request->input('units', []);

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
            ->get()
            ->map(function ($row) {
                $row->key = 'sales-' . $row->id_sales . '-area-' . $row->id_area;
                return $row;
            });

        if (!empty($selectedUnits)) {
            $targets = $assignments->whereIn('key', $selectedUnits)->values();
        } else {
            $targets = $assignments;
        }

        if ($targets->isEmpty()) {
            return back()->with('error', 'Tidak ada unit yang dipilih untuk diexport.');
        }

        $fileName = 'laporan-pembukuan-' . $tahun . '.xlsx';

        return Excel::download(
            new LaporanSalesAreaExport($tahun, $targets),
            $fileName
        );
    }

    public function exportPdf(Request $request)
    {
        return back()->with('error', 'Export PDF belum diaktifkan.');
    }

    /**
     * EXPORT REKAP KEUANGAN BULANAN (1 sheet, semua sales-wilayah)
     */
    public function exportRekapKeuangan(Request $request)
    {
        $tahun = (int) ($request->input('tahun') ?? now()->year);
        $bulan = (int) ($request->input('bulan') ?? now()->month);

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

        $fileName = sprintf(
            'rekap-keuangan-%d-%02d.xlsx',
            $tahun,
            $bulan
        );

        return Excel::download(
            new RekapKeuanganBulananExport($tahun, $bulan, $assignments),
            $fileName
        );
    }

    /**
     * EXPORT REKAP HARIAN / BULANAN (per sheet = 1 bulan, format tabel NO/TANGGAL/PEMASUKAN/PENGELUARAN)
     */
    public function exportRekapHarianBulanan(Request $request)
    {
        $tahun = (int) ($request->input('tahun') ?? now()->year);

        $fileName = 'rekap-harian-bulanan-' . $tahun . '.xlsx';

        return Excel::download(
            new RekapHarianBulananExport($tahun),
            $fileName
        );
    }
}
