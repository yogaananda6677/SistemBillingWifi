<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SalesSetoranService
{
    /**
     * Bangun ledger (pembukuan) lengkap untuk 1 sales.
     *
     * Return:
     *  - monthlyKewajiban: ['YYYY-MM' => ['pendapatan','komisi','pengeluaran','wajib']]
     *  - setorans: Collection setoran (dengan nama admin)
     *  - allocDetail: [id_setoran => [ ['periode'=>'YYYY-MM','nominal'=>..., 'lebih'=>bool], ... ]]
     *  - saldoPerBulan: ['YYYY-MM' => ['wajib','dibayar','kurang']]
     *  - totalPendapatan, totalKomisi, totalPengeluaran
     *  - totalWajib, totalSetoran, saldoGlobal (totalSetoran - totalWajib)
     */
    public static function buildLedger(int $salesId): array
    {
        // ==========================
        // 1. HITUNG KEWAJIBAN / BULAN
        // ==========================

        // Pendapatan per bulan
        $pendapatanPerBulan = DB::table('pembayaran as p')
            ->selectRaw('YEAR(p.tanggal_bayar) as tahun, MONTH(p.tanggal_bayar) as bulan, SUM(p.nominal) as total')
            ->where('p.id_sales', $salesId)
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->tahun, $row->bulan);
            });

        // Komisi per bulan
        $komisiPerBulan = DB::table('transaksi_komisi as tk')
            ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
            ->selectRaw('YEAR(p.tanggal_bayar) as tahun, MONTH(p.tanggal_bayar) as bulan, SUM(tk.nominal_komisi) as total')
            ->where('p.id_sales', $salesId)
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->tahun, $row->bulan);
            });

        // Pengeluaran approved per bulan
        $pengeluaranPerBulan = DB::table('pengeluaran as pg')
            ->selectRaw('YEAR(pg.tanggal_approve) as tahun, MONTH(pg.tanggal_approve) as bulan, SUM(pg.nominal) as total')
            ->where('pg.id_sales', $salesId)
            ->where('pg.status_approve', 'approved')
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->tahun, $row->bulan);
            });

        // Gabung semua bulan yang pernah muncul
        $allMonthKeys = collect()
            ->merge($pendapatanPerBulan->keys())
            ->merge($komisiPerBulan->keys())
            ->merge($pengeluaranPerBulan->keys())
            ->unique()
            ->values()
            ->sort()
            ->all();

        $monthlyKewajiban = []; // 'YYYY-MM' => [...]

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

        // ==========================
        // 2. AMBIL SEMUA SETORAN SALES INI
        // ==========================

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

        // ==========================
        // 3. ALOKASI SETORAN KE BULAN-BULAN
        //    - Nutup bulan paling lama dulu
        //    - Sisa jadi "kelebihan" di bulan setoran
        // ==========================

        $terpenuhi = [];
        foreach ($monthlyKewajiban as $ym => $data) {
            $terpenuhi[$ym] = 0;
        }

        $allocDetail = []; // id_setoran => [ [periode,nominal,lebih], ... ]

        $monthKeysSorted = array_keys($monthlyKewajiban);
        sort($monthKeysSorted); // urut naik

        foreach ($setorans as $st) {
            $sisaSetor = (float) $st->nominal;
            $setorYm   = substr($st->tanggal_setoran, 0, 7); // "YYYY-MM"

            foreach ($monthKeysSorted as $ym) {
                // Tidak alokasikan ke bulan setelah tanggal setoran
                if ($ym > $setorYm) {
                    break;
                }

                $wajib  = $monthlyKewajiban[$ym]['wajib'] ?? 0;
                $sudah  = $terpenuhi[$ym] ?? 0;
                $kurang = $wajib - $sudah;

                if ($kurang <= 0) {
                    continue; // kewajiban bulan ini sudah penuh
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

            // Sisa setoran yang tidak dibutuhkan utk nutup kewajiban sebelumnya => kelebihan
            if ($sisaSetor > 0) {
                $allocDetail[$st->id_setoran][] = [
                    'periode' => $setorYm,
                    'nominal' => $sisaSetor,
                    'lebih'   => true,
                ];
            }
        }

        // ==========================
        // 4. SALDO PER BULAN + TOTAL
        // ==========================

        $saldoPerBulan = [];
        $totalPendapatan = 0;
        $totalKomisi = 0;
        $totalPengeluaran = 0;
        $totalWajib = 0;

        foreach ($monthlyKewajiban as $ym => $data) {
            $wajib = $data['wajib'];
            $dibayar = $terpenuhi[$ym] ?? 0;

            $saldoPerBulan[$ym] = [
                'wajib'     => $wajib,
                'dibayar'   => $dibayar,
                'kurang'    => $wajib - $dibayar, // bisa negatif kalau ada logika lain, skrg harusnya <= 0
            ];

            $totalPendapatan  += $data['pendapatan'];
            $totalKomisi      += $data['komisi'];
            $totalPengeluaran += $data['pengeluaran'];
            $totalWajib       += $wajib;
        }

        $totalSetoran = (float) $setorans->sum('nominal');
        $saldoGlobal  = $totalSetoran - $totalWajib; // + = kelebihan, - = masih kurang

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
}
