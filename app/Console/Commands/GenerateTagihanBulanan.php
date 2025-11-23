<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Langganan;
use App\Models\Tagihan;
use Carbon\Carbon;

class GenerateTagihanBulanan extends Command
{
    protected $signature = 'tagihan:generate-bulanan';
    protected $description = 'Generate tagihan bulanan otomatis';

    public function handle()
    {
        $bulan = now()->format('m');
        $tahun = now()->format('Y');

        // Cegah generate ganda
        if (Tagihan::where('bulan', $bulan)->where('tahun', $tahun)->exists()) {
            $this->info("Tagihan bulan $bulan-$tahun sudah digenerate.");
            return Command::SUCCESS;
        }

        $langganan = Langganan::with(['paket', 'pelanggan'])
            ->where('status_langganan', 'aktif')
            ->get();

        $ppnSetting = \App\Models\Ppn::first();

        foreach ($langganan as $l) {

            if (!$l->paket || !$l->pelanggan) {
                // skip jika paket atau pelanggan hilang
                continue;
            }

            $tanggalPasang = $l->pelanggan->tanggal_registrasi;
            $bulanTagihan = now()->addMonth()->month; // bulan depan
            $tahunTagihan = now()->addMonth()->year;  // sesuaikan tahun jika Des → Jan

            $day = Carbon::parse($tanggalPasang)->day;

            $jatuhTempo = Carbon::create($tahunTagihan, $bulanTagihan, 1)
                                ->day($day)
                                ->min(Carbon::create($tahunTagihan, $bulanTagihan, 1)->endOfMonth());

            $harga = $l->paket->harga_dasar;
            $ppn   = $harga * $ppnSetting->presentase_ppn;
            $total = $harga + $ppn;

            Tagihan::create([
                'id_langganan'   => $l->id_langganan,
                'bulan'          => $bulan,
                'tahun'          => $tahun,
                'harga_dasar'    => $harga,
                'ppn_nominal'    => $ppn,
                'total_tagihan'  => $total,
                'status_tagihan' => 'belum lunas',
                'jatuh_tempo'    => $jatuhTempo,
            ]);
        }

        $this->info("Tagihan $bulan-$tahun berhasil digenerate!");
        return Command::SUCCESS;
    }
}
