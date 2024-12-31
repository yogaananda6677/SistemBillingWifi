<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;
class DashboardSalesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // user yang login (tabel users)
        $now  = Carbon::now();

        $startDate = $now->copy()->startOfMonth();
        $endDate   = $now->copy()->endOfMonth();

        /**
         * AMBIL ID SALES DARI RELASI USER -> SALES
         * users.id  -> sales.user_id
         * sales.id_sales -> pelanggan.id_sales
         */
        $salesId = optional($user->sales)->id_sales; // ⬅️ INI YANG PENTING

        /**
         * BASE QUERY PELANGGAN PER SALES (UNTUK HITUNGAN)
         */
        $basePelanggan = Pelanggan::query();

        if ($salesId) {
            $basePelanggan->where('id_sales', $salesId);
        }

        // TOTAL PELANGGAN (SEMUA STATUS)
        $totalPelanggan = (clone $basePelanggan)->count();

        /**
         * 🔁 LOGIKA STATUS BARU:
         * "Baru" TIDAK lagi baca status di DB,
         * tapi berdasarkan tanggal_registrasi bulan & tahun ini.
         */
        $totalBaru = (clone $basePelanggan)
            ->whereMonth('tanggal_registrasi', $now->month)
            ->whereYear('tanggal_registrasi', $now->year)
            ->count();

        // STATUS LAIN MASIH DARI KOLOM status_pelanggan
        $totalAktif = (clone $basePelanggan)
            ->where('status_pelanggan', 'aktif')
            ->count();

        $totalBerhenti = (clone $basePelanggan)
            ->where('status_pelanggan', 'berhenti')
            ->count();

        $totalIsolir = (clone $basePelanggan)
            ->where('status_pelanggan', 'isolir')
            ->count();

        /**
         * 1. DATA PELANGGAN PER SALES (UNTUK LOOP STATUS BAYAR)
         */
        $pelangganQuery = Pelanggan::with(['langganan.tagihan', 'area']);

        if ($salesId) {
            // Hanya pelanggan milik sales yang login
            $pelangganQuery->where('id_sales', $salesId);
        }

        $pelanggan = $pelangganQuery->get();

        /**
         * 2. STATUS BAYAR PELANGGAN (SUDAH / BELUM)
         *
         * Catatan aturan baru:
         * - Sudah bayar  => tagihan terbaru LUNAS / SUDAH LUNAS.
         * - Belum bayar  => tagihan terbaru TIDAK lunas DAN
         *                   jatuh_tempo <= hari ini.
         * - Sebelum jatuh tempo => TIDAK dihitung sebagai belum bayar.
         */
        $totalSudahBayar = 0;
        $totalBelumBayar = 0;

        foreach ($pelanggan as $p) {
            $semuaTagihan = $p->langganan
                ->flatMap(fn ($l) => $l->tagihan);

            if ($semuaTagihan->isEmpty()) {
                continue;
            }

            // Ambil tagihan terbaru (berdasarkan tahun & bulan)
            $tagihanTerbaru = $semuaTagihan
                ->sortByDesc(fn ($t) => $t->tahun * 100 + $t->bulan)
                ->first();

            if (!$tagihanTerbaru) {
                continue;
            }

            $statusTagihan = strtolower($tagihanTerbaru->status_tagihan ?? '');
            $jatuhTempo    = $tagihanTerbaru->jatuh_tempo
                ? Carbon::parse($tagihanTerbaru->jatuh_tempo)
                : null;

            // Sudah bayar: lunas / sudah lunas
            if (in_array($statusTagihan, ['lunas', 'sudah lunas'])) {
                $totalSudahBayar++;
            } else {
                // Belum bayar hanya dihitung kalau SUDAH jatuh tempo
                if ($jatuhTempo && $jatuhTempo->lessThanOrEqualTo($now)) {
                    $totalBelumBayar++;
                }
                // Kalau belum jatuh tempo -> tidak dihitung sebagai belum bayar
            }
        }
        /**
         * 3. WAJIB SETOR PER SALES
         *    - Kewajiban = pembayaran - komisi - pengeluaran
         *    - Sudah setor = dari tabel setoran
         */

        // ============== HARI INI ==============
        // Pembayaran hari ini
        $pembayaranHariIni = DB::table('pembayaran as p')
            ->when($salesId, function ($q) use ($salesId) {
                $q->where('p.id_sales', $salesId);   // ⬅️ GANTI DI SINI
            })
            ->whereDate('p.tanggal_bayar', $now->toDateString())
            ->sum('p.nominal');


        // Komisi hari ini
        $komisiHariIni = DB::table('transaksi_komisi as tk')
            ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
            ->when($salesId, function ($q) use ($salesId) {
                $q->where('tk.id_sales', $salesId);   // ⬅️ GANTI DI SINI
            })
            ->whereDate('p.tanggal_bayar', $now->toDateString())
            ->sum('tk.nominal_komisi');

        // Pengeluaran hari ini
        $pengeluaranHariIni = DB::table('pengeluaran as pg')
            ->when($salesId, function ($q) use ($salesId) {
                $q->where('pg.id_sales', $salesId);
            })
            ->where('pg.status_approve', 'approved')
            ->whereDate('pg.tanggal_approve', $now->toDateString())
            ->sum('pg.nominal');

        // Kewajiban setor hari ini
        $wajibSetorHariIni = $pembayaranHariIni - $komisiHariIni - $pengeluaranHariIni;

        // Setoran hari ini
        $sudahSetorHariIni = DB::table('setoran as st')
            ->when($salesId, function ($q) use ($salesId) {
                $q->where('st.id_sales', $salesId);
            })
            ->whereDate('st.tanggal_setoran', $now->toDateString())
            ->sum('st.nominal');

        // ============== BULAN INI ==============
        // Pembayaran bulan ini
        $pembayaranBulanIni = DB::table('pembayaran as p')
            ->when($salesId, function ($q) use ($salesId) {
                $q->where('p.id_sales', $salesId);   // ⬅️ GANTI DI SINI
            })
            ->whereYear('p.tanggal_bayar', $now->year)
            ->whereMonth('p.tanggal_bayar', $now->month)
            ->sum('p.nominal');


        // Komisi bulan ini
        $komisiBulanIni = DB::table('transaksi_komisi as tk')
            ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
            ->when($salesId, function ($q) use ($salesId) {
                $q->where('tk.id_sales', $salesId);   // ⬅️ atau p.id_sales
            })
            ->whereYear('p.tanggal_bayar', $now->year)
            ->whereMonth('p.tanggal_bayar', $now->month)
            ->sum('tk.nominal_komisi');


        // Pengeluaran bulan ini
        $pengeluaranBulanIni = DB::table('pengeluaran as pg')
            ->when($salesId, function ($q) use ($salesId) {
                $q->where('pg.id_sales', $salesId);
            })
            ->where('pg.status_approve', 'approved')
            ->whereYear('pg.tanggal_approve', $now->year)
            ->whereMonth('pg.tanggal_approve', $now->month)
            ->sum('pg.nominal');

        // Kewajiban setor bulan ini
        $wajibSetorBulanIni = $pembayaranBulanIni - $komisiBulanIni - $pengeluaranBulanIni;

        // Setoran bulan ini (pakai kolom tahun/bulan di tabel setoran)
        $sudahSetorBulanIni = DB::table('setoran as st')
            ->when($salesId, function ($q) use ($salesId) {
                $q->where('st.id_sales', $salesId);
            })
            ->where('st.tahun', $now->year)
            ->where('st.bulan', $now->month)
            ->sum('st.nominal');
// Untuk kebutuhan tampilan (filter bulan/tahun)
$selectedMonth = $now->month;
$selectedYear  = $now->year;

return view('seles2.dashboard.index', [
    'wajibSetorHariIni'   => $wajibSetorHariIni,
    'sudahSetorHariIni'   => $sudahSetorHariIni,
    'wajibSetorBulanIni'  => $wajibSetorBulanIni,
    'sudahSetorBulanIni'  => $sudahSetorBulanIni,

    'totalPelanggan'      => $totalPelanggan,
    'totalAktif'          => $totalAktif,
    'totalBaru'           => $totalBaru,
    'totalBerhenti'       => $totalBerhenti,
    'totalIsolir'         => $totalIsolir,
    'totalSudahBayar'     => $totalSudahBayar,
    'totalBelumBayar'     => $totalBelumBayar,

    'pembayaranHariIni'   => $pembayaranHariIni,
    'pembayaranBulanIni'  => $pembayaranBulanIni,

    'startDate'           => $startDate,
    'endDate'             => $endDate,

    'selectedMonth'       => $selectedMonth,
    'selectedYear'        => $selectedYear,
]);

    }
}
