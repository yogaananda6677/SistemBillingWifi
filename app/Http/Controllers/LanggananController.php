<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Langganan;
use App\Models\Pelanggan;
use App\Models\Paket;
use Illuminate\Support\Facades\DB;


class LanggananController extends Controller
{
    /**
     * Tampilkan daftar langganan
     */
    public function index()
    {
        // eager loading untuk menghindari N+1 query
        $langganan = Langganan::with(['pelanggan', 'paket'])->get();
        return view('langganan.index', compact('langganan'));
    }

    /**
     * Tampilkan form tambah langganan
     */
    public function create()
    {
        $pelanggan = Pelanggan::all(); // atau filter jika perlu
        $paket = Paket::all();
        return view('langganan.create', compact('pelanggan', 'paket'));
    }

    /**
     * Simpan langganan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'id_paket' => 'required|exists:paket,id_paket',
        ]);

        DB::beginTransaction();

        try {
            // 1. Buat pelanggan baru
            $pelanggan = Pelanggan::create([
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'id_sales' => $request->id_sales,
            ]);

            // 2. Buat langganan otomatis
            Langganan::create([
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'id_paket' => $request->id_paket,
                'tanggal_mulai' => now(),
                'status_langganan' => 'aktif',
            ]);

            DB::commit();

            // 3. Redirect tetap ke pelanggan.index (atau langganan.index)
            return redirect()->route('pelanggan.index')
                ->with('success', 'Pelanggan dan langganan berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan!');
        }
    }


    /**
     * Tampilkan detail langganan
     */
    public function show($id)
    {
        $langganan = Langganan::with(['pelanggan', 'paket'])->findOrFail($id);
        return view('langganan.show', compact('langganan'));
    }

    /**
     * Tampilkan form edit langganan
     */
    public function edit($id)
    {
        $langganan = Langganan::findOrFail($id);
        $pelanggan = Pelanggan::all();
        $paket = Paket::all();
        return view('langganan.edit', compact('langganan', 'pelanggan', 'paket'));
    }

    /**
     * Update langganan
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'id_paket' => 'required|exists:paket,id_paket',
            'status_aktif' => 'required|in:aktif,nonaktif',
        ]);

        $langganan = Langganan::findOrFail($id);
        $langganan->update([
            'id_pelanggan' => $request->id_pelanggan,
            'id_paket' => $request->id_paket,
            'status_aktif' => $request->status_aktif,
        ]);

        return redirect()->route('langganan.index')->with('success', 'Langganan berhasil diperbarui');
    }

    /**
     * Hapus langganan
     */
    public function destroy($id)
    {
        $langganan = Langganan::findOrFail($id);
        $langganan->delete();
        return redirect()->route('langganan.index')->with('success', 'Langganan berhasil dihapus');
    }
}
