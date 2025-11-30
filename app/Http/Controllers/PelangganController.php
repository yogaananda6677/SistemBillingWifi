<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Langganan;
use App\Models\Sales;
use App\Models\Paket;
use App\Models\Area;
use Illuminate\Support\Facades\DB;
// ⬇️ TAMBAHAN
use App\Models\Tagihan;
// use App\Models\Pembayaran;
use App\Models\PaymentItem;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Pembayaran;

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
        $totalPelanggan = Pelanggan::count();

        // Jika request AJAX, return data JSON untuk pagination
        if ($request->ajax()) {
            return $this->getPelangganData($request);
        }

        $pelanggan = Pelanggan::with(['area', 'sales.user', 'langganan.paket'])->paginate(10);
        return view('pelanggan.index', compact(
            'pelanggan',
            'dataSales',
            'dataArea',
            'dataPaket',
            'totalPelanggan'
        ));
    }
public function status(Request $request)
{
    $dataSales = Sales::with('user')->get();
    $dataArea  = Area::all();
    $dataPaket = Paket::all();

    $status = $request->get('status', 'aktif'); // default aktif
    $search = $request->get('search', '');
    $area   = $request->get('area', '');
    $sales  = $request->get('sales', '');       // 🔹 filter sales

    $query = Pelanggan::with([
        'area',
        'sales.user',
        'langganan.paket',
        'langganan.tagihan',
    ]);

    // FILTER SEARCH
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

    // FILTER WILAYAH
    if (!empty($area)) {
        $query->where('id_area', $area);
    }

    // 🔹 FILTER SALES
    if (!empty($sales)) {
        $query->where('id_sales', $sales);
    }

    // FILTER STATUS (logika sama kayak sebelumnya)
    if ($status === 'baru') {
        $query->where('status_pelanggan', 'baru')
              ->whereMonth('tanggal_registrasi', now()->month)
              ->whereYear('tanggal_registrasi', now()->year);
    } elseif ($status === 'aktif') {
        $query->whereIn('status_pelanggan', ['aktif', 'baru']);
    } elseif (in_array($status, ['berhenti', 'isolir'])) {
        $query->where('status_pelanggan', $status);
    }

    $pelanggan = $query->paginate(10)->appends($request->query());

    // total per status
    $statusCounts = $this->getStatusCounts();

    // 🔹 RESPONSE UNTUK AJAX (search/filter realtime + pagination)
    if ($request->ajax()) {
        $html       = view('pelanggan.partials.table_rows_status', compact('pelanggan', 'status'))->render();
        $pagination = $pelanggan->links()->toHtml();

        return response()->json([
            'html'       => $html,
            'pagination' => $pagination,
            'total'      => $pelanggan->total(),
        ]);
    }

    // 🔹 NON-AJAX: tampilan biasa
    return view('pelanggan.status', compact(
        'pelanggan',
        'dataSales',
        'dataArea',
        'dataPaket',
        'statusCounts',
        'status'
    ));
}
private function getStatusCounts()
{
    // === KHUSUS BARU: sama seperti filter di status() ===
    $baruBulanIni = Pelanggan::where('status_pelanggan', 'baru')
        ->whereMonth('tanggal_registrasi', now()->month)
        ->whereYear('tanggal_registrasi', now()->year)
        ->count();

    // === AKTIF: samakan juga dengan query di status() (aktif + baru) ===
    $aktifCount = Pelanggan::whereIn('status_pelanggan', ['aktif', 'baru'])->count();

    // === STATUS LAIN: boleh pakai hitung biasa ===
    $rows = Pelanggan::select('status_pelanggan', DB::raw('COUNT(*) as total'))
        ->groupBy('status_pelanggan')
        ->pluck('total', 'status_pelanggan')
        ->toArray();

    return [
        'baru'     => $baruBulanIni,
        'aktif'    => $aktifCount,
        'berhenti' => $rows['berhenti'] ?? 0,
        'isolir'   => $rows['isolir']   ?? 0,
    ];
}


    /**
     * Get pelanggan data for AJAX requests
     */
    private function getPelangganData(Request $request)
    {
        $search = $request->get('search', '');
        $area   = $request->get('area', '');
        $status = $request->get('status', '');
        // 🔹 TAMBAH INI
        $salesId = $request->get('sales', '');

        $query = Pelanggan::with([
            'area',
            'sales.user',
            'langganan.paket',
            'langganan.tagihan',
    ]);


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

        if (!empty($area)) {
            $query->where('id_area', $area);
        }

        // 🔹 FILTER SALES
        if (!empty($salesId)) {
            $query->where('id_sales', $salesId);
        }
        if (!empty($status)) {

            if ($status === 'baru') {
                $query->where('status_pelanggan', 'baru')
                    ->whereMonth('tanggal_registrasi', now()->month)
                    ->whereYear('tanggal_registrasi', now()->year);
            } elseif ($status === 'aktif') {
                $query->whereIn('status_pelanggan', ['aktif', 'baru']);

            } else {
                $query->where('status_pelanggan', $status);
            }
        }



        $pelanggan = $query->paginate(10);

        // mode: 'semua' (default) atau 'status'
        $mode = $request->get('mode', 'semua');

        $partial = $mode === 'status'
            ? 'pelanggan.partials.table_rows_status'   // untuk halaman Status Pelanggan (aksi lengkap)
            : 'pelanggan.partials.table_rows';         // untuk halaman Semua Pelanggan (aksi sederhana)

        $view = view($partial, compact('pelanggan'))->render();
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
        // 1. SIMPAN PELANGGAN
        $pelanggan = Pelanggan::create([
            'id_sales'           => $request->id_sales ?? null,
            'id_area'            => $request->id_area ?? null,
            'nama'               => $request->nama,
            'nik'                => $request->nik,
            'alamat'             => $request->alamat,
            'nomor_hp'           => $request->nomor_hp,
            'ip_address'         => $request->ip_address,
            'status_pelanggan'   => $request->status_pelanggan,
            'tanggal_registrasi' => $request->tanggal_registrasi,
        ]);

        // 2. SIMPAN LANGGANAN
        $status_langganan = Langganan::statusLanggananOptions($request->status_pelanggan);

        // pakai tanggal_registrasi sebagai tanggal_mulai (boleh diubah kalau mau now())
        $tanggalMulai = Carbon::parse($request->tanggal_registrasi)->toDateString();

        $langganan = Langganan::create([
            'id_pelanggan'    => $pelanggan->id_pelanggan,
            'id_paket'        => $request->id_paket,
            'tanggal_mulai'   => $tanggalMulai,
            'status_langganan'=> $status_langganan,
        ]);

        // 3. LOGIKA BILLING REGISTRASI
        //    hanya buat tagihan awal kalau status_pelanggan memang "baru" atau "aktif"
        if (in_array($request->status_pelanggan, ['baru', 'aktif'])) {

            // Ambil paket & hitungan dari paket (PPN dari paket)
            $paket = Paket::findOrFail($request->id_paket);

            $hargaDasar   = $paket->harga_dasar;
            $ppnNominal   = $paket->ppn_nominal;
            $totalTagihan = $paket->harga_total;

            // Bulan tagihan pertama = bulan setelah tanggal_mulai (bulan penuh pertama)
            $mulai = Carbon::parse($tanggalMulai);
            $firstBillMonth = $mulai->copy()->addMonthNoOverflow(); // bulan berikutnya

            $bulanTagihan = $firstBillMonth->month; // 1-12
            $tahunTagihan = $firstBillMonth->year;

            // Jatuh tempo misal tanggal 10 bulan tagihan
            $jatuhTempo = Carbon::create($tahunTagihan, $bulanTagihan, 10, 23, 59, 59);

            // // 3a. Buat PEMBAYARAN (uang registrasi: bayar 1 bulan)
            // $pembayaran = Pembayaran::create([
            //     'id_pelanggan'  => $pelanggan->id_pelanggan,
            //     'id_sales'      => $request->id_sales ?? null,
            //     'tanggal_bayar' => now(),
            //     'nominal'       => $totalTagihan,
            //     'no_pembayaran' => 'REG-' . strtoupper(Str::random(10)),
            // ]);

            // // 3b. Buat TAGIHAN bulan penuh pertama (bulan depan) dengan status LUNAS
            // $tagihan = Tagihan::create([
            //     'id_langganan'   => $langganan->id_langganan,
            //     'bulan'          => $bulanTagihan,
            //     'tahun'          => $tahunTagihan,
            //     'harga_dasar'    => $hargaDasar,
            //     'ppn_nominal'    => $ppnNominal,
            //     'total_tagihan'  => $totalTagihan,
            //     'status_tagihan' => 'lunas', // sudah dibayar saat registrasi
            //     'jatuh_tempo'    => $jatuhTempo,
            // ]);

            // // 3c. Hubungkan via PAYMENT_ITEM
            // PaymentItem::create([
            //     'id_pembayaran' => $pembayaran->id_pembayaran,
            //     'id_tagihan'    => $tagihan->id_tagihan,
            //     'nominal_bayar' => $totalTagihan,
            // ]);
        }

        DB::commit();

        return redirect()->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil dibuat dan tagihan awal sudah tercatat.');

    } catch (\Exception $e) {
        DB::rollBack();
        // sementara untuk debug:
        // dd($e->getMessage());
        return back()->with('error', 'Terjadi kesalahan!');
    }
}


    /**
     * Display the specified resource.
     */

public function show($id)
{
    $pelanggan = Pelanggan::with([
        'area',
        'sales.user',
        'langganan.paket',
    ])->findOrFail($id);

    // riwayat pembayaran khusus pelanggan ini
    $riwayatPembayaran = Pembayaran::with([
        'sales.user',
        'items.tagihan.langganan.paket',
    ])
        ->where('id_pelanggan', $pelanggan->id_pelanggan)
        ->orderByDesc('tanggal_bayar')
        ->paginate(10); // atau ->get() kalau mau tanpa pagination
        $items = collect();

foreach ($riwayatPembayaran as $pay) {
    foreach ($pay->items as $item) {
        $items->push([
            'pay'        => $pay,
            'item'       => $item,
            'tagihan'    => $item->tagihan,
            'langganan'  => $item->tagihan?->langganan,
            'paket'      => $item->tagihan?->langganan?->paket,
            'tahun'      => $item->tagihan?->tahun,
            'bulan'      => $item->tagihan?->bulan,
        ]);
    }
}

$items = $items->sortByDesc(fn($d) => $d['tahun'] * 100 + $d['bulan'])
               ->values();


    return view('pelanggan.show', compact('pelanggan', 'riwayatPembayaran', 'items'));
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
            $redirectRoute = $request->input('redirect_to', 'pelanggan.index');

            return redirect()->route($redirectRoute)
                ->with('success', 'Pelanggan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());

            return back()->with('error', 'Terjadi kesalahan!');
        }
    }



    /**
     * Remove the specified resource from storage.
     */
public function destroy(Request $request, $id)
{
    $pelanggan = Pelanggan::findOrFail($id);

    // hapus relasi dulu kalau perlu
    $pelanggan->langganan()->delete();
    $pelanggan->delete();

    // Kalau mau dukung AJAX juga, boleh cek dulu
    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dihapus.'
        ]);
    }

    // NON-AJAX (klik tombol hapus biasa) -> redirect balik
    return redirect()
        ->back()
        ->with('success', 'Pelanggan berhasil dihapus.');
}
    public function statusBaru(Request $request)
    {
        $dataSales = Sales::with('user')->get();
        $dataArea  = Area::all();
        $dataPaket = Paket::all();

        $pelanggan = Pelanggan::with(['area', 'sales.user', 'langganan.paket'])
            ->where('status_pelanggan', 'baru')
            ->whereMonth('tanggal_registrasi', now()->month)
            ->whereYear('tanggal_registrasi', now()->year)
            ->paginate(10);

        return view('pelanggan.status_baru', compact('pelanggan', 'dataSales', 'dataArea', 'dataPaket'));
    }

/**
 * Pastikan tagihan bulan & tahun ini ada untuk langganan ini.
 * Kalau sudah ada → kembalikan existing.
 * Kalau belum ada → buat baru.
 */
private function ensureTagihanBulanIni(Langganan $langganan): ?Tagihan
{
    $langganan->loadMissing(['paket', 'pelanggan']);

    // Kalau paket atau pelanggan tidak lengkap, jangan buat tagihan
    if (! $langganan->paket || ! $langganan->pelanggan) {
        return null;
    }

    $today = now();
    $tahun = (int) $today->format('Y');
    $bulan = (int) $today->format('n');

    // 1. Cek apakah SUDAH ada tagihan bulan ini
    $existing = Tagihan::where('id_langganan', $langganan->id_langganan)
        ->where('tahun', $tahun)
        ->where('bulan', $bulan)
        ->first();

    if ($existing) {
        return $existing;
    }

    // 2. Hitung jatuh tempo (sama pola dengan yang lain)
    $mulai = $langganan->tanggal_mulai
        ? Carbon::parse($langganan->tanggal_mulai)
        : Carbon::parse($langganan->pelanggan->tanggal_registrasi ?? $today);

    $dayAktif      = $mulai->day;
    $endOfMonthDay = Carbon::create($tahun, $bulan, 1)->endOfMonth()->day;
    $dayJatuhTempo = min($dayAktif, $endOfMonthDay);

    $jatuhTempo = Carbon::create($tahun, $bulan, $dayJatuhTempo, 23, 59, 59);

    $paket = $langganan->paket;

    return Tagihan::create([
        'id_langganan'   => $langganan->id_langganan,
        'bulan'          => $bulan,
        'tahun'          => $tahun,
        'harga_dasar'    => $paket->harga_dasar,
        'ppn_nominal'    => $paket->ppn_nominal,
        'total_tagihan'  => $paket->harga_total,
        'status_tagihan' => 'belum lunas',
        'jatuh_tempo'    => $jatuhTempo,
    ]);
}
public function aktivasi(Request $request, Pelanggan $pelanggan)
{
    DB::beginTransaction();

    try {
        $pelanggan->update([
            'status_pelanggan' => 'aktif',
        ]);

        $status_langganan = Langganan::statusLanggananOptions('aktif');

        // 🔴 AMBIL LANGGANAN TERAKHIR YANG PUNYA PAKET
        $langganan = Langganan::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->whereNotNull('id_paket')        // <-- ini penting
            ->orderByDesc('tanggal_mulai')
            ->first();

        // Kalau bener-bener tidak ada langganan dengan paket → jangan paksa aktivasi
        if (! $langganan) {
            DB::rollBack();
            return back()->with('error', 'Tidak bisa aktivasi: pelanggan belum memiliki paket langganan.');
        }

        // 🔵 Tetap pakai tanggal_mulai = sekarang (sesuai keinginanmu)
        $langganan->update([
            'status_langganan'  => $status_langganan,
            'tanggal_mulai'     => now(),   // kamu tetap boleh pakai now()
            'tanggal_isolir'    => null,
            'tanggal_berhenti'  => null,
        ]);

        // 🔹 DI SINI DIJAMIN sudah punya paket → langsung panggil helper
        $this->ensureTagihanBulanIni($langganan->fresh());

        DB::commit();

        return redirect()
            ->route('pelanggan.status', ['status' => 'aktif'])
            ->with('success', 'Pelanggan berhasil diaktivasi & tagihan bulan ini sudah disiapkan.');
    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()
            ->route('pelanggan.status', ['status' => 'aktif'])
            ->with('error', 'Gagal mengaktivasi pelanggan.');
    }
}



public function isolir(Request $request, Pelanggan $pelanggan)
{
    DB::beginTransaction();

    try {
        $pelanggan->update([
            'status_pelanggan' => 'isolir',
        ]);

        $status_langganan = Langganan::statusLanggananOptions('isolir');
        $langganan = Langganan::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

        if ($langganan) {
            $langganan->update([
                'status_langganan'  => $status_langganan,
                'tanggal_isolir'    => now(),
            ]);
        }

        DB::commit();

        return redirect()
            ->route('pelanggan.status', ['status' => 'isolir'])
            ->with('success', 'Pelanggan berhasil diisolir.');
    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()
            ->route('pelanggan.status', ['status' => 'isolir'])
            ->with('error', 'Gagal mengisolir pelanggan.');
    }
}
public function bukaIsolir(Request $request, Pelanggan $pelanggan)
{
    DB::beginTransaction();

    try {
        $pelanggan->update([
            'status_pelanggan' => 'aktif',
        ]);

        $status_langganan = Langganan::statusLanggananOptions('aktif');

        $langganan = Langganan::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->orderByDesc('tanggal_mulai')
            ->first();

        if ($langganan) {
            $langganan->update([
                'status_langganan'  => $status_langganan,
                // ❌ jangan sentuh tanggal_mulai
                'tanggal_isolir'    => null,
            ]);

            // 🔹 generate tagihan bulan ini kalau belum ada & paket sudah ada
            if ($langganan->id_paket) {
                $this->ensureTagihanBulanIni($langganan->fresh());
            }
        }

        DB::commit();

        return redirect()
            ->route('pelanggan.status', ['status' => 'aktif'])
            ->with('success', 'Isolir pelanggan sudah dibuka & tagihan bulan ini sudah disiapkan.');
    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()
            ->route('pelanggan.status', ['status' => 'aktif'])
            ->with('error', 'Gagal membuka isolir pelanggan.');
    }
}



public function berhenti(Request $request, Pelanggan $pelanggan)
{
    DB::beginTransaction();

    try {
        $pelanggan->update([
            'status_pelanggan' => 'berhenti',
        ]);

        $status_langganan = Langganan::statusLanggananOptions('berhenti');
        $langganan = Langganan::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

        if ($langganan) {
            $langganan->update([
                'status_langganan'  => $status_langganan,
                'tanggal_berhenti'  => now(),
            ]);
        }

        DB::commit();

        return redirect()
            ->route('pelanggan.status', ['status' => 'berhenti'])
            ->with('success', 'Pelanggan sudah diberhentikan.');
    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()
            ->route('pelanggan.status', ['status' => 'berhenti'])
            ->with('error', 'Gagal memberhentikan pelanggan.');
    }
}



}
