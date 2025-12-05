<?php

// app/Http/Controllers/Sales/PembayaranSalesController.php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Area;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PembayaranSalesController extends Controller
{
    public function riwayat(Request $request)
    {
        $user  = Auth::user();
        $sales = $user->sales;

        $query = Pembayaran::with([
            'pelanggan.area',
            'sales.user',
            'user',
            'items.tagihan.langganan.paket',
        ]);

        // khusus pembayaran yg terkait sales ini saja
        if ($sales) {
            $query->where('id_sales', $sales->id_sales);
        }

        // filter2 sama seperti versi admin (optional, bisa kamu samakan)
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('no_pembayaran', 'like', "%{$search}%")
                  ->orWhereHas('pelanggan', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_bayar', $request->tanggal);
        }

        if ($request->filled('area_id')) {
            $query->whereHas('pelanggan', function ($q) use ($request) {
                $q->where('id_area', $request->area_id);
            });
        }


        $pembayaran = $query->orderByDesc('tanggal_bayar')->paginate(15);

        $areas     = Area::orderBy('nama_area')->get();
        $salesList = Sales::with('user')->orderBy('id_sales')->get();

        return view('seles2.pembayaran.riwayat', compact('pembayaran', 'areas', 'salesList'));
    }
}
