<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\Sales;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // <--- WAJIB, untuk DB::raw

class DashboardController extends Controller
{
    /**
     * Hitung status pelanggan (global + pelanggan baru per bulan terpilih)
     */
private function getStatusCounts(Carbon $startDate, Carbon $endDate): array
{
    $month = $startDate->format('m');
    $year  = $startDate->format('Y');

    // Total pelanggan global
    $total = Pelanggan::count();

    // Pelanggan baru bulan ini (berdasarkan tanggal registrasi)
    $baruBulanIni = Pelanggan::whereMonth('tanggal_registrasi', $month)
        ->whereYear('tanggal_registrasi', $year)
        ->count();

    // Pelanggan aktif (status aktif + baru)
    $aktif = Pelanggan::whereIn('status_pelanggan', ['aktif', 'baru'])->count();

    // Pelanggan berhenti
    $berhenti = Pelanggan::where('status_pelanggan', 'berhenti')->count();

    // Pelanggan isolir
    $isolir = Pelanggan::where('status_pelanggan', 'isolir')->count();

    return [
        'total'    => $total,
        'baru'     => $baruBulanIni,
        'aktif'    => $aktif,
        'berhenti' => $berhenti,
        'isolir'   => $isolir,
    ];
}


    public function index(Request $request)
    {
    // ==============================
    // FILTER BULAN + TAHUN
    // ==============================
    $bulanForm = $request->input('bulan');   // "01".."12"
    $tahunForm = $request->input('tahun');  // "2023" dll

    if ($bulanForm && $tahunForm) {
        // bentuk "YYYY-MM" supaya sama dengan format lama
        $bulanInput = $tahunForm . '-' . $bulanForm;    // contoh: 2025-08
    } else {
        // fallback kalau suatu saat kamu pakai ?bulan=YYYY-MM langsung
        $bulanInput = $request->input('bulan_full');    // opsional
    }

    if ($bulanInput) {
        try {
            $startDate = \Carbon\Carbon::parse($bulanInput . '-01')->startOfMonth();
            $endDate   = \Carbon\Carbon::parse($bulanInput . '-01')->endOfMonth();
        } catch (\Throwable $e) {
            $startDate = now()->startOfMonth();
            $endDate   = now()->endOfMonth();
        }
    } else {
        $startDate = now()->startOfMonth();
        $endDate   = now()->endOfMonth();
    }
    $selMonth = (int) $startDate->format('m');
$selYear  = (int) $startDate->format('Y');
        // Nama bulan (Indonesia) buat label
        $monthNames = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        $currentMonthNumber = $startDate->format('m');
        $currentYear        = $startDate->format('Y');
        $currentMonthName   = $monthNames[$currentMonthNumber] ?? 'Bulan';

        // ============================================
        // HITUNG DATA BERDASARKAN RENTANG BULAN
        // ============================================

// LUNAS di bulan terpilih
$totalPembayaranTerima = Tagihan::where('status_tagihan', 'lunas')
    ->whereYear('jatuh_tempo', $selYear)
    ->whereMonth('jatuh_tempo', $selMonth)
    ->sum('total_tagihan');

// BELUM LUNAS & SUDAH JATUH TEMPO di bulan terpilih
$totalPembayaranTerlambat = Tagihan::where('status_tagihan', 'belum lunas')
    ->whereYear('jatuh_tempo', $selYear)
    ->whereMonth('jatuh_tempo', $selMonth)
    ->where('jatuh_tempo', '<', now())
    ->sum('total_tagihan');




        // ============================================
        // DATA PELANGGAN (pakai helper getStatusCounts)
        // ============================================
        $statusCounts = $this->getStatusCounts($startDate, $endDate);

$counters = [
    [
        'icon'  => 'bi-person-fill',
        'color' => 'text-primary',
        'label' => 'Total Pelanggan',
        'value' => $statusCounts['total'],
    ],
    [
        'icon'  => 'bi-person-plus-fill',
        'color' => 'text-success',
        'label' => "Pelanggan Baru ({$currentMonthName} {$currentYear})",
        'value' => $statusCounts['baru'],
    ],
    [
        'icon'  => 'bi-emoji-smile-fill',
        'color' => 'text-info',
        'label' => 'Pelanggan Aktif',
        'value' => $statusCounts['aktif'],
    ],
    [
        'icon'  => 'bi-person-dash-fill',
        'color' => 'text-warning',
        'label' => 'Pelanggan Isolir',
        'value' => $statusCounts['isolir'],
    ],
    [
        'icon'  => 'bi-person-x-fill',
        'color' => 'text-danger',
        'label' => 'Pelanggan Berhenti',
        'value' => $statusCounts['berhenti'],
    ],
];

// ============================================
// STATUS PEMBAYARAN (UNTUK PIE CHART)
// ============================================
$statusPembayaran = [
    'lunas'       => Tagihan::where('status_tagihan', 'lunas')
                        ->whereBetween('jatuh_tempo', [$startDate, $endDate])
                        ->count(),
    'belum_lunas' => Tagihan::where('status_tagihan', 'belum lunas')
                        ->whereBetween('jatuh_tempo', [$startDate, $endDate])
                        ->count(),
];

        // ============================================
        // TABEL TAGIHAN (5 TERBARU PER JENIS)
        // ============================================
        $tagihanBelumBayar = Tagihan::with([
                'langganan.pelanggan.area',
                'langganan.pelanggan.sales.user',
            ])
            ->whereBetween('jatuh_tempo', [$startDate, $endDate])
            ->where('status_tagihan', 'belum lunas')
            ->orderBy('jatuh_tempo', 'asc')
            ->limit(5)
            ->get();

        $tagihanSudahBayar = Tagihan::with([
                'langganan.pelanggan.area',
                'langganan.pelanggan.sales.user',
            ])
            ->whereBetween('jatuh_tempo', [$startDate, $endDate])
            ->where('status_tagihan', 'lunas')
            ->orderBy('jatuh_tempo', 'desc')
            ->limit(5)
            ->get();

            
            // ============================================
// PROGRES PENARIKAN PEMBAYARAN PER SALES–WILAYAH
// ============================================

// Ambil semua pasangan sales–area yang aktif
$assignments = DB::table('area_sales as asg')
    ->join('sales as s', 's.id_sales', '=', 'asg.id_sales')
    ->join('users as u', 'u.id', '=', 's.user_id')
    ->join('area as a', 'a.id_area', '=', 'asg.id_area')
    ->select(
        'asg.id_area',
        'a.nama_area',
        's.id_sales',
        'u.name as nama_sales'
    )
    ->orderBy('u.name')
    ->orderBy('a.nama_area')
    ->get();

$salesProgress = $assignments->map(function ($asg) use ($startDate, $endDate) {

    // Total tagihan milik sales–area ini di bulan terpilih
    $totalTagihan = Tagihan::whereHas('langganan.pelanggan', function ($q) use ($asg) {
            $q->where('id_sales', $asg->id_sales)
              ->where('id_area',  $asg->id_area);
        })
        ->whereBetween('jatuh_tempo', [$startDate, $endDate])
        ->count();

    // Tagihan yang sudah lunas milik sales–area ini di bulan terpilih
    $tagihanLunas = Tagihan::whereHas('langganan.pelanggan', function ($q) use ($asg) {
            $q->where('id_sales', $asg->id_sales)
              ->where('id_area',  $asg->id_area);
        })
        ->whereBetween('jatuh_tempo', [$startDate, $endDate])
        ->where('status_tagihan', 'lunas')
        ->count();

    $percent = $totalTagihan > 0
        ? round(($tagihanLunas / $totalTagihan) * 100)
        : 0;

    return [
        // Label: "Nama Sales – Nama Area"
        'nama'    => ($asg->nama_sales ?? 'Sales #' . $asg->id_sales) . ' – ' . ($asg->nama_area ?? 'Tanpa area'),
        'percent' => $percent,
        'done'    => $tagihanLunas,
        'total'   => $totalTagihan,
    ];
});

// (opsional) kalau mau, bisa filter yang totalTagihan > 0 biar nggak rame bar 0/0
$salesProgress = $salesProgress->filter(fn ($row) => $row['total'] > 0)->values();

    return view('admin.dashboard', [
        // data2 lama...
        'counters'                 => $counters,
        'totalPembayaranTerima'    => $totalPembayaranTerima,
        'totalPembayaranTerlambat' => $totalPembayaranTerlambat,
        'tagihanBelumBayar'        => $tagihanBelumBayar,
        'tagihanSudahBayar'        => $tagihanSudahBayar,
        'salesProgress'            => $salesProgress,
        'startDate'                => $startDate,
        'endDate'                  => $endDate,
        'statusPembayaran'         => $statusPembayaran,
        'statusCounts'             => $statusCounts,

        // buat select di Blade:
        'selectedMonth'            => $bulanForm ?: $startDate->format('m'),
        'selectedYear'             => $tahunForm ?: $startDate->format('Y'),
        'currentMonthName' => $currentMonthName,
        'currentYear'      => $currentYear,
    ]);

    }
}
