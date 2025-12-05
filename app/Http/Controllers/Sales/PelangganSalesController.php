<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Area;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PelangganSalesController extends Controller
{
    /**
     * List pelanggan umum (lama) – boleh tetap dipakai.
     */
    public function index(Request $request)
    {
        $user  = Auth::user();
        $sales = $user->sales ?? null;

        $query = Pelanggan::with([
            'area',
            'langganan.tagihan',
        ]);

        if ($sales) {
            $query->where('id_sales', $sales->id_sales);
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nomor_hp', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $pelanggan = $query
            ->orderBy('nama')
            ->paginate(20)
            ->appends($request->query());

        return view('seles2.pelanggan.index', compact('pelanggan'));
    }

    /**
     * Halaman STATUS PELANGGAN (baru / aktif / berhenti / isolir) untuk sales.
     * Catatan:
     *  - "baru" sekarang BUKAN status di DB.
     *  - "baru" = pelanggan yang daftar di bulan & tahun ini (berdasarkan tanggal_registrasi).
     */
    public function status(Request $request)
    {
        $user  = Auth::user();
        $sales = $user->sales ?? null;

        if (!$sales) {
            abort(403, 'Sales tidak ditemukan');
        }

        $status = $request->get('status', 'aktif'); // default: aktif

        $query = Pelanggan::with([
                'area',
                'sales.user',
                'langganan.paket',
                'langganan.tagihan',
            ])
            ->where('id_sales', $sales->id_sales);

        // terapkan filter status (logika baru)
        $this->applyStatusFilter($query, $status);

        $pelanggan = $query
            ->orderBy('nama')
            ->paginate(20)
            ->appends($request->query());

        // hitung badge status per sales
        $statusCounts = $this->getStatusCountsForSales($sales->id_sales);

        // data area untuk filter di dropdown (kalau mau dipakai)
        $dataArea = Area::orderBy('nama_area')->get();

        return view('seles2.pelanggan.status', [
            'pelanggan'    => $pelanggan,
            'status'       => $status,
            'statusCounts' => $statusCounts,
            'dataArea'     => $dataArea,
        ]);
    }

    /**
     * Terapkan filter status ke query pelanggan.
     *
     * Sekarang:
     *  - status = 'baru'   -> filter berdasarkan tanggal_registrasi (bulan & tahun ini).
     *  - status = 'aktif'  -> status_pelanggan = 'aktif'.
     *  - status = 'berhenti' / 'isolir' -> sesuai status_pelanggan.
     */
    private function applyStatusFilter($query, string $status): void
    {
        if ($status === 'baru') {
            // "Pelanggan baru" = yang daftar bulan & tahun ini
            $query->whereMonth('tanggal_registrasi', now()->month)
                  ->whereYear('tanggal_registrasi', now()->year);
        } elseif ($status === 'aktif') {
            $query->where('status_pelanggan', 'aktif');
        } elseif (in_array($status, ['berhenti', 'isolir'])) {
            $query->where('status_pelanggan', $status);
        }
        // kalau status lain / nggak dikenali, biarkan tanpa filter tambahan
    }

    /**
     * Hitung jumlah per status untuk badge di halaman status (KHUSUS SALES).
     *
     * - 'baru'     => jumlah pelanggan milik sales ini yang registrasinya bulan & tahun ini.
     * - 'aktif'    => status_pelanggan = 'aktif'.
     * - 'berhenti' => status_pelanggan = 'berhenti'.
     * - 'isolir'   => status_pelanggan = 'isolir'.
     */
    private function getStatusCountsForSales(int $salesId): array
    {
        // base semua pelanggan milik sales ini
        $base = Pelanggan::where('id_sales', $salesId);

        // Baru bulan ini (BERDASARKAN TANGGAL, bukan status)
        $baruBulanIni = (clone $base)
            ->whereMonth('tanggal_registrasi', now()->month)
            ->whereYear('tanggal_registrasi', now()->year)
            ->count();

        // Aktif hanya yang status_pelanggan = 'aktif'
        $aktifCount = (clone $base)
            ->where('status_pelanggan', 'aktif')
            ->count();

        // Berhenti, isolir (di-group)
        $rows = (clone $base)
            ->select('status_pelanggan', DB::raw('COUNT(*) as total'))
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
     * DETAIL PELANGGAN (lama, tetap boleh dipakai untuk halaman detail).
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
            'user',
        ])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->orderByDesc('tanggal_bayar')
            ->paginate(10);

        return view('seles2.pelanggan.show', compact('pelanggan', 'riwayatPembayaran'));
    }

    /**
     * STATUS BAYAR (yang sudah ada – tetap).
     */
    public function statusBayar(Request $request)
    {
        $user  = Auth::user();
        $sales = $user->sales ?? null;

        $statusBayar = $request->get('status_bayar', 'belum'); // 'lunas' / 'belum'

        $query = Pelanggan::with([
            'area',
            'langganan.tagihan',
        ]);

        if ($sales) {
            $query->where('id_sales', $sales->id_sales);
        }

        $query->whereHas('langganan.tagihan', function ($q) use ($statusBayar) {
            if ($statusBayar === 'lunas') {
                $q->whereIn('status_tagihan', ['lunas', 'sudah lunas']);
            } else {
                $q->where('status_tagihan', 'belum lunas');
            }
        });

        if ($search = $request->get('q')) {
            $query->where(function ($q2) use ($search) {
                $q2->where('nama', 'like', "%{$search}%")
                   ->orWhere('nomor_hp', 'like', "%{$search}%")
                   ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        if ($area = $request->get('area')) {
            $query->whereHas('area', function ($q3) use ($area) {
                $q3->where('nama_area', 'like', "%{$area}%");
            });
        }

        $pelanggan = $query
            ->orderBy('nama')
            ->paginate(20)
            ->appends($request->query());

        $baseForCount = Pelanggan::query();

        if ($sales) {
            $baseForCount->where('id_sales', $sales->id_sales);
        }

        $countLunas = (clone $baseForCount)
            ->whereHas('langganan.tagihan', function ($q) {
                $q->whereIn('status_tagihan', ['lunas', 'sudah lunas']);
            })->count();

        $countBelum = (clone $baseForCount)
            ->whereHas('langganan.tagihan', function ($q) {
                $q->where('status_tagihan', 'belum lunas');
            })->count();

        $dataArea = Area::orderBy('nama_area')->get();

        return view('seles2.pelanggan.status-bayar', compact(
            'pelanggan',
            'statusBayar',
            'countLunas',
            'countBelum',
            'dataArea',
        ));
    }
}
