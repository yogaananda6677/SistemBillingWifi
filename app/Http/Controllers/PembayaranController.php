<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Services\TagihanService;

use App\Models\Langganan;
use App\Models\Pembayaran;
use App\Models\PaymentItem;
use App\Models\Sales;
use App\Models\Paket;
use App\Models\Area;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Auth;
class PembayaranController extends Controller
{
    protected TagihanService $tagihanService;

    public function __construct(TagihanService $tagihanService)
    {
        $this->tagihanService = $tagihanService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_langganan' => 'required|exists:langganan,id_langganan',
            'bulan'        => 'required|integer|min:1|max:12',
            'tahun'        => 'required|integer|min:2000',
            // Kalau mau: 'nominal' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $langganan = Langganan::with(['paket', 'pelanggan'])
                ->findOrFail($request->id_langganan);

            $bulan = (int) $request->bulan;
            $tahun = (int) $request->tahun;

            // 1. Ambil / buat tagihan bulan ini via SERVICE (tidak duplikatif)
            $tagihan = $this->tagihanService->getOrCreateForMonth($langganan, $tahun, $bulan);

            // 2. Cek kalau tagihan sudah lunas → jangan boleh dibayar lagi
            if ($tagihan->status_tagihan === 'lunas') {
                DB::rollBack();
                return back()->with('error', 'Tagihan bulan ini sudah lunas.');
            }

            // 3. Tentukan nominal pembayaran
            $nominalBayar = $request->filled('nominal')
                ? (float) $request->nominal
                : (float) $tagihan->total_tagihan;

            // Wajib full
            if ($nominalBayar < $tagihan->total_tagihan) {
                DB::rollBack();
                return back()->with('error', 'Nominal kurang dari total tagihan.');
            }
            
            // 4. Buat PEMBAYARAN
            $pembayaran = Pembayaran::create([
                'id_pelanggan'  => $langganan->id_pelanggan,
                'id_sales'      => optional($langganan->pelanggan)->id_sales,
                'id_user'       => Auth::id(), // <── admin / user yang input
                'tanggal_bayar' => now(),
                'nominal'       => $nominalBayar,
                'no_pembayaran' => 'INV-' . strtoupper(Str::random(10)),
            ]);

            // 5. LINK ke TAGIHAN via PAYMENT_ITEM
            PaymentItem::create([
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'id_tagihan'    => $tagihan->id_tagihan,
                'nominal_bayar' => $nominalBayar,
            ]);

            // 6. Update status tagihan → lunas
            $tagihan->update([
                'status_tagihan' => 'lunas',
            ]);

            DB::commit();

            return back()->with('success', 'Pembayaran berhasil, tagihan bulan ini sudah lunas.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memproses pembayaran.');
        }
    }

    public function riwayat(Request $request)
    {
        $query = Pembayaran::with([
            'pelanggan.area',
            'pelanggan.sales.user',
            'sales.user',
            'user', // <── tambahkan ini
            'items.tagihan.langganan.paket',
        ])->orderByDesc('tanggal_bayar');

        // SEARCH global
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('no_pembayaran', 'like', "%{$search}%")
                  ->orWhere('id_pembayaran', 'like', "%{$search}%")
                  ->orWhereHas('pelanggan', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  })
                  ->orWhereHas('pelanggan.area', function ($q3) use ($search) {
                      $q3->where('nama_area', 'like', "%{$search}%");
                  });
            });
        }

        // FILTER sumber
        if ($sumber = $request->sumber) {
            if ($sumber === 'admin') {
                $query->whereNull('id_sales');
            } elseif ($sumber === 'sales') {
                $query->whereNotNull('id_sales');
            }
        }

        // FILTER AREA
        if ($areaId = $request->area_id) {
            $query->whereHas('pelanggan.area', function ($q) use ($areaId) {
                $q->where('id_area', $areaId);
            });
        }

        // FILTER SALES
        if ($salesId = $request->sales_id) {
            $query->where(function ($q) use ($salesId) {
                $q->where('id_sales', $salesId)
                  ->orWhereHas('pelanggan.sales', function ($q2) use ($salesId) {
                      $q2->where('id_sales', $salesId);
                  });
            });
        }

        // FILTER tanggal dari / sampai
        if ($dari = $request->tanggal_dari) {
            $query->whereDate('tanggal_bayar', '>=', $dari);
        }

        if ($sampai = $request->tanggal_sampai) {
            $query->whereDate('tanggal_bayar', '<=', $sampai);
        }

        $pembayaran = $query->paginate(15)->withQueryString();

        // data dropdown
        $areas     = Area::orderBy('nama_area')->get();
        $salesList = Sales::with('user')->orderBy('id_sales')->get();

        // AJAX response
        if ($request->ajax()) {
            $tbody      = view('pembayaran.partials.table_rows_riwayat', compact('pembayaran'))->render();
            $pagination = $pembayaran->links()->toHtml();

            return response()->json([
                'tbody'      => $tbody,
                'pagination' => $pagination,
            ]);
        }

        return view('pembayaran.riwayat', compact('pembayaran', 'areas', 'salesList'));
    }

    /**
     * Hapus SATU item pembayaran (tombol "hapus" 1 baris)
     */
    public function hapusItem(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // Ambil item pembayaran + relasinya
            $item = PaymentItem::with(['pembayaran', 'tagihan'])->findOrFail($id);

            $pembayaran = $item->pembayaran;
            $tagihan    = $item->tagihan;

            // Flag: apakah tagihan ini sangat mungkin dibuat tepat saat pembayaran?
            $bolehHapusTagihan = false;

            if ($tagihan && $pembayaran && $tagihan->created_at && $pembayaran->tanggal_bayar) {
                // kalau created_at == tanggal_bayar (sampai level detik), anggap "dipaksa create"
                $bolehHapusTagihan = $tagihan->created_at->equalTo($pembayaran->tanggal_bayar);
            }

            // 1. Kalau "tagihan dipaksa create" → hapus saja tagihannya
            if ($tagihan && $bolehHapusTagihan) {
                $tagihan->delete();
            }
            // 2. Kalau tagihan biasa (auto generate / lama) → cuma kembalikan ke "belum lunas"
            elseif ($tagihan) {
                $tagihan->update([
                    'status_tagihan' => 'belum lunas',
                ]);
            }

            // 3. Kurangi nominal di tabel pembayaran
            if ($pembayaran) {
                $pembayaran->update([
                    'nominal' => max(0, $pembayaran->nominal - $item->nominal_bayar),
                ]);
            }

            // 4. Hapus item-nya
            $item->delete();

            // 5. Kalau pembayaran sudah tidak punya item lagi → hapus pembayaran
            if ($pembayaran && $pembayaran->items()->count() === 0) {
                $pembayaran->delete();
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item pembayaran berhasil dihapus.',
                ]);
            }

            return back()->with('success', 'Item pembayaran berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus item pembayaran.',
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus item pembayaran.');
        }
    }

    /**
     * Hapus BEBERAPA item sekaligus (checkbox "Hapus Terpilih")
     */
    public function hapusItemBulk(Request $request)
    {
        $request->validate([
            'items'         => 'required|array',
            'items.*'       => 'exists:payment_item,id_payment_item',
            'id_pembayaran' => 'nullable|exists:pembayaran,id_pembayaran',
        ], [
            'items.required' => 'Pilih dulu minimal satu tagihan yang mau dihapus.',
        ]);

        DB::beginTransaction();

        try {
            $items = PaymentItem::with(['pembayaran', 'tagihan'])
                ->whereIn('id_payment_item', $request->items)
                ->get();

            if ($items->isEmpty()) {
                DB::rollBack();
                return back()->with('error', 'Tidak ada item pembayaran yang ditemukan.');
            }

            // Group per pembayaran untuk hitung ulang total nominal
            $byPembayaran = $items->groupBy('id_pembayaran');

            // 1. Kembalikan status tagihan / hapus tagihan yang "dipaksa create" + hapus item
            foreach ($items as $item) {
                $tagihan    = $item->tagihan;
                $pembayaran = $item->pembayaran;

                $bolehHapusTagihan = false;

                if ($tagihan && $pembayaran && $tagihan->created_at && $pembayaran->tanggal_bayar) {
                    $bolehHapusTagihan = $tagihan->created_at->equalTo($pembayaran->tanggal_bayar);
                }

                if ($tagihan && $bolehHapusTagihan) {
                    // tagihan ini cuma ada karena dipaksa create saat bayar → hapus
                    $tagihan->delete();
                } elseif ($tagihan) {
                    // tagihan biasa → balikin ke belum lunas
                    $tagihan->update([
                        'status_tagihan' => 'belum lunas',
                    ]);
                }

                // hapus item payment-nya
                $item->delete();
            }

            // 2. Recalculate nominal per pembayaran
            foreach ($byPembayaran as $pembayaranId => $listItems) {
                $pembayaran = Pembayaran::with('items')->find($pembayaranId);

                if (! $pembayaran) {
                    continue;
                }

                $newTotal = $pembayaran->items->sum('nominal_bayar');

                if ($newTotal <= 0 || $pembayaran->items->count() === 0) {
                    $pembayaran->delete();
                } else {
                    $pembayaran->update([
                        'nominal' => $newTotal,
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Item pembayaran terpilih berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus item pembayaran.');
        }
    }
}
