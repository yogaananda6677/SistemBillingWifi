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
                'setorans'      => collect(),
                'allocDetail'   => [],
                'monthlyKewajiban' => [],
            ]);
        }

        $salesId = $sales->id_sales;

        /*
        |--------------------------------------------------------------------------
        | 1. HITUNG KEWAJIBAN PER BULAN (PENDAPATAN BERSIH)
        |    Wajib setor = pendapatan pelanggan - komisi - pengeluaran (approved)
        |--------------------------------------------------------------------------
        */

        // Pendapatan per bulan (pembayaran pelanggan yang ditangani sales ini)
        $pendapatanPerBulan = DB::table('pembayaran as p')
            ->selectRaw('YEAR(p.tanggal_bayar) as tahun, MONTH(p.tanggal_bayar) as bulan, SUM(p.nominal) as total')
            ->where('p.id_sales', $salesId)
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->tahun, $row->bulan);
            });

        // Komisi per bulan (berdasarkan tanggal bayar)
        $komisiPerBulan = DB::table('transaksi_komisi as tk')
            ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
            ->selectRaw('YEAR(p.tanggal_bayar) as tahun, MONTH(p.tanggal_bayar) as bulan, SUM(tk.nominal_komisi) as total')
            ->where('p.id_sales', $salesId)
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->tahun, $row->bulan);
            });

        // Pengeluaran approved per bulan (tanggal_approve)
        $pengeluaranPerBulan = DB::table('pengeluaran as pg')
            ->selectRaw('YEAR(pg.tanggal_approve) as tahun, MONTH(pg.tanggal_approve) as bulan, SUM(pg.nominal) as total')
            ->where('pg.id_sales', $salesId)
            ->where('pg.status_approve', 'approved')
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->tahun, $row->bulan);
            });

        // Gabung semua bulan yang muncul
        $allMonthKeys = collect()
            ->merge($pendapatanPerBulan->keys())
            ->merge($komisiPerBulan->keys())
            ->merge($pengeluaranPerBulan->keys())
            ->unique()
            ->values()
            ->sort()
            ->all();

        $monthlyKewajiban = [];     // [ 'YYYY-MM' => ['wajib' => ..., 'pendapatan'=>..., 'komisi'=>..., 'pengeluaran'=>...] ]

        foreach ($allMonthKeys as $ym) {
            $pendapatan  = (float) ($pendapatanPerBulan[$ym]->total ?? 0);
            $komisi      = (float) ($komisiPerBulan[$ym]->total ?? 0);
            $pengeluaran = (float) ($pengeluaranPerBulan[$ym]->total ?? 0);

            $wajib = $pendapatan - $komisi - $pengeluaran;

            $monthlyKewajiban[$ym] = [
                'pendapatan'  => $pendapatan,
                'komisi'      => $komisi,
                'pengeluaran' => $pengeluaran,
                'wajib'       => $wajib,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 2. AMBIL SEMUA SETORAN SALES INI + ADMIN PENERIMA
        |--------------------------------------------------------------------------
        */

        $setorans = DB::table('setoran as st')
            ->join('admins as a', 'a.id_admin', '=', 'st.id_admin')
            ->join('users as ua', 'ua.id', '=', 'a.user_id')
            ->select(
                'st.id_setoran',
                'st.id_sales',
                'st.id_admin',
                'st.tanggal_setoran',
                'st.nominal',
                'st.catatan',
                'ua.name as nama_admin'
            )
            ->where('st.id_sales', $salesId)
            ->orderBy('st.tanggal_setoran', 'asc')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 3. ALOKASI TIAP SETORAN KE BULAN-BULAN YANG MASIH KURANG
        |    - Prinsip: uang setor selalu nutup kewajiban bulan paling lama (terkecil) yang masih kurang.
        |    - Tidak mengalokasikan ke bulan yang "belum lewat" (ym > ym setoran)
        |--------------------------------------------------------------------------
        */

        // Tracking berapa kewajiban bulan yang sudah tertutup
        $terpenuhi = [];
        foreach ($monthlyKewajiban as $ym => $data) {
            $terpenuhi[$ym] = 0;
        }

        $allocDetail = []; // [id_setoran => [ ['periode'=>'YYYY-MM','nominal'=>..., 'lebih'=>bool|null], ... ]]

        $monthKeysSorted = array_keys($monthlyKewajiban);
        sort($monthKeysSorted); // dari bulan paling awal

        foreach ($setorans as $st) {
            $sisaSetor = (float) $st->nominal;
            $setorYm   = substr($st->tanggal_setoran, 0, 7); // "YYYY-MM"

            foreach ($monthKeysSorted as $ym) {
                // Jangan alokasikan ke bulan setelah tanggal setoran
                if ($ym > $setorYm) {
                    break;
                }

                $wajib  = $monthlyKewajiban[$ym]['wajib'] ?? 0;
                $sudah  = $terpenuhi[$ym] ?? 0;
                $kurang = $wajib - $sudah;

                if ($kurang <= 0) {
                    continue; // kewajiban bulan ini sudah penuh / kelebihan
                }

                if ($sisaSetor <= 0) {
                    break;
                }

                $alok = min($sisaSetor, $kurang);

                $terpenuhi[$ym] = $sudah + $alok;
                $sisaSetor      -= $alok;

                $allocDetail[$st->id_setoran][] = [
                    'periode' => $ym,
                    'nominal' => $alok,
                    'lebih'   => false,
                ];

                if ($sisaSetor <= 0) {
                    break;
                }
            }

            // Jika masih ada sisa setoran setelah semua kewajiban bulan <= ym setoran penuh,
            // anggap sebagai "kelebihan" yang ditempatkan di bulan setoran itu sendiri.
            if ($sisaSetor > 0) {
                $allocDetail[$st->id_setoran][] = [
                    'periode' => $setorYm,
                    'nominal' => $sisaSetor,
                    'lebih'   => true,
                ];
            }
        }

        return view('seles2.setoran.index', [
            'setorans'        => $setorans,
            'allocDetail'     => $allocDetail,
            'monthlyKewajiban'=> $monthlyKewajiban,
        ]);
    }
}
