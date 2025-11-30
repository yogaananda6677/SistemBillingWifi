<?php

namespace App\Services;

use App\Models\Langganan;
use App\Models\Tagihan;
use Carbon\Carbon;

class TagihanService
{
public function getOrCreateForMonth(Langganan $langganan, int $tahun, int $bulan): ?Tagihan
{
    $current = Carbon::create($tahun, $bulan, 1)->startOfDay();

    $mulaiMonth = Carbon::parse($langganan->tanggal_mulai)->startOfMonth();

    $berhentiMonth = null;
    if ($langganan->tanggal_berhenti) {
        $berhentiMonth = Carbon::parse($langganan->tanggal_berhenti)->startOfMonth();
    }

    // 1. Kalau sudah ada tagihan bulan ini → pakai itu saja
    $existing = $langganan->tagihan()
        ->where('tahun', $tahun)
        ->where('bulan', $bulan)
        ->first();

    if ($existing) {
        return $existing;
    }

    // 2. Di luar periode langganan → jangan bikin tagihan
    if ($current->lt($mulaiMonth)) {
        return null;
    }

    if ($berhentiMonth && $current->gt($berhentiMonth)) {
        return null;
    }

    // 3. Dalam periode aktif → create tagihan baru
    $paket        = $langganan->paket;
    $hargaDasar   = $paket->harga_dasar ?? 0;
    $ppnNominal   = (int) ($paket->ppn_nominal ?? 0);
    $totalTagihan = $paket->harga_total ?? ($hargaDasar + $ppnNominal);

    $jatuhTempo   = $current->copy()->day(10)->endOfDay(); // contoh

    return $langganan->tagihan()->create([
        'bulan'          => $bulan,
        'tahun'          => $tahun,
        'harga_dasar'    => $hargaDasar,
        'ppn_nominal'    => $ppnNominal,
        'total_tagihan'  => $totalTagihan,
        'status_tagihan' => 'belum lunas',
        'jatuh_tempo'    => $jatuhTempo,
    ]);
}
}