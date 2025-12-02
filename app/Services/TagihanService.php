<?php

namespace App\Services;

use App\Models\Langganan;
use App\Models\Tagihan;
use Carbon\Carbon;

class TagihanService
{
    /**
     * Cek apakah bulan tertentu MASIH boleh dibuatkan tagihan
     * - status_langganan harus 'aktif'
     * - kalau sudah isolir / berhenti, bulan >= bulan isolir/berhenti TIDAK boleh dibuat
     */
    public function bolehDibuatUntukBulan(Langganan $langganan, int $tahun, int $bulan): bool
    {
        $target = Carbon::create($tahun, $bulan, 1)->startOfMonth();

        // Hanya langganan aktif yang boleh
        if ($langganan->status_langganan !== 'aktif') {
            return false;
        }

        // Kalau ada tanggal berhenti -> bulan mulai berhenti TIDAK boleh ditagih
        if ($langganan->tanggal_berhenti) {
            $berhenti = Carbon::parse($langganan->tanggal_berhenti)->startOfMonth();
            if ($target->greaterThanOrEqualTo($berhenti)) {
                return false;
            }
        }

        // Kalau ada tanggal isolir -> bulan mulai isolir TIDAK boleh ditagih
        if ($langganan->tanggal_isolir) {
            $isolir = Carbon::parse($langganan->tanggal_isolir)->startOfMonth();
            if ($target->greaterThanOrEqualTo($isolir)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ambil / buat tagihan bulan tertentu.
     *
     * @param  bool  $createdBecausePayment
     *         TRUE  → tagihan dibuat saat proses pembayaran (bisa dihapus kalau payment dihapus)
     *         FALSE → tagihan dibuat oleh proses lain (cron bulanan, dsb) → tidak dihapus ketika payment dihapus
     */
    public function getOrCreateForMonth(
        Langganan $langganan,
        int $tahun,
        int $bulan,
        bool $createdBecausePayment = false
    ): ?Tagihan {
        // 1. Kalau nggak boleh ditagihkan, balikin existing aja kalau ADA, tapi jangan create baru
        if (! $this->bolehDibuatUntukBulan($langganan, $tahun, $bulan)) {
            return Tagihan::where('id_langganan', $langganan->id_langganan)
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->first();
        }

        // 2. Cek kalau sudah ada
        $existing = Tagihan::where('id_langganan', $langganan->id_langganan)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->first();

        if ($existing) {
            return $existing;
        }

        // 3. Hitung nilai tagihan dari paket
        $paket        = $langganan->paket;
        $hargaDasar   = $paket->harga_dasar   ?? 0;
        $ppnNominal   = $paket->ppn_nominal   ?? 0;
        $totalTagihan = $paket->harga_total   ?? ($hargaDasar + $ppnNominal);

        // 4. Set default jatuh tempo (misal tgl 10 bulan tsb – silakan sesuaikan)
        $jatuhTempo = Carbon::create($tahun, $bulan, 10)->endOfDay();

        return Tagihan::create([
            'id_langganan'          => $langganan->id_langganan,
            'bulan'                 => $bulan,
            'tahun'                 => $tahun,
            'harga_dasar'           => $hargaDasar,
            'ppn_nominal'           => $ppnNominal,
            'total_tagihan'         => $totalTagihan,
            'status_tagihan'        => 'belum lunas',
            'jatuh_tempo'           => $jatuhTempo,
            'dibuat_otomatis_bayar' => $createdBecausePayment,
        ]);
    }

    /**
     * Dipakai oleh CRON: generate tagihan untuk SEMUA langganan yang masih aktif.
     * Tagihan hasil proses ini TIDAK dianggap "dibuat karena pembayaran".
     */
    public function generateForAllActive(int $tahun, int $bulan): void
    {
        Langganan::with('paket')
            ->where('status_langganan', 'aktif')
            ->chunkById(500, function ($chunk) use ($tahun, $bulan) {
                foreach ($chunk as $langganan) {
                    if ($this->bolehDibuatUntukBulan($langganan, $tahun, $bulan)) {
                        // createdBecausePayment = false (default)
                        $this->getOrCreateForMonth($langganan, $tahun, $bulan, false);
                    }
                }
            });
    }
}
