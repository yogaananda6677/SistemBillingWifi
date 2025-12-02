<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TagihanService;
use Carbon\Carbon;

class GenerateMonthlyTagihan extends Command
{
    // NAMA YANG DIPAKAI DI ARTISAN & SCHEDULE
    protected $signature   = 'tagihan:generate {tahun?} {bulan?}';

    protected $description = 'Generate tagihan bulanan untuk semua langganan aktif';

    protected TagihanService $tagihanService;

    public function __construct(TagihanService $tagihanService)
    {
        parent::__construct();
        $this->tagihanService = $tagihanService;
    }

    public function handle(): int
    {
        $tahun = $this->argument('tahun');
        $bulan = $this->argument('bulan');

        if (!$tahun || !$bulan) {
            $now   = Carbon::now();
            $tahun = $now->year;
            $bulan = $now->month;
        }

        $this->info("Generate tagihan untuk {$bulan}-{$tahun} ...");

        $this->tagihanService->generateForAllActive((int)$tahun, (int)$bulan);

        $this->info('Selesai generate tagihan.');

        return self::SUCCESS;
    }
}
