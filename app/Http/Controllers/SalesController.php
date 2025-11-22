<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index()
    {
        $data = Sales::all();
        return view('sales.index', compact('data'));
    }

    public function create()
    {
        return view('sales.create');
    }

    public function store(Request $request)
    {
        Sales::create($request->all());
        return redirect()->route('sales.index')->with('success', 'Sales berhasil ditambahkan');
    }

    public function show($id)
    {
        $sales = Sales::findOrFail($id);
        return view('sales.show', compact('sales'));
    }

    public function edit($id)
    {
        $sales = Sales::findOrFail($id);
        return view('sales.edit', compact('sales'));
    }

    public function update(Request $request, $id)
    {
        $sales = Sales::findOrFail($id);
        $sales->update($request->all());
        return redirect()->route('sales.index')->with('success', 'Sales berhasil diperbarui');
    }

    public function destroy($id)
    {
        Sales::findOrFail($id)->delete();
        return redirect()->route('sales.index')->with('success', 'Sales berhasil dihapus');
    }
}
