<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\Langganan;
use App\Models\Pelanggan;

class TagihanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tagihan::with('langganan.pelanggan','langganan.paket');

       if($request->ajax()) {
            $query = Tagihan::with('langganan.pelanggan','langganan.paket');

            if($request->search){
                $query->whereHas('langganan.pelanggan', fn($q) => $q->where('nama', 'like', "%{$request->search}%"))
                    ->orWhereHas('langganan.paket', fn($q) => $q->where('nama_paket', 'like', "%{$request->search}%"));
            }

            if($request->status){
                $query->where('status_tagihan', $request->status);
            }

            if($request->paket){
                $query->whereHas('langganan.paket', fn($q) => $q->where('id_paket', $request->paket));
            }

            $dataTagihan = $query->paginate(10);
            $html = view('tagihan.partials.table', compact('dataTagihan'))->render();
            $pagination = $dataTagihan->links()->render();

            return response()->json(['html'=>$html, 'pagination'=>$pagination]);

        };

        // request biasa (non-AJAX)
        $paketList = Langganan::with('paket')->get()->pluck('paket')->unique('id_paket');
        $dataTagihan = $query->paginate(10);
        return view('tagihan.index', compact('dataTagihan', 'paketList'));
    }

    // public function index(Request $request)
    // {
    //     $query = Tagihan::with('langganan.pelanggan', 'langganan.paket');

    //     // Filter search
    //     if ($request->filled('search')) {
    //         $search = $request->search;
    //         $query->whereHas('langganan.pelanggan', function($q) use($search){
    //             $q->where('nama', 'like', "%{$search}%");
    //         })->orWhereHas('langganan.paket', function($q) use($search){
    //             $q->where('nama_paket', 'like', "%{$search}%");
    //         });
    //     }

    //     // Filter status
    //     if ($request->filled('status')) {
    //         $query->where('status_tagihan', $request->status);
    //     }

    //     // Filter paket
    //     if ($request->filled('paket')) {
    //         $paketId = $request->paket;
    //         $query->whereHas('langganan.paket', function($q) use($paketId){
    //             $q->where('id_paket', $paketId);
    //         });
    //     }

    //     // Ambil daftar paket unik untuk filter dropdown
    //     $paketList = Langganan::with('paket')->get()->pluck('paket')->unique('id_paket');

    //     // Pagination
    //     $dataTagihan = $query->paginate(10)->withQueryString();

    //     return view('tagihan.index', compact('dataTagihan', 'paketList'));
    // }





    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
