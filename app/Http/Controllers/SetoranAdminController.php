<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SetoranAdminController extends Controller
{
    // ======================== //
    // 1. HALAMAN LIST SALES   //
    // ======================== //
    public function index(Request $request)
    {
        // Bulan default: bulan berjalan (PASTIKAN INTEGER)
        $bulan = (int) $request->get('bulan', now()->month);
        $namaBulan = now()->startOfMonth()->month($bulan)->translatedFormat('F');

        // Ringkasan per sales (total setor, target sementara 0)
        $sales = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('setoran as st', function ($join) use ($bulan) {
                $join->on('st.id_sales', '=', 's.id_sales')
                     ->whereMonth('st.tanggal_setoran', $bulan);
            })
            ->select(
                's.id_sales',
                'u.name as nama_sales',
                DB::raw('COALESCE(SUM(st.nominal), 0) as total_setor')
            )
            ->groupBy('s.id_sales', 'u.name')
            ->orderBy('u.name')
            ->get()
            ->map(function ($row) {
                // karena di tabel sales belum ada kolom target_setor, kita set 0 dulu
                $row->target_setor = 0;

                // sisa = target - total_setor
                $row->sisa = $row->target_setor - $row->total_setor;

                return $row;
            });

        return view('setoran.index', [
            'sales'      => $sales,
            'bulan'      => $bulan,
            'namaBulan'  => $namaBulan,
        ]);
    }

    // ======================== //
    // 2. RIWAYAT SETORAN      //
    // ======================== //
    public function riwayat($id_sales, Request $request)
    {
        // Bulan juga harus integer, jangan pakai format('m') string
        $bulan = (int) $request->get('bulan', now()->month);
        $namaBulan = now()->startOfMonth()->month($bulan)->translatedFormat('F');

        // data sales
        $sales = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->select('s.id_sales', 'u.name as nama_sales')
            ->where('s.id_sales', $id_sales)
            ->first();

        if (!$sales) {
            abort(404);
        }

        // sementara: target 0 dulu (belum ada kolom target di DB)
        $sales->target_setor = 0;

        // total setor bulan ini
        $totalSetor = DB::table('setoran')
            ->where('id_sales', $id_sales)
            ->whereMonth('tanggal_setoran', $bulan)
            ->sum('nominal');

        // sisa global bulan ini (target - total)
        $sisa = $sales->target_setor - $totalSetor;

        // riwayat detail
        $riwayat = DB::table('setoran as st')
            ->join('admins as a', 'a.id_admin', '=', 'st.id_admin')
            ->join('users as ua', 'ua.id', '=', 'a.user_id')
            ->select(
                'st.tanggal_setoran',
                'st.nominal',
                'st.catatan',
                'ua.name as nama_admin'
            )
            ->where('st.id_sales', $id_sales)
            ->whereMonth('st.tanggal_setoran', $bulan)
            ->orderByDesc('st.tanggal_setoran')
            ->get();

        return view('setoran.riwayat', [
            'sales'      => $sales,
            'riwayat'    => $riwayat,
            'totalSetor' => $totalSetor,
            'sisa'       => $sisa,
            'bulan'      => $bulan,
            'namaBulan'  => $namaBulan,
        ]);
    }

    // ======================== //
    // 3. FORM TAMBAH SETORAN  //
    // ======================== //
    public function create($id_sales)
    {
        $sales = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->select('s.id_sales', 'u.name as nama_sales')
            ->where('s.id_sales', $id_sales)
            ->first();

        if (!$sales) {
            abort(404);
        }

        return view('setoran.create', [
            'sales' => $sales,
        ]);
    }

    // ======================== //
    // 4. SIMPAN SETORAN       //
    // ======================== //
    public function store(Request $request)
    {
        $request->validate([
            'id_sales' => ['required', 'exists:sales,id_sales'],
            'nominal'  => ['required', 'numeric', 'min:1'],
            'catatan'  => ['nullable', 'string'],
        ]);

        DB::table('setoran')->insert([
            'id_sales'        => $request->id_sales,
            'id_admin'        => DB::table('admins')
                                    ->where('user_id', Auth::id())
                                    ->value('id_admin'),
            'tanggal_setoran' => now(),
            'nominal'         => $request->nominal,
            'catatan'         => $request->catatan,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()
            ->route('admin.setoran.riwayat', $request->id_sales)
            ->with('success', 'Setoran berhasil disimpan.');
    }
}
