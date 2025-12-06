<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class DetailSetoranSheet implements FromArray, WithTitle
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
            'Tanggal Setoran',
            'Sales',
            'Wilayah',
            'Nominal',
            'Catatan',
        ];

        $salesIds = $this->assignments->pluck('id_sales')->unique()->values()->all();
        $areaIds  = $this->assignments->pluck('id_area')->unique()->values()->all();

        $data = DB::table('setoran as st')
            ->join('sales as s', 's.id_sales', '=', 'st.id_sales')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('area as a', 'a.id_area', '=', 'st.id_area')
            ->select(
                'st.tanggal_setoran',
                'u.name as nama_sales',
                'a.nama_area',
                'st.nominal',
                'st.catatan'
            )
            ->whereYear('st.tanggal_setoran', $this->tahun)
            ->whereMonth('st.tanggal_setoran', $this->bulan)
            ->whereIn('st.id_sales', $salesIds)
            ->whereIn('st.id_area', $areaIds)
            ->orderBy('st.tanggal_setoran')
            ->get();

        foreach ($data as $row) {
            $rows[] = [
                $row->tanggal_setoran,
                $row->nama_sales,
                $row->nama_area,
                (int) $row->nominal,
                $row->catatan,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Detail Setoran';
    }
}
