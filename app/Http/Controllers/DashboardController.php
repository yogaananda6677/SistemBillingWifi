<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\Sales;

class DashboardController extends Controller
{
    public function index()
    {
        // =========================
        // TOTAL-TOTAL DASAR
        // =========================

        $totalPelanggan         = Pelanggan::count();
        $totalPelangganBaru     = Pelanggan::where('status_pelanggan', 'baru')->count();
        $totalPelangganAktif    = Pelanggan::where('status_pelanggan', 'aktif')->count();
        $totalPelangganBerhenti = Pelanggan::where('status_pelanggan', 'berhenti')->count();

        // Total pembayaran diterima (misal: semua waktu)
        $totalPembayaranTerima = (float) Pembayaran::sum('nominal');

        // Total tagihan terlambat (belum lunas & lewat jatuh tempo)
        $totalPembayaranTerlambat = (float) Tagihan::where('status_tagihan', 'belum lunas')
            ->where('jatuh_tempo', '<', now())
            ->sum('total_tagihan');

        // =========================
        // COUNTER KECIL
        // =========================

        $counters = [
            [
                'icon'  => 'bi-person-fill',
                'color' => 'text-primary',
                'label' => 'Total Pelanggan',
                'value' => $totalPelanggan,
            ],
            [
                'icon'  => 'bi-person-plus-fill',
                'color' => 'text-success',
                'label' => 'Pelanggan Baru',
                'value' => $totalPelangganBaru,
            ],
            [
                'icon'  => 'bi-emoji-smile-fill',
                'color' => 'text-info',
                'label' => 'Pelanggan Aktif',
                'value' => $totalPelangganAktif,
            ],
            [
                'icon'  => 'bi-person-x-fill',
                'color' => 'text-danger',
                'label' => 'Pelanggan Berhenti',
                'value' => $totalPelangganBerhenti,
            ],
        ];

        // =========================
        // DATA TABEL "BELUM BAYAR" & "SUDAH BAYAR"
        // =========================

        $tagihanBelumBayar = Tagihan::with([
                'langganan.pelanggan.area',
                'langganan.pelanggan.sales.user',
            ])
            ->where('status_tagihan', 'belum lunas')
            ->orderBy('jatuh_tempo', 'asc')
            ->limit(5)
            ->get();

        $tagihanSudahBayar = Tagihan::with([
                'langganan.pelanggan.area',
                'langganan.pelanggan.sales.user',
            ])
            ->where('status_tagihan', 'lunas')
            ->orderBy('jatuh_tempo', 'desc')
            ->limit(5)
            ->get();

        // =========================
        // PROGRES PENARIKAN PER SALES (CONTOH SEDERHANA)
        // =========================
        // Contoh logika:
        //  - total pelanggan per sales
        //  - berapa pelanggan yang punya tagihan bulan ini & sudah lunas
        // Silakan sesuaikan dengan kebutuhan real-mu.

        $salesProgress = Sales::withCount('pelanggan')->get()->map(function ($sales) {
            $totalPelanggan = $sales->pelanggan_count;

            // misal: yang sudah bayar adalah pelanggan yang punya minimal 1 tagihan 'lunas'
            $pelangganSudahBayar = $sales->pelanggan()
                ->whereHas('langganan.tagihan', function ($q) {
                    $q->where('status_tagihan', 'lunas');
                })
                ->count();

            $percent = $totalPelanggan > 0
                ? round(($pelangganSudahBayar / $totalPelanggan) * 100)
                : 0;

            return [
                'nama'    => $sales->user->name ?? 'Sales #' . $sales->id_sales,
                'percent' => $percent,
                'done'    => $pelangganSudahBayar,
                'total'   => $totalPelanggan,
            ];
        });

        return view('admin.dashboard', [
            'counters'                => $counters,
            'totalPembayaranTerima'   => $totalPembayaranTerima,
            'totalPembayaranTerlambat'=> $totalPembayaranTerlambat,
            'totalPelanggan'          => $totalPelanggan,
            'totalPelangganBaru'      => $totalPelangganBaru,
            'totalPelangganAktif'     => $totalPelangganAktif,
            'totalPelangganBerhenti'  => $totalPelangganBerhenti,
            'tagihanBelumBayar'       => $tagihanBelumBayar,
            'tagihanSudahBayar'       => $tagihanSudahBayar,
            'salesProgress'           => $salesProgress,
        ]);
    }
}
