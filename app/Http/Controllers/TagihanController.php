<?php

namespace App\Http\Controllers;

use App\Models\Langganan;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // status di URL: '', 'semua', 'belum_lunas', 'lunas'
        $statusFilter = $request->get('status', ''); // '' = semua

        // base query: per pelanggan
        $query = \App\Models\Pelanggan::with([
            'area',
            'sales.user',
            'langganan.paket',
            'langganan.tagihan',
        ]);

        // 🔍 FILTER SEARCH (nama pelanggan / nama paket)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhereHas('langganan.paket', function ($q2) use ($search) {
                        $q2->where('nama_paket', 'like', "%{$search}%");
                    })
                    ->orWhereHas('area', function ($q3) use ($search) {
                        $q3->where('nama_area', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sales.user', function ($q4) use ($search) {
                        $q4->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // 📦 FILTER PAKET
        if ($request->filled('paket')) {
            $query->whereHas('langganan.paket', function ($q) use ($request) {
                $q->where('id_paket', $request->paket);
            });
        }

        // 👤 FILTER SALES
        if ($request->filled('sales')) {
            $query->where('id_sales', $request->sales);
        }

        // 📍 FILTER WILAYAH
        if ($request->filled('area')) {
            $query->where('id_area', $request->area);
        }

        // 🧾 FILTER STATUS TAGIHAN PER PELANGGAN
        if ($statusFilter === 'lunas') {
            // Pelanggan yang semua tagihannya lunas (atau tidak punya tagihan belum lunas)
            $query->whereDoesntHave('langganan.tagihan', function ($q) {
                $q->where('status_tagihan', 'belum lunas');
            });
        } elseif ($statusFilter === 'belum_lunas') {
            // Pelanggan yang punya minimal 1 tagihan "belum lunas"
            $query->whereHas('langganan.tagihan', function ($q) {
                $q->where('status_tagihan', 'belum lunas');
            });
        }
        // kalau '', 'semua' → tidak di-filter

        // PAGINATE
        $pelanggan = $query->paginate(10)->withQueryString();

        // daftar paket unik untuk filter dropdown
        $paketList = \App\Models\Langganan::with('paket')
            ->get()
            ->pluck('paket')
            ->unique('id_paket');

        // 🔹 data untuk filter Sales & Wilayah
        $dataSales = \App\Models\Sales::with('user')->get();
        $dataArea = \App\Models\Area::all();

        // kalau AJAX → kembalikan partial table saja
        if ($request->ajax()) {
            $html = view('tagihan.partials.table', compact('pelanggan'))->render();
            $pagination = $pelanggan->links()->render();

            return response()->json([
                'html' => $html,
                'pagination' => $pagination,
            ]);
        }

        // NON-AJAX → full page
        return view('tagihan.index', [
            'pelanggan' => $pelanggan,
            'paketList' => $paketList,
            'dataSales' => $dataSales,
            'dataArea' => $dataArea,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function hapusTagihanPelanggan(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'tagihan_ids' => 'required|array',
            'tagihan_ids.*' => 'exists:tagihan,id_tagihan',
        ]);

        // Hapus hanya tagihan belum lunas dan belum terhubung payment_item
        $deleted = \App\Models\Tagihan::whereIn('id_tagihan', $request->tagihan_ids)
            ->whereHas('langganan', fn ($q) => $q->where('id_pelanggan', $request->id_pelanggan))
            ->where('status_tagihan', 'belum lunas')
            ->whereDoesntHave('paymentItem')
            ->delete();

        return back()->with('success', "Berhasil menghapus $deleted tagihan.");
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
