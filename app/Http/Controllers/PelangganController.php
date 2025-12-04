<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Langganan;
use App\Models\Paket;
use App\Models\PaymentItem;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Sales;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PelangganController extends Controller
{
    /**
     * Halaman "Semua Pelanggan" (admin).
     */
    public function index(Request $request)
    {
        $dataSales       = Sales::with('user')->get();
        $dataArea        = Area::all();
        $dataPaket       = Paket::all();
        $totalPelanggan  = Pelanggan::count();

        // Untuk request AJAX (filter / pagination dinamis)
        if ($request->ajax()) {
            // pastikan mode default = "semua"
            $request->merge(['mode' => $request->get('mode', 'semua')]);

            return $this->getPelangganData($request);
        }

        $pelanggan = $this->basePelangganQuery()->paginate(10);

        return view('pelanggan.index', compact(
            'pelanggan',
            'dataSales',
            'dataArea',
            'dataPaket',
            'totalPelanggan'
        ));
    }

    /**
     * Halaman "Status Pelanggan" (baru / aktif / isolir / berhenti).
     * Catatan:
     * - "baru" sekarang berdasar tanggal_registrasi bulan & tahun ini, bukan status di DB.
     */
    public function status(Request $request)
    {
        $dataSales = Sales::with('user')->get();
        $dataArea  = Area::all();
        $dataPaket = Paket::all();

        $status = $request->get('status', 'aktif'); // default aktif
        $search = $request->get('search');
        $area   = $request->get('area');
        $sales  = $request->get('sales');

        $query = $this->basePelangganQuery();

        $this->applyCommonFilters($query, $search, $area, $sales);
        $this->applyStatusFilter($query, $status);

        $pelanggan    = $query->paginate(10)->appends($request->query());
        $statusCounts = $this->getStatusCounts();

        // AJAX (ganti isi tabel + pagination saja)
        if ($request->ajax()) {
            $html       = view('pelanggan.partials.table_rows_status', compact('pelanggan', 'status'))->render();
            $pagination = $pelanggan->links()->toHtml();

            return response()->json([
                'html'       => $html,
                'pagination' => $pagination,
                'total'      => $pelanggan->total(),
            ]);
        }

        // Non-AJAX
        return view('pelanggan.status', compact(
            'pelanggan',
            'dataSales',
            'dataArea',
            'dataPaket',
            'statusCounts',
            'status'
        ));
    }

    /**
     * Query dasar untuk semua list pelanggan (index, status, ajax).
     */
    private function basePelangganQuery(): Builder
    {
        return Pelanggan::with([
            'area',                // wilayah
            'sales.user',          // sales + user
            'langganan.paket',     // paket langganan
            'langganan.tagihan',   // tagihan-tagihan
        ]);
    }

    /**
     * Filter yang sering dipakai bareng (search / area / sales).
     */
    private function applyCommonFilters(Builder $query, ?string $search, ?string $areaId, ?string $salesId): void
    {
        if (!empty($search)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('nomor_hp', 'like', "%{$search}%")
                    ->orWhereHas('area', function (Builder $q2) use ($search) {
                        $q2->where('nama_area', 'like', "%{$search}%");
                    })
                    ->orWhereHas('langganan.paket', function (Builder $q3) use ($search) {
                        $q3->where('nama_paket', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($areaId)) {
            $query->where('id_area', $areaId);
        }

        if (!empty($salesId)) {
            $query->where('id_sales', $salesId);
        }
    }

    /**
     * Filter status untuk list pelanggan (baru / aktif / berhenti / isolir).
     *
     * Sekarang:
     * - "baru"  => tanggal_registrasi bulan & tahun ini (tanpa cek status_pelanggan).
     * - "aktif" => status_pelanggan = 'aktif'.
     * - lainnya => sesuai status_pelanggan.
     */
    private function applyStatusFilter(Builder $query, ?string $status): void
    {
        if (empty($status)) {
            return;
        }

        if ($status === 'baru') {
            // Pelanggan baru bulan & tahun ini (berdasarkan tanggal_registrasi)
            $query->whereMonth('tanggal_registrasi', now()->month)
                  ->whereYear('tanggal_registrasi', now()->year);
        } elseif ($status === 'aktif') {
            $query->where('status_pelanggan', 'aktif');
        } elseif (in_array($status, ['berhenti', 'isolir'])) {
            $query->where('status_pelanggan', $status);
        }
    }

    /**
     * Hitung jumlah per status untuk badge di halaman status.
     *
     * - 'baru'    => jumlah pelanggan dengan tanggal_registrasi bulan & tahun ini.
     * - 'aktif'   => status_pelanggan = 'aktif'.
     * - 'berhenti'=> status_pelanggan = 'berhenti'.
     * - 'isolir'  => status_pelanggan = 'isolir'.
     */
    private function getStatusCounts(): array
    {
        // Baru bulan ini (BERDASARKAN TANGGAL, bukan status)
        $baruBulanIni = Pelanggan::whereMonth('tanggal_registrasi', now()->month)
            ->whereYear('tanggal_registrasi', now()->year)
            ->count();

        // Aktif hanya status 'aktif'
        $aktifCount = Pelanggan::where('status_pelanggan', 'aktif')->count();

        $rows = Pelanggan::select('status_pelanggan', DB::raw('COUNT(*) as total'))
            ->groupBy('status_pelanggan')
            ->pluck('total', 'status_pelanggan')
            ->toArray();

        return [
            'baru'     => $baruBulanIni,
            'aktif'    => $aktifCount,
            'berhenti' => $rows['berhenti'] ?? 0,
            'isolir'   => $rows['isolir'] ?? 0,
        ];
    }

    /**
     * Endpoint AJAX list pelanggan (dipakai index & status).
     */
    private function getPelangganData(Request $request)
    {
        $search  = $request->get('search');
        $area    = $request->get('area');
        $status  = $request->get('status');
        $salesId = $request->get('sales');
        $mode    = $request->get('mode', 'semua'); // "semua" atau "status"

        $query = $this->basePelangganQuery();

        $this->applyCommonFilters($query, $search, $area, $salesId);
        $this->applyStatusFilter($query, $status);

        $pelanggan = $query->paginate(10);

        $partial = $mode === 'status'
            ? 'pelanggan.partials.table_rows_status'
            : 'pelanggan.partials.table_rows';

        $html       = view($partial, compact('pelanggan'))->render();
        $pagination = $pelanggan->links()->toHtml();

        return response()->json([
            'html'       => $html,
            'pagination' => $pagination,
            'total'      => $pelanggan->total(),
        ]);
    }

    /**
     * Form create.
     */
    public function create()
    {
        $dataSales = Sales::with('user')->get();
        $dataArea  = Area::all();
        $dataPaket = Paket::all();

        return view('pelanggan.create', compact('dataSales', 'dataArea', 'dataPaket'));
    }

    /**
     * Simpan pelanggan baru (+ setup langganan dan (opsional) tagihan awal).
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_sales'           => 'nullable|exists:sales,id_sales',
            'id_area'            => 'nullable|exists:area,id_area',
            'id_paket'           => 'required|exists:paket,id_paket',
            'nama'               => 'required|string|max:100',
            'nik'                => 'required|string|max:30',
            'alamat'             => 'required|string',
            'nomor_hp'           => 'required|string',
            'ip_address'         => 'required|string',
            // status_pelanggan: 'baru' DIHAPUS dari pilihan
            'status_pelanggan'   => 'required|in:aktif,berhenti,isolir',
            'tanggal_registrasi' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            // 1. PELANGGAN
            $pelanggan = Pelanggan::create([
                'id_sales'           => $request->id_sales,
                'id_area'            => $request->id_area,
                'nama'               => $request->nama,
                'nik'                => $request->nik,
                'alamat'             => $request->alamat,
                'nomor_hp'           => $request->nomor_hp,
                'ip_address'         => $request->ip_address,
                'status_pelanggan'   => $request->status_pelanggan,
                'tanggal_registrasi' => $request->tanggal_registrasi,
            ]);

            // 2. LANGGANAN
            $statusLangganan = Langganan::statusLanggananOptions($request->status_pelanggan);
            $tanggalMulai    = Carbon::parse($request->tanggal_registrasi)->toDateString();

            $langganan = Langganan::create([
                'id_pelanggan'     => $pelanggan->id_pelanggan,
                'id_paket'         => $request->id_paket,
                'tanggal_mulai'    => $tanggalMulai,
                'status_langganan' => $statusLangganan,
            ]);

            // 3. (OPSIONAL) LOGIKA TAGIHAN AWAL
            // Hanya untuk status_pelanggan 'aktif' sekarang
            if (in_array($request->status_pelanggan, ['aktif'])) {
                $paket        = Paket::findOrFail($request->id_paket);
                $hargaDasar   = $paket->harga_dasar;
                $ppnNominal   = $paket->ppn_nominal;
                $totalTagihan = $paket->harga_total;

                $mulai          = Carbon::parse($tanggalMulai);
                $firstBillMonth = $mulai->copy()->addMonthNoOverflow();
                $bulanTagihan   = $firstBillMonth->month;
                $tahunTagihan   = $firstBillMonth->year;
                $jatuhTempo     = Carbon::create($tahunTagihan, $bulanTagihan, 10, 23, 59, 59);

                // Kalau mau diaktifkan lagi tinggal buka blok ini dan sesuaikan:
                /*
                $pembayaran = Pembayaran::create([
                    'id_pelanggan'  => $pelanggan->id_pelanggan,
                    'id_sales'      => $request->id_sales,
                    'tanggal_bayar' => now(),
                    'nominal'       => $totalTagihan,
                    'no_pembayaran' => 'REG-' . strtoupper(Str::random(10)),
                ]);

                $tagihan = Tagihan::create([
                    'id_langganan'   => $langganan->id_langganan,
                    'bulan'          => $bulanTagihan,
                    'tahun'          => $tahunTagihan,
                    'harga_dasar'    => $hargaDasar,
                    'ppn_nominal'    => $ppnNominal,
                    'total_tagihan'  => $totalTagihan,
                    'status_tagihan' => 'lunas',
                    'jatuh_tempo'    => $jatuhTempo,
                ]);

                PaymentItem::create([
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'id_tagihan'    => $tagihan->id_tagihan,
                    'nominal_bayar' => $totalTagihan,
                ]);
                */
            }

            DB::commit();

            return redirect()
                ->route('pelanggan.index')
                ->with('success', 'Pelanggan berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan pelanggan.');
        }
    }

    /**
     * Detail pelanggan + riwayat pembayaran.
     */
    public function show($id)
    {
        $pelanggan = Pelanggan::with([
            'area',
            'sales.user',
            'langganan.paket',
        ])->findOrFail($id);

        $riwayatPembayaran = Pembayaran::with([
            'sales.user',
            'items.tagihan.langganan.paket',
        ])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->orderByDesc('tanggal_bayar')
            ->paginate(10);

        $items = collect();

        foreach ($riwayatPembayaran as $pay) {
            foreach ($pay->items as $item) {
                $items->push([
                    'pay'       => $pay,
                    'item'      => $item,
                    'tagihan'   => $item->tagihan,
                    'langganan' => $item->tagihan?->langganan,
                    'paket'     => $item->tagihan?->langganan?->paket,
                    'tahun'     => $item->tagihan?->tahun,
                    'bulan'     => $item->tagihan?->bulan,
                ]);
            }
        }

        $items = $items
            ->sortByDesc(fn($d) => $d['tahun'] * 100 + $d['bulan'])
            ->values();

        return view('pelanggan.show', compact('pelanggan', 'riwayatPembayaran', 'items'));
    }

    /**
     * Form edit.
     */
    public function edit($id)
    {
        $dataSales = Sales::with('user')->get();
        $dataArea  = Area::all();
        $dataPaket = Paket::all();
        $pelanggan = Pelanggan::findOrFail($id);

        return view('pelanggan.edit', compact('pelanggan', 'dataSales', 'dataArea', 'dataPaket'));
    }

    /**
     * Update pelanggan + langganan.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_sales'           => 'nullable|exists:sales,id_sales',
            'id_area'            => 'nullable|exists:area,id_area',
            'nama'               => 'required|string|max:100',
            'nik'                => 'required|string|max:30',
            'alamat'             => 'required|string',
            'nomor_hp'           => 'required|string',
            'ip_address'         => 'required|string',
            // 'baru' DIHAPUS dari pilihan
            'status_pelanggan'   => 'required|in:aktif,berhenti,isolir',
            'tanggal_registrasi' => 'required|date',
            'id_paket'           => 'required|exists:paket,id_paket',
        ]);

        $pelanggan = Pelanggan::findOrFail($id);

        DB::beginTransaction();

        try {
            $pelanggan->update([
                'id_sales'           => $request->id_sales,
                'id_area'            => $request->id_area,
                'nama'               => $request->nama,
                'nik'                => $request->nik,
                'alamat'             => $request->alamat,
                'nomor_hp'           => $request->nomor_hp,
                'ip_address'         => $request->ip_address,
                'status_pelanggan'   => $request->status_pelanggan,
                'tanggal_registrasi' => $request->tanggal_registrasi,
            ]);

            $statusLangganan = Langganan::statusLanggananOptions($request->status_pelanggan);
            $langganan       = Langganan::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

            if ($langganan) {
                $langganan->update([
                    'id_paket'         => $request->id_paket,
                    'status_langganan' => $statusLangganan,
                ]);
            } else {
                Langganan::create([
                    'id_pelanggan'     => $pelanggan->id_pelanggan,
                    'id_paket'         => $request->id_paket,
                    'tanggal_mulai'    => now(),
                    'status_langganan' => $statusLangganan,
                ]);
            }

            DB::commit();

            $redirectRoute = $request->input('redirect_to', 'pelanggan.index');

            return redirect()
                ->route($redirectRoute)
                ->with('success', 'Pelanggan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengupdate pelanggan.');
        }
    }

    /**
     * Hapus pelanggan + langganan.
     */
    public function destroy(Request $request, $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $pelanggan->langganan()->delete();
        $pelanggan->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pelanggan berhasil dihapus.',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    /**
     * Status pelanggan "baru" (admin).
     * Sekarang: berdasarkan tanggal_registrasi bulan & tahun ini, tanpa cek status_pelanggan.
     */
    public function statusBaru(Request $request)
    {
        $dataSales = Sales::with('user')->get();
        $dataArea  = Area::all();
        $dataPaket = Paket::all();

        $pelanggan = $this->basePelangganQuery()
            ->whereMonth('tanggal_registrasi', now()->month)
            ->whereYear('tanggal_registrasi', now()->year)
            ->paginate(10);

        return view('pelanggan.status', compact('pelanggan', 'dataSales', 'dataArea', 'dataPaket'));
    }

    /**
     * Pastikan tagihan bulan & tahun ini ada untuk langganan ini.
     */
    private function ensureTagihanBulanIni(Langganan $langganan): ?Tagihan
    {
        $langganan->loadMissing(['paket', 'pelanggan']);

        if (!$langganan->paket || !$langganan->pelanggan) {
            return null;
        }

        $today = now();
        $tahun = (int) $today->format('Y');
        $bulan = (int) $today->format('n');

        $existing = Tagihan::where('id_langganan', $langganan->id_langganan)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->first();

        if ($existing) {
            return $existing;
        }

        $mulai = $langganan->tanggal_mulai
            ? Carbon::parse($langganan->tanggal_mulai)
            : Carbon::parse($langganan->pelanggan->tanggal_registrasi ?? $today);

        $dayAktif      = $mulai->day;
        $endOfMonthDay = Carbon::create($tahun, $bulan, 1)->endOfMonth()->day;
        $dayJatuhTempo = min($dayAktif, $endOfMonthDay);

        $jatuhTempo = Carbon::create($tahun, $bulan, $dayJatuhTempo, 23, 59, 59);
        $paket      = $langganan->paket;

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

            $statusLangganan = Langganan::statusLanggananOptions('aktif');

            $langganan = Langganan::where('id_pelanggan', $pelanggan->id_pelanggan)
                ->whereNotNull('id_paket')
                ->orderByDesc('tanggal_mulai')
                ->first();

            if (!$langganan) {
                DB::rollBack();

                return back()->with('error', 'Tidak bisa aktivasi: pelanggan belum memiliki paket langganan.');
            }

            $langganan->update([
                'status_langganan' => $statusLangganan,
                'tanggal_mulai'    => now(),
                'tanggal_isolir'   => null,
                'tanggal_berhenti' => null,
            ]);

            $this->ensureTagihanBulanIni($langganan->fresh());

            DB::commit();

            return redirect()
                ->route('pelanggan.status', ['status' => 'aktif'])
                ->with('success', 'Pelanggan berhasil diaktivasi & tagihan bulan ini disiapkan.');

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

            $statusLangganan = Langganan::statusLanggananOptions('isolir');
            $langganan       = Langganan::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

            if ($langganan) {
                $langganan->update([
                    'status_langganan' => $statusLangganan,
                    'tanggal_isolir'   => now(),
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

            $statusLangganan = Langganan::statusLanggananOptions('aktif');

            $langganan = Langganan::where('id_pelanggan', $pelanggan->id_pelanggan)
                ->orderByDesc('tanggal_mulai')
                ->first();

            if ($langganan) {
                $langganan->update([
                    'status_langganan' => $statusLangganan,
                    'tanggal_isolir'   => null,
                ]);

                if ($langganan->id_paket) {
                    $this->ensureTagihanBulanIni($langganan->fresh());
                }
            }

            DB::commit();

            return redirect()
                ->route('pelanggan.status', ['status' => 'aktif'])
                ->with('success', 'Isolir pelanggan sudah dibuka & tagihan bulan ini disiapkan.');

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

            $statusLangganan = Langganan::statusLanggananOptions('berhenti');
            $langganan       = Langganan::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

            if ($langganan) {
                $langganan->update([
                    'status_langganan' => $statusLangganan,
                    'tanggal_berhenti' => now(),
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
