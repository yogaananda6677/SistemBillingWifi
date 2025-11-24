<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paket;
use App\Models\Ppn;

class PaketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataPaket  = Paket::all();
        $isPpnSet   = Ppn::exists(); // true kalau sudah ada minimal 1 data PPN

        return view('paket-layanan.index', compact('dataPaket', 'isPpnSet'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ppn = Ppn::first();

        if (!$ppn) {
            return redirect()
                ->route('paket-layanan.index')
                ->with('error', 'PPN belum diatur. Silakan tambahkan PPN terlebih dahulu.');
        }

        return view('paket-layanan.create', compact('ppn'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_paket'  => 'required|string|max:255',
            'kecepatan'   => 'required|string|max:255',
            'harga_dasar' => 'required|numeric|min:0',
        ]);

        $ppn = Ppn::first();
        if (!$ppn) {
            return redirect()
                ->route('paket-layanan.index')
                ->with('error', 'PPN belum diatur. Silakan tambahkan PPN terlebih dahulu.');
        }

        $hargaDasar = $request->harga_dasar;
        $ppnNominal = $hargaDasar * $ppn->presentase_ppn; // presentase_ppn sudah 0.xx
        $hargaTotal = $hargaDasar + $ppnNominal;

        Paket::create([
            'nama_paket'   => $request->nama_paket,
            'kecepatan'    => $request->kecepatan,
            'harga_dasar'  => $hargaDasar,
            'ppn_nominal'  => $ppnNominal,
            'harga_total'  => $hargaTotal,
        ]);

        return redirect()
            ->route('paket-layanan.index')
            ->with('success', 'Paket berhasil ditambahkan.');
    }
    

    // method edit/update/destroy lanjut pakai strukturmu sendiri
}
