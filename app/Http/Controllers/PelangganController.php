<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Langganan;
use App\Models\Sales;
use App\Models\Paket;
use App\Models\Area;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $dataSales = Sales::with('user')->get();
        $dataArea = Area::all();
        $dataPaket = Paket::all();

        // Jika request AJAX, return data JSON untuk pagination
        if ($request->ajax()) {
            return $this->getPelangganData($request);
        }

        $pelanggan = Pelanggan::with(['area', 'sales.user', 'langganan.paket'])->paginate(10);
        return view('pelanggan.index', compact('pelanggan', 'dataSales', 'dataArea', 'dataPaket'));
    }

    /**
     * Get pelanggan data for AJAX requests
     */
    private function getPelangganData(Request $request)
    {
        $search = $request->get('search', '');
        $area = $request->get('area', '');
        $status = $request->get('status', '');

        $query = Pelanggan::with(['area', 'sales.user', 'langganan.paket']);

        // Filter pencarian
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('nomor_hp', 'like', "%{$search}%")
                  ->orWhereHas('area', function($q2) use ($search) {
                      $q2->where('nama_area', 'like', "%{$search}%");
                  })
                  ->orWhereHas('langganan.paket', function($q3) use ($search) {
                      $q3->where('nama_paket', 'like', "%{$search}%");
                  });
            });
        }

        // Filter wilayah
        if (!empty($area)) {
            $query->where('id_area', $area);
        }

        // Filter status
        if (!empty($status)) {
            $query->where('status_pelanggan', $status);
        }

        $pelanggan = $query->paginate(10);

        $view = view('pelanggan.partials.table_rows', compact('pelanggan'))->render();
        $pagination = $pelanggan->links()->toHtml();

        return response()->json([
            'html' => $view,
            'pagination' => $pagination,
            'total' => $pelanggan->total()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dataSales = Sales::with('user')->get();
        $dataArea = Area::all();
        $dataPaket = Paket::all();
        return view('pelanggan.create', compact('dataSales' , 'dataArea', 'dataPaket'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_sales' => 'nullable|exists:sales,id_sales',
            'id_area' => 'nullable|exists:area,id_area',
            'id_paket' => 'required',
            'nama' => 'required|string|max:100',
            'nik' => 'required|string|max:30',
            'alamat' => 'required|string',
            'nomor_hp' => 'required|string',
            'ip_address' => 'required|string',
            'status_pelanggan' => 'required|in:baru,aktif,berhenti,isolir',
            'tanggal_registrasi' => 'required|date'
        ]);

        DB::beginTransaction();

        try {
            // 1. Simpan pelanggan
            $pelanggan = Pelanggan::create([
                'id_sales'          => $request->id_sales ?? null,
                'id_area'           => $request->id_area ?? null,
                'nama'              => $request->nama,
                'nik'               => $request->nik,
                'alamat'            => $request->alamat,
                'nomor_hp'          => $request->nomor_hp,
                'ip_address'        => $request->ip_address,
                'status_pelanggan'  => $request->status_pelanggan,
                'tanggal_registrasi' => $request->tanggal_registrasi,
            ]);


            $status_langganan = Langganan::statusLanggananOptions($request->status_pelanggan);

            dd($status_langganan);

            // 2. Simpan langganan
            Langganan::create([
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'id_paket'     => $request->id_paket,
                'tanggal_mulai' => now(),
                'status_langganan'  => $status_langganan,
            ]);

            DB::commit();

            return redirect()->route('pelanggan.index')
                ->with('success', 'Pelanggan berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pelanggan = Pelanggan::with(['area', 'sales.user', 'langganan.paket'])->findOrFail($id);
        return view('pelanggan.show', compact('pelanggan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $dataSales = Sales::with('user')->get();
        $dataArea = Area::all();
        $dataPaket = Paket::all();
        $pelanggan = Pelanggan::findOrFail($id);
        return view('pelanggan.edit', compact('pelanggan','dataSales', 'dataArea', 'dataPaket'));
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'id_sales' => 'nullable|exists:sales,id_sales',
    //         'id_area' => 'nullable|exists:area,id_area',
    //         'nama' => 'required|string|max:100',
    //         'nik' => 'required|string|max:30',
    //         'alamat' => 'required|string',
    //         'nomor_hp' => 'required|string',
    //         'ip_address' => 'required|string',
    //         'status_pelanggan' => 'required|in:baru,aktif,berhenti,isolir',
    //         'tanggal_registrasi' => 'required|date',
    //         'id_paket' => 'required|exists:paket,id_paket'
    //     ]);

    //     // dd($request->all());

    //     $pelanggan = Pelanggan::findOrFail($id);
    //     DB::beginTransaction();

    //     try {

    //         // ======================
    //         // 1. UPDATE DATA PELANGGAN
    //         // ======================
    //         $pelanggan->update([
    //             'id_sales'          => $request->id_sales,
    //             'id_area'           => $request->id_area,
    //             'nama'              => $request->nama,
    //             'nik'               => $request->nik,
    //             'alamat'            => $request->alamat,
    //             'nomor_hp'          => $request->nomor_hp,
    //             'ip_address'        => $request->ip_address,
    //             'status_pelanggan'  => $request->status_pelanggan,
    //             'tanggal_registrasi' => $request->tanggal_registrasi,
    //         ]);

    //         // ======================
    //         // 2. UPDATE LANGGANAN
    //         // ======================
    //         $status_langganan = Langganan::statusLanggananOptions($request->status_pelanggan);
    //         $langganan = Langganan::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

    //         Langganan::where('id_langganan', $langganan->id_langganan)->update([
    //             'id_paket' => $request->id_paket,
    //             'status_langganan' => $status_langganan,
    //         ]);

    //         DB::commit();

    //         return redirect()->route('pelanggan.index')
    //             ->with('success', 'Pelanggan berhasil diperbarui');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Terjadi kesalahan!');
    //     }
    // }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_sales' => 'nullable|exists:sales,id_sales',
            'id_area' => 'nullable|exists:area,id_area',
            'nama' => 'required|string|max:100',
            'nik' => 'required|string|max:30',
            'alamat' => 'required|string',
            'nomor_hp' => 'required|string',
            'ip_address' => 'required|string',
            'status_pelanggan' => 'required|in:baru,aktif,berhenti,isolir',
            'tanggal_registrasi' => 'required|date',
            'id_paket' => 'required|exists:paket,id_paket'
        ]);

        $pelanggan = Pelanggan::findOrFail($id);

        DB::beginTransaction();

        try {

            // UPDATE PELANGGAN
            $pelanggan->update([
                'id_sales'          => $request->id_sales,
                'id_area'           => $request->id_area,
                'nama'              => $request->nama,
                'nik'               => $request->nik,
                'alamat'            => $request->alamat,
                'nomor_hp'          => $request->nomor_hp,
                'ip_address'        => $request->ip_address,
                'status_pelanggan'  => $request->status_pelanggan,
                'tanggal_registrasi'=> $request->tanggal_registrasi,
            ]);

            // UPDATE / CREATE LANGGANAN
            $status_langganan = Langganan::statusLanggananOptions($request->status_pelanggan);
            $langganan = Langganan::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

            if ($langganan) {
                // update
                $langganan->update([
                    'id_paket' => $request->id_paket,
                    'status_langganan' => $status_langganan,
                ]);
            } else {
                // create
                Langganan::create([
                    'id_pelanggan' => $pelanggan->id_pelanggan,
                    'id_paket' => $request->id_paket,
                    'tanggal_mulai' => now(),
                    'status_langganan' => $status_langganan,
                ]);
            }

            DB::commit();

            return redirect()->route('pelanggan.index')
                ->with('success', 'Pelanggan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan!');
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->langganan()->delete();
        $pelanggan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dihapus.'
        ]);
    }
}
