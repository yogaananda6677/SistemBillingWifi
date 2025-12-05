<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SetoranAdminController extends Controller
{
    public function index(Request $request)
    {
        // ambil data sales untuk dropdown
        $sales = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->select('s.id_sales', 'u.name as nama_sales')
            ->orderBy('u.name')
            ->get();

        // riwayat setoran terakhir
        $riwayat = DB::table('setoran as st')
            ->join('sales as s', 's.id_sales', '=', 'st.id_sales')
            ->join('users as us', 'us.id', '=', 's.user_id')
            ->join('admins as a', 'a.id_admin', '=', 'st.id_admin')
            ->join('users as ua', 'ua.id', '=', 'a.user_id')
            ->select(
                'st.tanggal_setoran',
                'st.nominal',
                'st.catatan',
                'us.name as nama_sales',
                'ua.name as nama_admin'
            )
            ->orderByDesc('st.tanggal_setoran')
            ->limit(20)
            ->get();

        // ⛔ BARIS INI YANG BIKIN ERROR:
        // return view('admin.setoran.index', [...]);

        // ✅ GANTI JADI:
        return view('setoran.index', [
            'sales'   => $sales,
            'riwayat' => $riwayat,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_sales'        => ['required', 'exists:sales,id_sales'],
            'tanggal_setoran' => ['required', 'date'],
            'nominal'         => ['required', 'numeric', 'min:1'],
            'catatan'         => ['nullable', 'string'],
        ]);

        DB::table('setoran')->insert([
            'id_sales'        => $request->id_sales,
            'id_admin'        => DB::table('admins')
                                    ->where('user_id', Auth::id())
                                    ->value('id_admin'),
            'tanggal_setoran' => $request->tanggal_setoran,
            'nominal'         => $request->nominal,
            'catatan'         => $request->catatan,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()
            ->route('admin.setoran.index')
            ->with('success', 'Setoran berhasil disimpan.');
    }
}
