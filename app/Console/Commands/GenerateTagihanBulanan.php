<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Langganan;
use App\Services\TagihanService;
use Carbon\Carbon;

class GenerateTagihanBulanan extends Command
{
    protected $signature   = 'tagihan:generate-bulanan';
    protected $description = 'Generate tagihan bulanan otomatis';

    protected TagihanService $tagihanService;

    public function __construct(TagihanService $tagihanService)
    {
        parent::__construct();
        $this->tagihanService = $tagihanService;
    }

    public function handle()
    {
        $today     = now();
        $bulan     = (int) $today->format('n');  // 1..12
        $tahun     = (int) $today->format('Y');
        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfDay();

        // Ambil semua langganan aktif di awal bulan
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
                continue;
            }

            // Panggil service: dia yang urus "sudah ada atau belum"
            $tagihan = $this->tagihanService->getOrCreateForMonth($l, $tahun, $bulan);

            // Laravel punya flag ini: true kalau barusan di-create
            if ($tagihan->wasRecentlyCreated) {
                $jumlahDibuat++;
            }
        }

        $this->info("Tagihan $bulan-$tahun berhasil digenerate: $jumlahDibuat tagihan dibuat.");
        return Command::SUCCESS;
    }
}
