<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;


class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $dataArea = Area::with(['sales.user'])->get();
    return view('area.index', compact('dataArea'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('area.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_area' => 'required|string|max:255',
        ]);

        Area::create([
            'nama_area' => $request->nama_area,
        ]);

        return redirect()->route('area.index')->with('success', 'Area berhasil ditambahkan.');
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
        $area = Area::findOrFail($id);
        return view('area.edit', compact('area'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_area' => 'required|string|max:255',
        ]);

        $area = Area::findOrFail($id);
        $area->update([
            'nama_area' => $request->nama_area,
        ]);

        return redirect()->route('area.index')->with('success', 'Area berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $area = Area::withCount('sales')->findOrFail($id);

        // Cek apakah area punya sales
        if ($area->sales_count > 0) {
            return redirect()
                ->route('area.index')
                ->with('error', 'Area tidak bisa dihapus karena masih memiliki sales.');
        }

        $area->delete();

        return redirect()->route('area.index')->with('success', 'Area berhasil dihapus.');
    }

}
