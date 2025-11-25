<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Langganan;
use App\Models\Tagihan;
use Carbon\Carbon;

class GenerateTagihanBulanan extends Command
{
    protected $signature   = 'tagihan:generate-bulanan';
    protected $description = 'Generate tagihan bulanan otomatis';

    public function handle()
    {
        $today     = now();
        $bulan     = (int) $today->format('n');  // 1..12
        $tahun     = (int) $today->format('Y');
        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfDay();

        // Jatuh tempo fix tanggal 10 bulan berjalan (silakan ubah kalau mau)
        $jatuhTempo = Carbon::create($tahun, $bulan, 10, 23, 59, 59);

        // Ambil semua langganan aktif di awal bulan, dan belum berhenti sebelum awal bulan
        $langganan = Langganan::with(['paket', 'pelanggan'])
            ->where('status_langganan', 'aktif')
            ->whereDate('tanggal_mulai', '<=', $awalBulan->toDateString())
            ->where(function ($q) use ($awalBulan) {
                $q->whereNull('tanggal_berhenti')
                  ->orWhereDate('tanggal_berhenti', '>=', $awalBulan->toDateString());
            })
            ->get();

        $jumlahDibuat = 0;

        foreach ($langganan as $l) {
            if (!$l->paket || !$l->pelanggan) {
                // skip jika paket atau pelanggan hilang
                continue;
            }

            // CEK: sudah punya tagihan bulan ini belum?
            $sudahAda = Tagihan::where('id_langganan', $l->id_langganan)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->exists();

            if ($sudahAda) {
                continue; // jangan buat dobel
            }

            // PPN & total ambil langsung dari paket
            $harga = $l->paket->harga_dasar;
            $ppn   = $l->paket->ppn_nominal;
            $total = $l->paket->harga_total;

            Tagihan::create([
                'id_langganan'   => $l->id_langganan,
                'bulan'          => $bulan,     // bulan berjalan
                'tahun'          => $tahun,
                'harga_dasar'    => $harga,
                'ppn_nominal'    => $ppn,
                'total_tagihan'  => $total,
                'status_tagihan' => 'belum lunas',
                'jatuh_tempo'    => $jatuhTempo,
            ]);

            $jumlahDibuat++;
        }

        $this->info("Tagihan $bulan-$tahun berhasil digenerate: $jumlahDibuat tagihan dibuat.");
        return Command::SUCCESS;
    }
}
