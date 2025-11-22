<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ppn;

class PpnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ppn = Ppn::first();
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
        $ppn = Ppn::findOrFail($id);
        return view('ppn.edit', compact('ppn'));
    }

    /**
     * Update the specified resource in storage.
     */
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

        return redirect()->route('ppn.index')->with('success', 'PPN berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ppn = Ppn::findOrFail($id);
        $ppn->delete();

        return redirect()->route('ppn.index')->with('success', 'PPN berhasil dihapus.');
    }
}
