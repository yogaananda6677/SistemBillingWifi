<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paket;
use App\Models\Ppn;

class PaketController extends Controller
{
    public function index()
    {
        $dataPaket = Paket::withCount('langganan')->get();
        $isPpnSet  = Ppn::exists();

        return view('paket-layanan.index', compact('dataPaket', 'isPpnSet'));
    }

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
        $ppnNominal = $hargaDasar * $ppn->presentase_ppn;
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

    // ✅ BAGIAN EDIT – dibikin sama logikanya dengan CREATE
    public function edit($id)
    {
        $ppn = Ppn::first();

        // Kalau PPN belum ada, jangan boleh edit paket juga
        if (!$ppn) {
            return redirect()
                ->route('paket-layanan.index')
                ->with('error', 'PPN belum diatur. Silakan tambahkan PPN terlebih dahulu.');
        }

        $paket = Paket::findOrFail($id);

        return view('paket-layanan.edit', compact('paket', 'ppn'));
    }

    // ✅ UPDATE – hitung ulang harga pakai PPN yang sama seperti store()
    public function update(Request $request, $id)
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

        $paket = Paket::findOrFail($id);

        $hargaDasar = $request->harga_dasar;
        $ppnNominal = $hargaDasar * $ppn->presentase_ppn;
        $hargaTotal = $hargaDasar + $ppnNominal;

        $paket->update([
            'nama_paket'   => $request->nama_paket,
            'kecepatan'    => $request->kecepatan,
            'harga_dasar'  => $hargaDasar,
            'ppn_nominal'  => $ppnNominal,
            'harga_total'  => $hargaTotal,
        ]);

        return redirect()
            ->route('paket-layanan.index')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        // ikut hitung jumlah langganan
        $paket = Paket::withCount('langganan')->findOrFail($id);

        // kalau masih punya pelanggan, jangan boleh hapus
        if ($paket->langganan_count > 0) {
            return redirect()
                ->route('paket-layanan.index')
                ->with('error', 'Paket tidak bisa dihapus karena masih memiliki pelanggan.');
        }

        $paket->delete();

        return redirect()
            ->route('paket-layanan.index')
            ->with('success', 'Paket layanan berhasil dihapus.');
    }

}

