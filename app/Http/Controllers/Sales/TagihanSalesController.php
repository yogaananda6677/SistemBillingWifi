<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Langganan;
use App\Models\Paket;
use App\Models\PaymentItem;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Sales;
use App\Models\Tagihan;
use App\Models\TransaksiKomisi;
use App\Services\TagihanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TagihanSalesController extends Controller
{
    protected TagihanService $tagihanService;

    public function __construct(TagihanService $tagihanService)
    {
        $this->tagihanService = $tagihanService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $salesId = optional($user->sales)->id_sales;

        $query = Pelanggan::with([
            'area',
            'sales.user',
            'langganan.paket',
            'langganan.tagihan',
        ]);

        if ($salesId) {
            $query->where('id_sales', $salesId);
        } else {
            $query->whereRaw('1 = 0'); // kalau bukan sales / belum mapping
        }

        // FILTER SEARCH
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('nomor_hp', 'like', "%{$search}%");
            });
        }

        // FILTER AREA
        if ($areaId = $request->get('area')) {
            $query->where('id_area', $areaId);
        }

        // FILTER PAKET
        if ($paketId = $request->get('paket')) {
            $query->whereHas('langganan', function ($q) use ($paketId) {
                $q->where('id_paket', $paketId);
            });
        }

        $pelanggan = $query->paginate(15)->withQueryString();

        $dataArea = Area::orderBy('nama_area')->get();
        $paketList = Paket::orderBy('nama_paket')->get();

        // AJAX
        if ($request->ajax()) {
            $htmlTable = view('seles2.tagihan.partials.table', compact('pelanggan'))->render();
            $htmlPag = $pelanggan->links()->toHtml();
            $htmlModals = view('seles2.tagihan.partials.modals', compact('pelanggan'))->render();

            return response()->json([
                'html' => $htmlTable,
                'pagination' => $htmlPag,
                'modals' => $htmlModals,
            ]);
        }

        return view('seles2.tagihan.index', compact(
            'pelanggan', 'dataArea', 'paketList'
        ));
    }

    /**
     * BAYAR PERIODE OLEH SALES
     * Logika pemilihan tagihan disamakan dengan AdminTagihanController::bayarBanyak
     * bedanya: di sini ada komisi untuk sales.
     */
    public function bayarBanyak(Request $request)
    {
        $user = $request->user();
        $sales = optional($user)->sales;
        $salesId = optional($sales)->id_sales;

        if (! $salesId) {
            return back()->with('error', 'Anda tidak terdaftar sebagai sales.');
        }

        $request->validate([
            'id_langganan' => 'required|integer',
            'start_ym' => 'required|date_format:Y-m',
            'jumlah_bulan' => 'required|integer|min:1|max:60',
        ]);

        // Pastikan langganan milik sales yang login
        $langganan = Langganan::with(['paket', 'pelanggan', 'tagihan'])
            ->where('id_langganan', $request->id_langganan)
            ->whereHas('pelanggan', function ($q) use ($salesId) {
                $q->where('id_sales', $salesId);
            })
            ->firstOrFail();

        DB::beginTransaction();

        try {
            // --------- Ambil data form ---------
            $startYm = $request->start_ym;          // "2025-08"
            $targetCount = (int) $request->jumlah_bulan;

            [$startYear, $startMonth] = array_map('intval', explode('-', $startYm));
            $current = Carbon::create($startYear, $startMonth, 1); // pointer bulan berjalan

            $tagihanDiproses = collect();
            $allTagihan = $langganan->tagihan;

            // 2025-09 -> 202509, untuk cari bulan existing max
            $maxExistingYm = $allTagihan->isNotEmpty()
                ? $allTagihan->max(fn ($t) => (int) $t->tahun * 100 + (int) $t->bulan)
                : null;

            $loopGuard = 0;
            $maxLoop = 120; // 10 tahun ke depan

            while ($tagihanDiproses->count() < $targetCount && $loopGuard < $maxLoop) {
                $tahun = (int) $current->format('Y');
                $bulan = (int) $current->format('n');
                $ym = $tahun * 100 + $bulan;

                // 1. cari tagihan existing untuk bulan ini
                $tagihan = $allTagihan->first(function ($t) use ($tahun, $bulan) {
                    return (int) $t->tahun === $tahun && (int) $t->bulan === $bulan;
                });

                // 2. kalau belum ada tagihan
                if (! $tagihan) {
                    if (! is_null($maxExistingYm) && $ym <= $maxExistingYm) {
                        // ada GAP di masa lalu -> jangan create, skip saja
                        $current->addMonth();
                        $loopGuard++;

                        continue;
                    }

                    // bulan di masa depan → boleh create via service
                    $tagihan = $this->tagihanService->getOrCreateForMonth(
                        $langganan,
                        $tahun,
                        $bulan,
                        true // dibuat karena bayar periode
                    );

                    if (! $tagihan) {
                        $current->addMonth();
                        $loopGuard++;

                        continue;
                    }

                    // masukkan ke koleksi & update maxExistingYm
                    $allTagihan->push($tagihan);
                    $maxExistingYm = max($maxExistingYm ?? 0, $ym);
                }

                // 3. kalau sudah lunas → skip
                if ($tagihan->status_tagihan === 'lunas') {
                    $current->addMonth();
                    $loopGuard++;

                    continue;
                }

                // 4. siap diproses
                $tagihanDiproses->push($tagihan);

                $current->addMonth();
                $loopGuard++;
            }

            if ($tagihanDiproses->isEmpty()) {
                throw new \Exception('Tidak ada tagihan yang perlu dibayar pada periode ini.');
            }

            // semua tagihan dari 1 pelanggan
            $pelangganId = $langganan->pelanggan->id_pelanggan;
            $totalBayar = $tagihanDiproses->sum('total_tagihan');

            // ==================================================
            // BUAT PEMBAYARAN (oleh SALES)
            // ==================================================
            $pembayaran = Pembayaran::create([
                'id_pelanggan' => $pelangganId,
                'id_sales' => $salesId,
                // kalau di tabel kamu sudah tambahkan kolom id_user, boleh aktifkan:
                // 'id_user'       => Auth::id(),
                'tanggal_bayar' => now(),
                'nominal' => $totalBayar,
                'no_pembayaran' => $this->generateNoPembayaranSales(),
            ]);

            // ==================================================
            // PAYMENT ITEM + UPDATE TAGIHAN
            // ==================================================
            foreach ($tagihanDiproses as $t) {
                PaymentItem::create([
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'id_tagihan' => $t->id_tagihan,
                    'nominal_bayar' => $t->total_tagihan,
                ]);

                $t->update(['status_tagihan' => 'lunas']);
            }

            // ==================================================
            // KOMISI SALES
            // ==================================================
            // Asumsi: kolom `sales.komisi` = nominal per 1 tagihan/bulan.
            // Kalau kamu mau ubah jadi persen, tinggal ganti rumus di sini.
            if ($sales && $sales->komisi !== null) {
                $jumlahUnitKomisi = $tagihanDiproses->count();          // berapa bulan yg dibayar
                $nominalPerUnit = (int) $sales->komisi;               // dari tabel sales
                $totalKomisi = $jumlahUnitKomisi * $nominalPerUnit;

                if ($totalKomisi > 0) {
                    TransaksiKomisi::create([
                        'id_pembayaran' => $pembayaran->id_pembayaran,
                        'id_sales' => $salesId,
                        'nominal_komisi' => $totalKomisi,
                        'jumlah_komisi' => $jumlahUnitKomisi,
                    ]);
                }
            }

            // ==================================================
            // BUKU KAS
            // ==================================================
            // Untuk pembayaran oleh SALES biasanya masuk BukuKas saat SETORAN,
            // bukan saat terima uang. Jadi di sini sengaja TIDAK buat row buku_kas.
            // Nanti saat sales setor ke admin, baru dibuat di controller Setoran.
            // (kalau kamu mau langsung masuk buku_kas di sini pun bisa, tinggal tambah.)

            DB::commit();

            return back()->with('success', 'Pembayaran periode berhasil diproses oleh sales.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memproses pembayaran: '.$e->getMessage());
        }
    }

    protected function generateNoPembayaranSales(): string
    {
        $last = Pembayaran::orderByDesc('id_pembayaran')->first();
        $counter = ($last && str_contains($last->no_pembayaran, '-'))
            ? (int) substr($last->no_pembayaran, -4) + 1
            : 1;

        // prefix beda dengan admin ("ADM-") biar no_pembayaran unik & kebaca
        return 'SLS-'.now()->format('Ymd').'-'.str_pad($counter, 4, '0', STR_PAD_LEFT);
    }
}
