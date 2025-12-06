<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

class RekapHarianBulananExport implements WithMultipleSheets
{
    use Exportable;

    protected int $tahun;

    public function __construct(int $tahun)
    {
        $this->tahun = $tahun;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach (range(1, 12) as $bulan) {
            $sheets[] = new RekapHarianPerBulanSheet($this->tahun, $bulan);
        }

        return $sheets;
    }
}
