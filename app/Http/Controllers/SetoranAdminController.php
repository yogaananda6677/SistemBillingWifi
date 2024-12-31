<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SetoranAdminController extends Controller
{
    // 1. LIST SEMUA SALES–AREA PER BULAN
    public function index(Request $request)
    {
        $tahun = (int) $request->get('tahun', now()->year);
        $bulan = (int) $request->get('bulan', now()->month);

        $rows = DB::table('area_sales as asg')
            ->join('sales as s', 's.id_sales', '=', 'asg.id_sales')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->join('area as a', 'a.id_area', '=', 'asg.id_area')
            ->select(
                's.id_sales',
                'u.name as nama_sales',
                'a.id_area',
                'a.nama_area'
            )
            ->orderBy('u.name')
            ->orderBy('a.nama_area')
            ->get()
            ->map(function ($row) use ($tahun, $bulan) {

                $pendapatan = DB::table('pembayaran as p')
                    ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                    ->where('p.id_sales', $row->id_sales)
                    ->where('pl.id_area', $row->id_area)
                    ->whereYear('p.tanggal_bayar', $tahun)
                    ->whereMonth('p.tanggal_bayar', $bulan)
                    ->sum('p.nominal');

                $komisi = DB::table('transaksi_komisi as tk')
                    ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
                    ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                    ->where('p.id_sales', $row->id_sales)
                    ->where('pl.id_area', $row->id_area)
                    ->whereYear('p.tanggal_bayar', $tahun)
                    ->whereMonth('p.tanggal_bayar', $bulan)
                    ->sum('tk.nominal_komisi');

                $pengeluaran = DB::table('pengeluaran as pg')
                    ->where('pg.id_sales', $row->id_sales)
                    ->where('pg.id_area', $row->id_area)
                    ->where('pg.status_approve', 'approved')
                    ->whereYear('pg.tanggal_approve', $tahun)
                    ->whereMonth('pg.tanggal_approve', $bulan)
                    ->sum('pg.nominal');

                $target = $pendapatan - $komisi - $pengeluaran;
$totalSetoran = DB::table('setoran as st')
    ->where('st.id_sales', $row->id_sales)
    ->where('st.id_area', $row->id_area)
    ->where('st.tahun', $tahun)
    ->where('st.bulan', $bulan)
    ->sum('st.nominal');


                $row->target_setor     = (float) $target;
                $row->total_setoran    = (float) $totalSetoran;
                $row->sisa             = $row->target_setor - $row->total_setoran;

                return $row;
            });

        return view('setoran.index', [
            'rows'          => $rows,
            'selectedYear'  => $tahun,
            'selectedMonth' => $bulan,
        ]);
    }

    // 2. RIWAYAT PER SALES–AREA & PER BULAN
    public function riwayat($id_sales, $id_area, Request $request)
    {
        $tahun = (int) $request->get('tahun', now()->year);
        $bulan = (int) $request->get('bulan', now()->month);
        $namaBulan = now()->setYear($tahun)->setMonth($bulan)->translatedFormat('F');

        $salesArea = DB::table('area_sales as asg')
            ->join('sales as s', 's.id_sales', '=', 'asg.id_sales')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->join('area as a', 'a.id_area', '=', 'asg.id_area')
            ->select(
                's.id_sales',
                'u.name as nama_sales',
                'a.id_area',
                'a.nama_area'
            )
            ->where('s.id_sales', $id_sales)
            ->where('a.id_area', $id_area)
            ->first();

        if (!$salesArea) {
            abort(404);
        }

        $pendapatan = DB::table('pembayaran as p')
            ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->where('p.id_sales', $id_sales)
            ->where('pl.id_area', $id_area)
            ->whereYear('p.tanggal_bayar', $tahun)
            ->whereMonth('p.tanggal_bayar', $bulan)
            ->sum('p.nominal');

        $komisi = DB::table('transaksi_komisi as tk')
            ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
            ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->where('p.id_sales', $id_sales)
            ->where('pl.id_area', $id_area)
            ->whereYear('p.tanggal_bayar', $tahun)
            ->whereMonth('p.tanggal_bayar', $bulan)
            ->sum('tk.nominal_komisi');

        $pengeluaran = DB::table('pengeluaran as pg')
            ->where('pg.id_sales', $id_sales)
            ->where('pg.id_area', $id_area)
            ->where('pg.status_approve', 'approved')
            ->whereYear('pg.tanggal_approve', $tahun)
            ->whereMonth('pg.tanggal_approve', $bulan)
            ->sum('pg.nominal');

        $wajibBulan = $pendapatan - $komisi - $pengeluaran;

$setorans = DB::table('setoran as st')
    ->join('admins as a', 'a.id_admin', '=', 'st.id_admin')
    ->join('users as ua', 'ua.id', '=', 'a.user_id')
    ->select(
        'st.id_setoran',
        'st.tanggal_setoran',
        'st.nominal',
        'st.catatan',
        'st.tahun',
        'st.bulan',
        'ua.name as nama_admin'
    )
    ->where('st.id_sales', $id_sales)
    ->where('st.id_area', $id_area)
    ->where('st.tahun', $tahun)
    ->where('st.bulan', $bulan)
    ->orderBy('st.tanggal_setoran', 'desc') // masih boleh urut tanggal REAL
    ->get();


        $totalSetoranBulan = (float) $setorans->sum('nominal');

        $sisaBulan      = max($wajibBulan - $totalSetoranBulan, 0);
        $kelebihanBulan = max($totalSetoranBulan - $wajibBulan, 0);

        return view('setoran.riwayat', [
            'salesArea'         => $salesArea,
            'setorans'          => $setorans,
            'tahun'             => $tahun,
            'bulan'             => $bulan,
            'namaBulan'         => $namaBulan,
            'wajibBulan'        => $wajibBulan,
            'totalSetoranBulan' => $totalSetoranBulan,
            'kelebihanBulan'    => $kelebihanBulan,
            'sisaBulan'         => $sisaBulan,
        ]);
    }

    // 3. SIMPAN SETORAN (PAKAI PERIODE PILIHAN)
public function store(Request $request)
{
    $request->validate([
        'id_sales' => ['required', 'exists:sales,id_sales'],
        'id_area'  => [
            'required',
            'integer',
            Rule::exists('area_sales', 'id_area')->where(function ($q) use ($request) {
                $q->where('id_sales', $request->id_sales);
            }),
        ],
        'nominal'  => ['required', 'numeric', 'min:1'],
        'catatan'  => ['nullable', 'string'],
        'tahun'    => ['required', 'integer'],
        'bulan'    => ['required', 'integer', 'between:1,12'],
    ]);

    $idAdmin = DB::table('admins')
        ->where('user_id', Auth::id())
        ->value('id_admin');

    $tahun = (int) $request->input('tahun');
    $bulan = (int) $request->input('bulan');

    DB::table('setoran')->insert([
        'id_sales'        => $request->id_sales,
        'id_area'         => $request->id_area,
        'id_admin'        => $idAdmin,
        'tanggal_setoran' => now(),          // tanggal REAL setor
        'tahun'           => $tahun,         // PERIODE kewajiban
        'bulan'           => $bulan,         // PERIODE kewajiban
        'nominal'         => $request->nominal,
        'catatan'         => $request->catatan,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    return redirect()
        ->route('admin.setoran.riwayat', [
            'id_sales' => $request->id_sales,
            'id_area'  => $request->id_area,
            'tahun'    => $tahun,
            'bulan'    => $bulan,
        ])
        ->with('success', 'Setoran berhasil disimpan.');
}

    // EDIT, UPDATE, DESTROY boleh tetap seperti punyamu,
    // yang penting untuk DESTROY baca $request->tahun / $request->bulan 
    // dan redirect pakai itu.


    // ======================== //
    // 5. FORM EDIT SETORAN     //
    // ======================== //
    public function edit($id_setoran, Request $request)
    {
        $setoran = DB::table('setoran as st')
            ->join('sales as s', 's.id_sales', '=', 'st.id_sales')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->join('area as a', 'a.id_area', '=', 'st.id_area')
            ->select(
                'st.id_setoran',
                'st.id_sales',
                'st.id_area',
                'st.nominal',
                'st.catatan',
                'st.tanggal_setoran',
                'u.name as nama_sales',
                'a.nama_area'
            )
            ->where('st.id_setoran', $id_setoran)
            ->first();

        if (!$setoran) {
            abort(404);
        }

        $tahun = (int) $request->get('tahun', date('Y', strtotime($setoran->tanggal_setoran)));
        $bulan = (int) $request->get('bulan', date('m', strtotime($setoran->tanggal_setoran)));

        return view('setoran.edit', [
            'setoran' => $setoran,
            'tahun'   => $tahun,
            'bulan'   => $bulan,
        ]);
    }

    // ======================== //
    // 6. UPDATE SETORAN        //
    // ======================== //

    public function update(Request $request, $id_setoran)
    {
        $request->validate([
            'nominal' => ['required', 'numeric', 'min:1'],
            'catatan' => ['nullable', 'string'],
            'tahun'   => ['required', 'integer'],
            'bulan'   => ['required', 'integer', 'between:1,12'],
        ]);

        $setoran = DB::table('setoran')->where('id_setoran', $id_setoran)->first();
        if (!$setoran) {
            abort(404);
        }

        $tahun = (int) $request->input('tahun');
        $bulan = (int) $request->input('bulan');

        DB::table('setoran')
            ->where('id_setoran', $id_setoran)
            ->update([
                'nominal'    => $request->nominal,
                'catatan'    => $request->catatan,
                'tahun'      => $tahun,   // periode kewajiban
                'bulan'      => $bulan,   // periode kewajiban
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.setoran.riwayat', [
                'id_sales' => $setoran->id_sales,
                'id_area'  => $setoran->id_area,
                'tahun'    => $tahun,
                'bulan'    => $bulan,
            ])
            ->with('success', 'Setoran berhasil diperbarui.');
    }

    // ======================== //
    // 7. HAPUS SETORAN         //
    // ======================== //
public function destroy(Request $request, $id_setoran)
{
    $setoran = DB::table('setoran')->where('id_setoran', $id_setoran)->first();
    if (!$setoran) {
        abort(404);
    }

    $tahun = (int) $request->input('tahun', $setoran->tahun ?? now()->year);
    $bulan = (int) $request->input('bulan', $setoran->bulan ?? now()->month);

    DB::table('setoran')->where('id_setoran', $id_setoran)->delete();

    return redirect()
        ->route('admin.setoran.riwayat', [
            'id_sales' => $setoran->id_sales,
            'id_area'  => $setoran->id_area,
            'tahun'    => $tahun,
            'bulan'    => $bulan,
        ])
        ->with('success', 'Setoran berhasil dihapus.');
}

}
