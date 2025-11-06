<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SalesSetoranService
{
    /**
     * Ledger GLOBAL (semua area) untuk 1 sales.
     * Dipakai untuk rekap keseluruhan.
     */
    public static function buildLedger(int $salesId): array
    {
        // ==========================
        // 1. KEWAJIBAN PER BULAN (GLOBAL, semua area)
        // ==========================

        // PENDAPATAN per bulan (berdasarkan pembayaran sales ini)
        $pendapatanPerBulan = DB::table('pembayaran as p')
            ->selectRaw('YEAR(p.tanggal_bayar) as tahun, MONTH(p.tanggal_bayar) as bulan, SUM(p.nominal) as total')
            ->where('p.id_sales', $salesId)
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->tahun, $row->bulan);
            });

        // KOMISI per bulan (berdasarkan pembayaran yang dilakukan sales ini)
        $komisiPerBulan = DB::table('transaksi_komisi as tk')
            ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
            ->selectRaw('YEAR(p.tanggal_bayar) as tahun, MONTH(p.tanggal_bayar) as bulan, SUM(tk.nominal_komisi) as total')
            ->where('p.id_sales', $salesId)
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->tahun, $row->bulan);
            });

        // PENGELUARAN approved per bulan (semua area sales ini)
        $pengeluaranPerBulan = DB::table('pengeluaran as pg')
            ->selectRaw('YEAR(pg.tanggal_approve) as tahun, MONTH(pg.tanggal_approve) as bulan, SUM(pg.nominal) as total')
            ->where('pg.id_sales', $salesId)
            ->where('pg.status_approve', 'approved')
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->tahun, $row->bulan);
            });

        // Gabungkan semua bulan yang pernah muncul di pendapatan/komisi/pengeluaran
        $allMonthKeys = collect()
            ->merge($pendapatanPerBulan->keys())
            ->merge($komisiPerBulan->keys())
            ->merge($pengeluaranPerBulan->keys())
            ->unique()
            ->values()
            ->sort()
            ->all();

        $monthlyKewajiban = [];

        foreach ($allMonthKeys as $ym) {
            $pendapatan  = (float) ($pendapatanPerBulan[$ym]->total ?? 0);
            $komisi      = (float) ($komisiPerBulan[$ym]->total ?? 0);
            $pengeluaran = (float) ($pengeluaranPerBulan[$ym]->total ?? 0);

            // Rumus kewajiban: pendapatan - komisi - pengeluaran
            $wajib = $pendapatan - $komisi - $pengeluaran;

            $monthlyKewajiban[$ym] = [
                'pendapatan'  => $pendapatan,
                'komisi'      => $komisi,
                'pengeluaran' => $pengeluaran,
                'wajib'       => $wajib,
            ];
        }

        // ==========================
        // 2. AMBIL SETORAN (SEMUA AREA SALES INI)
        // ==========================
        [$setorans, $allocDetail, $terpenuhi] = self::allocateSetoranForSales($salesId, null, $monthlyKewajiban);

        // ==========================
        // 3. HITUNG SALDO & TOTAL
        // ==========================
        $saldoPerBulan = [];
        $totalPendapatan   = 0;
        $totalKomisi       = 0;
        $totalPengeluaran  = 0;
        $totalWajib        = 0;

        foreach ($monthlyKewajiban as $ym => $data) {
            $wajib   = $data['wajib'];
            $dibayar = $terpenuhi[$ym] ?? 0;

            $saldoPerBulan[$ym] = [
                'wajib'   => $wajib,
                'dibayar' => $dibayar,
                'kurang'  => $wajib - $dibayar,
            ];

            $totalPendapatan  += $data['pendapatan'];
            $totalKomisi      += $data['komisi'];
            $totalPengeluaran += $data['pengeluaran'];
            $totalWajib       += $wajib;
        }

        $totalSetoran = (float) $setorans->sum('nominal');
        // + = kelebihan setoran, - = masih kurang
        $saldoGlobal  = $totalSetoran - $totalWajib;

        return [
            'monthlyKewajiban'  => $monthlyKewajiban,
            'setorans'          => $setorans,
            'allocDetail'       => $allocDetail,
            'saldoPerBulan'     => $saldoPerBulan,
            'totalPendapatan'   => $totalPendapatan,
            'totalKomisi'       => $totalKomisi,
            'totalPengeluaran'  => $totalPengeluaran,
            'totalWajib'        => $totalWajib,
            'totalSetoran'      => $totalSetoran,
            'saldoGlobal'       => $saldoGlobal,
        ];
    }

    /**
     * Ledger PER AREA untuk 1 sales.
     * (Ini yang dipakai di PembukuanController untuk pembukuan per sales–area.)
     */
    public static function buildLedgerPerArea(int $salesId, int $areaId): array
    {
        // ==========================
        // 1. KEWAJIBAN PER BULAN PER AREA
        // ==========================

        // PENDAPATAN per bulan (filter lewat pelanggan.id_area)
        $pendapatanPerBulan = DB::table('pembayaran as p')
            ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->selectRaw('YEAR(p.tanggal_bayar) as tahun, MONTH(p.tanggal_bayar) as bulan, SUM(p.nominal) as total')
            ->where('p.id_sales', $salesId)
            ->where('pl.id_area', $areaId)
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->tahun, $row->bulan);
            });

        // KOMISI per bulan (berdasarkan pembayaran yang pelanggannya area ini)
        $komisiPerBulan = DB::table('transaksi_komisi as tk')
            ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
            ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->selectRaw('YEAR(p.tanggal_bayar) as tahun, MONTH(p.tanggal_bayar) as bulan, SUM(tk.nominal_komisi) as total')
            ->where('tk.id_sales', $salesId)
            ->where('pl.id_area', $areaId)
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->tahun, $row->bulan);
            });

        // PENGELUARAN approved per bulan untuk sales+area ini
        $pengeluaranPerBulan = DB::table('pengeluaran as pg')
            ->selectRaw('YEAR(pg.tanggal_approve) as tahun, MONTH(pg.tanggal_approve) as bulan, SUM(pg.nominal) as total')
            ->where('pg.id_sales', $salesId)
            ->where('pg.id_area', $areaId)
            ->where('pg.status_approve', 'approved')
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->tahun, $row->bulan);
            });

        // Gabungkan semua bulan
        $allMonthKeys = collect()
            ->merge($pendapatanPerBulan->keys())
            ->merge($komisiPerBulan->keys())
            ->merge($pengeluaranPerBulan->keys())
            ->unique()
            ->values()
            ->sort()
            ->all();

        $monthlyKewajiban = [];

        foreach ($allMonthKeys as $ym) {
            $pendapatan  = (float) ($pendapatanPerBulan[$ym]->total ?? 0);
            $komisi      = (float) ($komisiPerBulan[$ym]->total ?? 0);
            $pengeluaran = (float) ($pengeluaranPerBulan[$ym]->total ?? 0);

            // pendapatan - komisi - pengeluaran
            $wajib = $pendapatan - $komisi - $pengeluaran;

            $monthlyKewajiban[$ym] = [
                'pendapatan'  => $pendapatan,
                'komisi'      => $komisi,
                'pengeluaran' => $pengeluaran,
                'wajib'       => $wajib,
            ];
        }

        // ==========================
        // 2. SETORAN khusus sales+area ini
        // ==========================
        [$setorans, $allocDetail, $terpenuhi] = self::allocateSetoranForSales($salesId, $areaId, $monthlyKewajiban);

        // ==========================
        // 3. SALDO & TOTAL
        // ==========================
        $saldoPerBulan = [];
        $totalPendapatan   = 0;
        $totalKomisi       = 0;
        $totalPengeluaran  = 0;
        $totalWajib        = 0;

        foreach ($monthlyKewajiban as $ym => $data) {
            $wajib   = $data['wajib'];
            $dibayar = $terpenuhi[$ym] ?? 0;

            $saldoPerBulan[$ym] = [
                'wajib'   => $wajib,
                'dibayar' => $dibayar,
                'kurang'  => $wajib - $dibayar,
            ];

            $totalPendapatan  += $data['pendapatan'];
            $totalKomisi      += $data['komisi'];
            $totalPengeluaran += $data['pengeluaran'];
            $totalWajib       += $wajib;
        }

        $totalSetoran = (float) $setorans->sum('nominal');
        $saldoGlobal  = $totalSetoran - $totalWajib;

        return [
            'monthlyKewajiban'  => $monthlyKewajiban,
            'setorans'          => $setorans,
            'allocDetail'       => $allocDetail,
            'saldoPerBulan'     => $saldoPerBulan,
            'totalPendapatan'   => $totalPendapatan,
            'totalKomisi'       => $totalKomisi,
            'totalPengeluaran'  => $totalPengeluaran,
            'totalWajib'        => $totalWajib,
            'totalSetoran'      => $totalSetoran,
            'saldoGlobal'       => $saldoGlobal,
        ];
    }

    /**
     * Helper: ambil setoran + lakukan alokasi FIFO
     * - Kalau $areaId = null → semua area sales tsb (global)
     * - Kalau $areaId != null → hanya setoran untuk area tsb
     *
     * @param  int        $salesId
     * @param  int|null   $areaId
     * @param  array      $monthlyKewajiban  ['YYYY-MM' => ['wajib' => ... , ...], ...]
     * @return array      [$setorans, $allocDetail, $terpenuhi]
     */
    protected static function allocateSetoranForSales(int $salesId, ?int $areaId, array $monthlyKewajiban): array
    {
        // Ambil data setoran + admin penerima
        $setorans = DB::table('setoran as st')
            ->join('admins as ad', 'ad.id_admin', '=', 'st.id_admin')
            ->join('users as ua', 'ua.id', '=', 'ad.user_id')
            ->select(
                'st.id_setoran',
                'st.id_sales',
                'st.id_area',
                'st.id_admin',
                'st.tanggal_setoran',
                'st.nominal',
                'st.catatan',
                'ua.name as nama_admin'
            )
            ->where('st.id_sales', $salesId)
            ->when(!is_null($areaId), function ($q) use ($areaId) {
                $q->where('st.id_area', $areaId);
            })
            ->orderBy('st.tanggal_setoran', 'asc')
            ->get();

        // Inisialisasi berapa kewajiban yang sudah terpenuhi per bulan
        $terpenuhi = [];
        foreach ($monthlyKewajiban as $ym => $data) {
            $terpenuhi[$ym] = 0.0;
        }

        $allocDetail = []; // [id_setoran => [ [periode, nominal, lebih], ... ]]

        // Urutan bulan dari paling lama
        $monthKeysSorted = array_keys($monthlyKewajiban);
        sort($monthKeysSorted);

        foreach ($setorans as $st) {
            $sisaSetor = (float) $st->nominal;

            // Setoran boleh dipakai untuk menutup kewajiban dari bulan paling lama dulu (FIFO)
            foreach ($monthKeysSorted as $ym) {
                $wajib  = $monthlyKewajiban[$ym]['wajib'] ?? 0;
                $sudah  = $terpenuhi[$ym] ?? 0;
                $kurang = $wajib - $sudah;

                if ($kurang <= 0) {
                    // bulan ini sudah lunas
                    continue;
                }

                if ($sisaSetor <= 0) {
                    // uang setoran sudah habis
                    break;
                }

                // Alokasikan sebagian/seluruh sisa setoran ke bulan ini
                $alok = min($sisaSetor, $kurang);

                $terpenuhi[$ym] = $sudah + $alok;
                $sisaSetor      -= $alok;

                $allocDetail[$st->id_setoran][] = [
                    'periode' => $ym,
                    'nominal' => $alok,
                    'lebih'   => false,
                ];
            }

            // Jika masih ada sisa setoran setelah semua kewajiban lunas → dianggap kelebihan (lebih)
            if ($sisaSetor > 0) {
                $allocDetail[$st->id_setoran][] = [
                    // ditandai di bulan (YYYY-MM) sesuai tanggal setoran
                    'periode' => substr($st->tanggal_setoran, 0, 7),
                    'nominal' => $sisaSetor,
                    'lebih'   => true,
                ];
            }
        }

        return [$setorans, $allocDetail, $terpenuhi];
    }
}
