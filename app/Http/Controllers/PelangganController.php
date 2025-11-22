<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Langganan;
use App\Models\Sales;
use App\Models\Paket;
use App\Models\Area;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataSales = Sales::with('user')->get();
        $dataArea = Area::all();
        $dataPaket = Paket::all();
        $pelanggan = Pelanggan::with('sales')->get();
        return view('pelanggan.index', compact('pelanggan' , 'dataSales', 'dataArea', 'dataPaket'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dataSales = Sales::with('user')->get();
        $dataArea = Area::all(); // dropdown sales
        $dataPaket = Paket::all(); // dropdown sales
        return view('pelanggan.create', compact('dataSales' , 'dataArea', 'dataPaket'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'id_sales' => 'nullable|exists:sales,id_sales',
            'id_area' => 'nullable|exists:area,id_area',
            'id_paket' => 'required',
            'nama' => 'required|string|max:100',
            'nik' => 'required|string|max:30',
            'alamat' => 'required|string',
            'nomor_hp' => 'required|string',
            'ip_address' => 'required|string',
            'status_pelanggan' => 'required|in:baru,aktif,berhenti',
            'tanggal_registrasi' => 'required|date'
        ]);

        DB::beginTransaction();

        try {
            // 1. Simpan pelanggan
            $pelanggan = Pelanggan::create([
                'id_sales'          => $request->id_sales ?? null,
                'id_area'           => $request->id_area ?? null,
                'nama'              => $request->nama,
                'nik'               => $request->nik,
                'alamat'            => $request->alamat,
                'nomor_hp'          => $request->nomor_hp,
                'ip_address'        => $request->ip_address,
                'status_pelanggan'  => $request->status_pelanggan, // default awal
                'tanggal_registrasi' => $request->tanggal_registrasi,
            ]);

            // dd($pelanggan);

            // 2. Simpan langganan
            Langganan::create([
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'id_paket'     => $request->id_paket,
                'tanggal_mulai' => now(),
                'status_langganan'  => 'aktif',
            ]);

            DB::commit();

            return redirect()->route('pelanggan.index')
                ->with('success', 'Pelanggan berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage());
            return back()->with('error', 'Terjadi kesalahan!');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pelanggan = Pelanggan::with(['area', 'sales.user', 'langganan.paket'])->findOrFail($id);
        return view('pelanggan.show', compact('pelanggan'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $dataSales = Sales::with('user')->get();
        $dataArea = Area::all(); // dropdown sales
        $dataPaket = Paket::all(); // dropdown sales
        $pelanggan = Pelanggan::findOrFail($id);
        return view('pelanggan.edit', compact('pelanggan','dataSales', 'dataArea', 'dataPaket'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_sales' => 'nullable|exists:sales,id_sales',
            'id_area' => 'nullable|exists:area,id_area',
            'nama' => 'required|string|max:100',
            'nik' => 'required|string|max:30',
            'alamat' => 'required|string',
            'nomor_hp' => 'required|string',
            'ip_address' => 'required|string',
            'status_pelanggan' => 'required|in:baru,aktif,berhenti',
            'tanggal_registrasi' => 'required|date'
        ]);

        $pelanggan = Pelanggan::findOrFail($id);

        $pelanggan->update($request->all());

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->langganan()->delete();
        $pelanggan->delete();

        return redirect()->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}
