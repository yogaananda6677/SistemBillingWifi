<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class DetailPengeluaranSheet implements FromArray, WithTitle
{
    protected int $tahun;
    protected int $bulan;
    protected Collection $assignments;

    public function __construct(int $tahun, int $bulan, Collection $assignments)
    {
        $this->tahun       = $tahun;
        $this->bulan       = $bulan;
        $this->assignments = $assignments;
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = [
            'Tanggal Approve',
            'Sales',
            'Wilayah',
            'Nama Pengeluaran',
            'Nominal',
            'Catatan',
        ];

        // ambil id_sales & id_area yang valid dari assignments
        $salesIds = $this->assignments->pluck('id_sales')->unique()->values()->all();
        $areaIds  = $this->assignments->pluck('id_area')->unique()->values()->all();

        $data = DB::table('pengeluaran as pg')
            ->join('sales as s', 's.id_sales', '=', 'pg.id_sales')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('area as a', 'a.id_area', '=', 'pg.id_area')
            ->select(
                'pg.tanggal_approve',
                'u.name as nama_sales',
                'a.nama_area',
                'pg.nama_pengeluaran',
                'pg.nominal',
                'pg.catatan'
            )
            ->where('pg.status_approve', 'approved')
            ->whereYear('pg.tanggal_approve', $this->tahun)
            ->whereMonth('pg.tanggal_approve', $this->bulan)
            ->whereIn('pg.id_sales', $salesIds)
            ->whereIn('pg.id_area', $areaIds)
            ->orderBy('pg.tanggal_approve')
            ->get();

        foreach ($data as $row) {
            $rows[] = [
                $row->tanggal_approve,
                $row->nama_sales,
                $row->nama_area,
                $row->nama_pengeluaran,
                (int) $row->nominal,
                $row->catatan,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Detail Pengeluaran';
    }
}
