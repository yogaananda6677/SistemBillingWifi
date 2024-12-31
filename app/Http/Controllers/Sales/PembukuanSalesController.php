<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\SalesSetoranService;


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
                'rekap'           => collect(),   // ← kosong tapi bentuknya collection
                'selectedYear'    => $tahun,
                'selectedMonth'   => $bulan,
            ]);
        }

        $salesId = $sales->id_sales;

        // =========================
        // AMBIL LIST AREA SALES
        // =========================
        $areas = DB::table('area_sales as asg')
            ->join('area as a', 'a.id_area', '=', 'asg.id_area')
            ->select('a.id_area', 'a.nama_area')
            ->where('asg.id_sales', $salesId)
            ->orderBy('a.nama_area')
            ->get();

        if ($areas->isEmpty()) {
            return view('seles2.pembukuan.index', [
                'rekap'           => collect(),
                'selectedYear'    => $tahun,
                'selectedMonth'   => $bulan,
            ]);
        }

        $rekapPerArea = collect();

        foreach ($areas as $area) {

            // 1) Pembayaran dari pelanggan di area ini
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
                ->where('pl.id_area', $area->id_area)
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->orderBy('p.tanggal_bayar', 'desc')
                ->get();

            // 2) Komisi dari pembayaran area ini
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
                ->where('pl.id_area', $area->id_area)
                ->whereYear('p.tanggal_bayar', $tahun)
                ->whereMonth('p.tanggal_bayar', $bulan)
                ->orderBy('p.tanggal_bayar', 'desc')
                ->get();

            // 3) Pengeluaran approved area ini
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
                ->where('pg.id_area', $area->id_area)
                ->where('pg.status_approve', 'approved')
                ->whereYear('pg.tanggal_approve', $tahun)
                ->whereMonth('pg.tanggal_approve', $bulan)
                ->orderBy('pg.tanggal_approve', 'desc')
                ->get();

            // 4) Setoran area ini bulan ini
$setoranList = DB::table('setoran as st')
    ->select(
        'st.id_setoran',
        'st.tanggal_setoran',
        'st.nominal',
        'st.catatan',
        'st.tahun',
        'st.bulan'
    )
    ->where('st.id_sales', $salesId)
    ->where('st.id_area', $area->id_area)
    ->where('st.tahun', $tahun)
    ->where('st.bulan', $bulan)
    ->orderBy('st.tanggal_setoran', 'desc')
    ->get();

// =========================
// HITUNG TOTALAN PER AREA (per bulan + ledger lintas bulan)
// =========================
$pendapatanPelanggan = (float) $pembayaranList->sum('nominal');
$komisi              = (float) $komisiList->sum('nominal_komisi');
$pengeluaran         = (float) $pengeluaranList->sum('nominal');
$totalSetoran        = (float) $setoranList->sum('nominal');

// Pendapatan bersih area ini (murni bulan ini, buat info)
$pendapatanBersih    = $pendapatanPelanggan - $komisi - $pengeluaran;

// ===============
// AMBIL DATA LEDGER PER AREA (SELURUH BULAN)
// ===============
$ledgerArea = SalesSetoranService::buildLedgerPerArea($salesId, $area->id_area);

// key bulan yg lagi dilihat (YYYY-MM)
$ymKey = sprintf('%04d-%02d', $tahun, $bulan);

// saldo per bulan dari ledger (sudah memperhitungkan setoran bulan lain)
$saldoBulan = $ledgerArea['saldoPerBulan'][$ymKey] ?? [
    'wajib'   => 0,
    'dibayar' => 0,
    'kurang'  => 0,
];

// Wajib & sisa pakai data ledger (bisa tertutup oleh setoran bulan lain)
$wajibSetor       = (float) $saldoBulan['wajib'];            // kewajiban bulan ini
$uangBelumDisetor = max((float) $saldoBulan['kurang'], 0);   // sisa setelah alokasi semua setoran

$rekapPerArea->push((object) [
    'id_area'                      => $area->id_area,
    'nama_area'                    => $area->nama_area,

    'total_pendapatan'             => $pendapatanPelanggan,
    'total_komisi'                 => $komisi,
    'total_pengeluaran'            => $pengeluaran,
    'total_setoran'                => $totalSetoran,
    'pendapatan_bersih'            => $pendapatanBersih,

    // ini sekarang hasil dari ledger (cross-bulan)
    'wajib_setor_bulan_ini'        => $wajibSetor,
    'uang_belum_disetor_bulan_ini' => $uangBelumDisetor,

    // detail buat modal
    'pembayaranList'               => $pembayaranList,
    'komisiList'                   => $komisiList,
    'pengeluaranList'              => $pengeluaranList,
    'setoranList'                  => $setoranList,
]);

        }

        return view('seles2.pembukuan.index', [
            'rekap'         => $rekapPerArea, // ← sekarang koleksi per area
            'selectedYear'  => $tahun,
            'selectedMonth' => $bulan,
        ]);
    }
}
