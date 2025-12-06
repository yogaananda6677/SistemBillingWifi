<?php

namespace App\Exports;

use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class LaporanSalesAreaSheet implements
    FromArray,
    WithTitle,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithEvents,
    WithColumnFormatting
{
    protected int $tahun;
    protected $target;

    // info untuk styling dinamis
    protected int $pelangganCount = 0;
    protected int $summaryHeaderRow = 0;
    protected int $summaryDataStartRow = 0;
    protected int $summaryDataEndRow = 0;

    public function __construct(int $tahun, $target)
    {
        $this->tahun  = $tahun;
        $this->target = $target;
    }

    public function array(): array
    {
        // 1) Ambil pelanggan + langganan + paket + tagihan + paymentItems + pembayaran
        $pelanggan = Pelanggan::with([
                'langganan.paket',
                'langganan.tagihan.paymentItems.pembayaran',
            ])
            ->where('id_sales', $this->target->id_sales)
            ->where('id_area',  $this->target->id_area)
            ->orderBy('nama')
            ->get();

        $this->pelangganCount = $pelanggan->count();

        // 2) Hitung statistik tagihan per bulan (lunas/belum + nominal)
        $monthlyStats = [];
        foreach (range(1, 12) as $b) {
            $monthlyStats[$b] = [
                'lunas_count'   => 0,
                'belum_count'   => 0,
                'lunas_nominal' => 0,
                'belum_nominal' => 0,
            ];
        }

        foreach ($pelanggan as $p) {
            foreach ($p->langganan ?? [] as $lang) {
                foreach ($lang->tagihan ?? [] as $tg) {
                    if ((int)$tg->tahun !== $this->tahun) {
                        continue;
                    }

                    $b = (int)$tg->bulan;
                    if (!isset($monthlyStats[$b])) {
                        continue;
                    }

                    $nom = (int)$tg->total_tagihan;
                    $status = strtolower($tg->status_tagihan ?? '');

                    if ($status === 'lunas') {
                        $monthlyStats[$b]['lunas_count']++;
                        $monthlyStats[$b]['lunas_nominal'] += $nom;
                    } else {
                        $monthlyStats[$b]['belum_count']++;
                        $monthlyStats[$b]['belum_nominal'] += $nom;
                    }
                }
            }
        }

        // 3) Susun baris data (tanpa headings)
        $rows = [];

        // ====== DATA PELANGGAN (tabel utama) ======
        foreach ($pelanggan as $i => $p) {
            $semuaTagihan = $p->langganan
                ? $p->langganan
                    ->flatMap(fn ($l) => $l->tagihan->where('tahun', $this->tahun))
                : collect();

            $regDate = $p->tanggal_registrasi
                ? Carbon::parse($p->tanggal_registrasi)
                : null;

            $stopDate = null;
            if ($p->langganan && $p->langganan->count()) {
                $stopLangganan = $p->langganan
                    ->filter(fn ($l) => !empty($l->tanggal_berhenti))
                    ->sortByDesc('tanggal_berhenti')
                    ->first();

                if ($stopLangganan) {
                    $stopDate = Carbon::parse($stopLangganan->tanggal_berhenti);
                }
            }

            $tarif = 0;
            if ($p->langganan && $p->langganan->count()) {
                $langAktif = $p->langganan->sortByDesc('tanggal_mulai')->first();
                if ($langAktif && $langAktif->paket) {
                    $tarif = (int)$langAktif->paket->harga_total;
                }
            }

            $bulanStatus = [];

            foreach (range(1, 12) as $b) {
                $cell = '';

                // setelah berhenti di tahun yang sama → kosong
                if ($stopDate && $stopDate->year == $this->tahun && $b > $stopDate->month) {
                    $bulanStatus[$b] = '';
                    continue;
                }

                // bulan daftar
                if ($regDate && $regDate->year == $this->tahun && $regDate->month == $b) {
                    $cell = 'DAFTAR';
                }

                // bulan berhenti (override)
                if ($stopDate && $stopDate->year == $this->tahun && $stopDate->month == $b) {
                    $bulanStatus[$b] = 'BERHENTI';
                    continue;
                }

                // tagihan bulan ini
                $tg = $semuaTagihan->first(function ($t) use ($b) {
                    return (int)$t->bulan === (int)$b;
                });

                if ($tg) {
                    $status = strtolower($tg->status_tagihan ?? '');

                    if ($status === 'lunas') {
                        $paymentItem = $tg->paymentItems
                            ? $tg->paymentItems
                                ->filter(fn($pi) => $pi->pembayaran)
                                ->sortByDesc(fn($pi) => $pi->pembayaran->tanggal_bayar)
                                ->first()
                            : null;

                        if ($paymentItem && $paymentItem->pembayaran) {
                            // contoh: 20-11-25
                            $cell = Carbon::parse($paymentItem->pembayaran->tanggal_bayar)
                                ->format('d-m-y');
                        } else {
                            $cell = 'LUNAS';
                        }
                    } else {
                        $cell = 'BELUM LUNAS';
                    }
                }

                $bulanStatus[$b] = $cell;
            }

            $rows[] = array_merge(
                [
                    $i + 1,
                    $p->nama,
                    $p->ip_address ?? '-',
                    $tarif,
                ],
                array_values($bulanStatus)
            );
        }

        // ====== SUMMARY BARU DI PALING BAWAH ======

        // data pelanggan mulai baris 5
        $dataStartRow = 5;
        $dataEndRow   = $this->pelangganCount > 0
            ? $dataStartRow + $this->pelangganCount - 1
            : 4; // kalau tidak ada pelanggan, taruh summary setelah header

        // spacer
        $rows[] = [''];

        // header summary
        $rows[] = [
            'Bulan',
            'Tagihan LUNAS (pcs)',
            'Tagihan BELUM (pcs)',
            'Nominal LUNAS',
            'Nominal BELUM',
        ];

        // hitung posisi baris summary di Excel
        $this->summaryHeaderRow   = $dataEndRow + 2;
        $this->summaryDataStartRow = $this->summaryHeaderRow + 1;

        foreach (range(1, 12) as $b) {
            $namaBulan = Carbon::create($this->tahun, $b, 1)->translatedFormat('F');

            $rows[] = [
                $namaBulan,
                $monthlyStats[$b]['lunas_count'],
                $monthlyStats[$b]['belum_count'],
                $monthlyStats[$b]['lunas_nominal'],
                $monthlyStats[$b]['belum_nominal'],
            ];
        }

        $this->summaryDataEndRow = $this->summaryDataStartRow + 12 - 1;

        return $rows;
    }

    public function title(): string
    {
        $name = $this->target->nama_sales . ' - ' . $this->target->nama_area;
        return Str::limit($name, 31, '');
    }

    public function headings(): array
    {
        return [
            ['LAPORAN PELANGGAN & PEMBAYARAN - TAHUN ' . $this->tahun],
            ['Sales: ' . $this->target->nama_sales . ' | Wilayah: ' . $this->target->nama_area],
            [''],
            [
                'NO',
                'NAMA PELANGGAN',
                'IP',
                'TARIF (Rp)',
                'JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN',
                'JUL', 'AGT', 'SEP', 'OKT', 'NOV', 'DES',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Judul
        $sheet->getStyle('A1:P1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2:P2')->getFont()->setBold(true)->setSize(12);

        // Header tabel pelanggan
        $sheet->getStyle('A4:P4')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A4:P4')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A4:P4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9D9D9');
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0', // tarif pelanggan
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge judul
                $sheet->mergeCells('A1:P1');
                $sheet->mergeCells('A2:P2');
                $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');

                // Data pelanggan
                $dataStartRow = 5;
                if ($this->pelangganCount > 0) {
                    $dataEndRow = $dataStartRow + $this->pelangganCount - 1;

                    // border tabel pelanggan
                    $sheet->getStyle("A4:P{$dataEndRow}")
                        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                    // center IP & kolom bulan
                    $sheet->getStyle("C{$dataStartRow}:C{$dataEndRow}")
                        ->getAlignment()->setHorizontal('center');
                    $sheet->getStyle("E{$dataStartRow}:P{$dataEndRow}")
                        ->getAlignment()->setHorizontal('center');

                    // warna hijau/merah di kolom bulan (per pelanggan)
                    for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                        for ($col = 5; $col <= 16; $col++) { // E (5) s/d P (16)
                            $cell = $sheet->getCellByColumnAndRow($col, $row);
                            $value = (string)$cell->getValue();

                            if (stripos($value, 'BELUM') !== false) {
                                // merah
                                $sheet->getStyle($cell->getCoordinate())
                                    ->getFont()->getColor()->setARGB(Color::COLOR_RED);
                            } elseif (preg_match('/^\d{2}-\d{2}-\d{2}$/', $value) || stripos($value, 'LUNAS') !== false) {
                                // hijau
                                $sheet->getStyle($cell->getCoordinate())
                                    ->getFont()->getColor()->setARGB('FF008000');
                            }
                        }
                    }
                }

                // ====== SUMMARY PER BULAN DI BAWAH ======
                if ($this->summaryHeaderRow > 0) {
                    // header summary
                    $sheet->getStyle("A{$this->summaryHeaderRow}:E{$this->summaryHeaderRow}")
                        ->getFont()->setBold(true);
                    $sheet->getStyle("A{$this->summaryHeaderRow}:E{$this->summaryHeaderRow}")
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFD9E1F2');

                    // border summary
                    $sheet->getStyle("A{$this->summaryHeaderRow}:E{$this->summaryDataEndRow}")
                        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                    // format angka untuk kolom B–E (summary)
                    $sheet->getStyle("B{$this->summaryDataStartRow}:E{$this->summaryDataEndRow}")
                        ->getNumberFormat()->setFormatCode('#,##0');

                    // hijau untuk LUNAS (kolom B & D)
                    $sheet->getStyle("B{$this->summaryDataStartRow}:B{$this->summaryDataEndRow}")
                        ->getFont()->getColor()->setARGB('FF008000');
                    $sheet->getStyle("D{$this->summaryDataStartRow}:D{$this->summaryDataEndRow}")
                        ->getFont()->getColor()->setARGB('FF008000');

                    // merah untuk BELUM (kolom C & E)
                    $sheet->getStyle("C{$this->summaryDataStartRow}:C{$this->summaryDataEndRow}")
                        ->getFont()->getColor()->setARGB(Color::COLOR_RED);
                    $sheet->getStyle("E{$this->summaryDataStartRow}:E{$this->summaryDataEndRow}")
                        ->getFont()->getColor()->setARGB(Color::COLOR_RED);
                }
            },
        ];
    }
}
