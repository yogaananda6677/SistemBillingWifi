<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanSalesAreaExport implements WithMultipleSheets
{
    use Exportable;

    protected int $tahun;
    /** @var \Illuminate\Support\Collection */
    protected Collection $targets;

    public function __construct(int $tahun, Collection $targets)
    {
        $this->tahun   = $tahun;
        $this->targets = $targets;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->targets as $target) {
            $sheets[] = new LaporanSalesAreaSheet($this->tahun, $target);
        }

        return $sheets;
    }
}
