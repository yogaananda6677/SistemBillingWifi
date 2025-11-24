<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Langganan;;
use App\Models\Pelanggan;
use App\Models\Ppn;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {

        $totalPembayaranTerima = 1200000.344;
        $totalPembayaranTerlambat = 1200000344;
        $totalPelanggan = Pelanggan::count();
        $totalPelangganBaru = Pelanggan::where('status_pelanggan', 'baru')->count();
        $totalPelangganAktif = Pelanggan::where('status_pelanggan', 'aktif')->count();
        $totalPelangganBerhenti = Pelanggan::where('status_pelanggan', 'berhenti')->count();

        $counters = [
            ['icon'=>'bi-person-fill','color'=>'text-primary','label'=>'Total Pelanggan','value'=>$totalPelanggan],
            ['icon'=>'bi-person-plus-fill','color'=>'text-success','label'=>'Pelanggan Baru','value'=>$totalPelangganBaru],
            ['icon'=>'bi-emoji-smile-fill','color'=>'text-info','label'=>'Pelanggan Aktif','value'=>$totalPelangganAktif],
            ['icon'=>'bi-person-x-fill','color'=>'text-danger','label'=>'Pelanggan Berhenti','value'=>$totalPelangganBerhenti],
        ];

        return view('admin.dashboard', compact('counters', 'totalPembayaranTerima', 'totalPembayaranTerlambat', 'totalPelanggan', 'totalPelangganBaru', 'totalPelangganAktif', 'totalPelangganBerhenti'));
    }
}
