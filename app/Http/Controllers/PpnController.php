<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ppn;
use App\Models\Paket;


class PpnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ppn = Ppn::first(); // cuma ambil satu data PPN
        return view('ppn.index', compact('ppn'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ppn.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'presentase_ppn' => 'required|numeric|min:0|max:100',
        ]);

        $ppn_convert = Ppn::convertPPN($request->presentase_ppn);

        Ppn::create([
            'presentase_ppn' => $ppn_convert,
        ]);

        return redirect()->route('ppn.index')->with('success', 'PPN berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $ppn = Ppn::findOrFail($id);
        return view('ppn.edit', compact('ppn'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'presentase_ppn' => 'required|numeric|min:0|max:100',
        ]);

        $ppn_convert = Ppn::convertPPN($request->presentase_ppn);

        $ppn = Ppn::findOrFail($id);
        $ppn->update([
            'presentase_ppn' => $ppn_convert,
        ]);

        // 🔥 UPDATE SEMUA PAKET MENGGUNAKAN MODEL
        $paketList = Paket::all();
        foreach ($paketList as $paket) {
            $paket->updateHargaDenganPpnBaru($ppn_convert);
        }

        return redirect()->route('ppn.index')
            ->with('success', 'PPN & harga paket berhasil diperbarui.');
    }


    public function destroy(string $id)
    {
        $ppn = Ppn::findOrFail($id);
        $ppn->delete();

        return redirect()->route('ppn.index')->with('success', 'PPN berhasil dihapus.');
    }
}
