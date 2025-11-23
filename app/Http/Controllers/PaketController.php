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
        $dataPaket = Paket::all();
        return view('paket-layanan.index', compact('dataPaket'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ppn = Ppn::first();
        return view('paket-layanan.create', compact('ppn'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'kecepatan' => 'required|string|max:100',
            'harga_dasar' => 'required|numeric|min:0',
            'ppn_nominal' => 'required|numeric|min:0|',
            // 'harga_total' => 'required|numeric|min:0',
            // 'durasi' => 'required|integer|min:1',
        ]);


        $ppnNominal = Paket::hitungPPN($request->harga_dasar, $request->ppn_nominal);
        $hargaTotal = Paket::hitungHargaTotal($request->harga_dasar, $ppnNominal);

        // $hargaDasar = Paket::formatAngka($request->harga_dasar);



        Paket::create([
            'nama_paket' => $request->nama_paket,
            'kecepatan' => $request->kecepatan,
            'harga_dasar' => $request->harga_dasar,
            'ppn_nominal' => $ppnNominal,
            'harga_total' => $hargaTotal,
            // 'durasi' => $request->durasi,
        ]);

        return redirect()->route('paket-layanan.index')->with('success', 'Paket layanan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $paket = Paket::findOrFail($id);
        $ppn = Ppn::first();
        return view('paket-layanan.edit', compact('paket', 'ppn'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'kecepatan' => 'required|string|max:100',
            'harga_dasar' => 'required|numeric|min:0',
            'ppn_nominal' => 'required|numeric|min:0|',
            // 'harga_total' => 'required|numeric|min:0',
            // 'durasi' => 'required|integer|min:1',
        ]);

        $ppnNominal = Paket::hitungPPN($request->harga_dasar, $request->ppn_nominal);
        $hargaTotal = Paket::hitungHargaTotal($request->harga_dasar, $ppnNominal);

        $paket = Paket::findOrFail($id);
        $paket->update([
            'nama_paket' => $request->nama_paket,
            'kecepatan' => $request->kecepatan,
            'harga_dasar' => $request->harga_dasar,
            'ppn_nominal' => $ppnNominal,
            'harga_total' => $hargaTotal,
            // 'durasi' => $request->durasi,
        ]);

        return redirect()->route('paket-layanan.index')->with('success', 'Paket layanan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paket = Paket::findOrFail($id);
        $paket->delete();

        return redirect()->route('paket-layanan.index')->with('success', 'Paket layanan berhasil dihapus.');
    }
}
