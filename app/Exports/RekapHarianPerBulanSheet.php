<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class RekapHarianPerBulanSheet implements
    FromArray,
    WithTitle,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithColumnFormatting,
    WithEvents
{
    protected int $tahun;
    protected int $bulan;

    public function __construct(int $tahun, int $bulan)
    {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
    }

    public function title(): string
    {
        $namaPendek = Carbon::create($this->tahun, $this->bulan, 1)->format('M');
        return sprintf('%02d-%s', $this->bulan, $namaPendek); // 01-Jan, 02-Feb, dst
    }

    public function headings(): array
    {
        $namaBulan = Carbon::create($this->tahun, $this->bulan, 1)
            ->translatedFormat('F');

        return [
            ['REKAP PEMASUKAN DAN PENGELUARAN BULAN ' . strtoupper($namaBulan) . ' ' . $this->tahun],
            [''],
            ['NO', 'TANGGAL', 'PEMASUKAN', 'NOMINAL', 'PENGELUARAN', 'NOMINAL'],
        ];
    }

    public function array(): array
    {
        $rows = [];

        // ================= PEMASUKAN =================

        // Pembayaran oleh ADMIN (id_sales NULL)
        $pembayaranAdmin = DB::table('pembayaran as p')
            ->join('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->leftJoin('users as u', 'u.id', '=', 'p.id_user')
            ->whereYear('p.tanggal_bayar', $this->tahun)
            ->whereMonth('p.tanggal_bayar', $this->bulan)
            ->whereNull('p.id_sales') // pembayaran lewat sales tidak dihitung di sini
            ->selectRaw('
                DATE(p.tanggal_bayar) as tgl,
                CONCAT("Internet ", pl.nama) as keterangan,
                p.nominal as nominal
            ')
            ->orderBy('tgl')
            ->get();

        // Setoran sales (pemasukan)
        $setoranSales = DB::table('setoran as st')
            ->join('sales as s', 's.id_sales', '=', 'st.id_sales')
            ->join('users as us', 'us.id', '=', 's.user_id')
            ->leftJoin('area as a', 'a.id_area', '=', 'st.id_area')
            ->whereYear('st.tanggal_setoran', $this->tahun)
            ->whereMonth('st.tanggal_setoran', $this->bulan)
            ->selectRaw('
                DATE(st.tanggal_setoran) as tgl,
                CONCAT("Setoran ", us.name, " - ", IFNULL(a.nama_area,"-")) as keterangan,
                st.nominal as nominal
            ')
            ->orderBy('tgl')
            ->get();

        // ================= PENGELUARAN =================

        $pengeluaran = DB::table('pengeluaran as pg')
            ->join('sales as s', 's.id_sales', '=', 'pg.id_sales')
            ->join('users as us', 'us.id', '=', 's.user_id')
            ->leftJoin('area as a', 'a.id_area', '=', 'pg.id_area')
            ->where('pg.status_approve', 'approved')
            ->whereYear('pg.tanggal_approve', $this->tahun)
            ->whereMonth('pg.tanggal_approve', $this->bulan)
            ->selectRaw('
                DATE(pg.tanggal_approve) as tgl,
                pg.nama_pengeluaran as keterangan,
                pg.nominal as nominal
            ')
            ->orderBy('tgl')
            ->get();

        // ================= GABUNG PER TANGGAL =================

        $byDate = [];

        foreach ($pembayaranAdmin as $row) {
            $tgl = $row->tgl;
            $byDate[$tgl]['pemasukan'][] = [
                'ket' => $row->keterangan,
                'nom' => (int) $row->nominal,
            ];
        }

        foreach ($setoranSales as $row) {
            $tgl = $row->tgl;
            $byDate[$tgl]['pemasukan'][] = [
                'ket' => $row->keterangan,
                'nom' => (int) $row->nominal,
            ];
        }

        foreach ($pengeluaran as $row) {
            $tgl = $row->tgl;
            $byDate[$tgl]['pengeluaran'][] = [
                'ket' => $row->keterangan,
                'nom' => (int) $row->nominal,
            ];
        }

        ksort($byDate);

        $no = 1;

        foreach ($byDate as $tgl => $data) {
            $pemasukan   = $data['pemasukan']   ?? [];
            $pengeluaran = $data['pengeluaran'] ?? [];

            $max = max(count($pemasukan), count($pengeluaran));
            if ($max === 0) {
                continue;
            }

            for ($i = 0; $i < $max; $i++) {
                $p  = $pemasukan[$i]   ?? null;
                $pg = $pengeluaran[$i] ?? null;

                $rows[] = [
                    $i === 0 ? $no : '',
                    $i === 0 ? Carbon::parse($tgl)->translatedFormat('j F Y') : '',
                    $p['ket'] ?? '',
                    $p['nom'] ?? '',
                    $pg['ket'] ?? '',
                    $pg['nom'] ?? '',
                ];
            }

            $no++;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Judul
        $sheet->getStyle('A1:F1')->getFont()->setBold(true)->setSize(16);

        // Header kolom
        $sheet->getStyle('A3:F3')->getFont()->setBold(true);
        $sheet->getStyle('A3:F3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9D9D9');
    }

    public function columnFormats(): array
    {
        return [
            'D' => '"Rp" #,##0',
            'F' => '"Rp" #,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Merge judul
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Border tabel
                if ($lastRow >= 3) {
                    $sheet->getStyle("A3:F{$lastRow}")
                        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }

                // NO & TANGGAL rata tengah
                $sheet->getStyle("A3:A{$lastRow}")
                    ->getAlignment()->setHorizontal('center');
                $sheet->getStyle("B3:B{$lastRow}")
                    ->getAlignment()->setHorizontal('center');
            },
        ];
    }
}
