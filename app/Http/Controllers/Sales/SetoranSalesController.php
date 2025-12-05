<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\SalesSetoranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetoranSalesController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Ambil data sales dari user yang login
        $sales = DB::table('sales')
            ->where('user_id', $userId)
            ->first();

        if (!$sales) {
            return view('seles2.setoran.index', [
                'sales'            => null,
                'setorans'         => collect(),
                'allocDetail'      => [],
                'monthlyKewajiban' => [],
                'saldoPerBulan'    => [],
                'totalPendapatan'  => 0,
                'totalKomisi'      => 0,
                'totalPengeluaran' => 0,
                'totalWajib'       => 0,
                'totalSetoran'     => 0,
                'saldoGlobal'      => 0,
            ]);
        }

        // Ledger lengkap untuk sales ini
        $ledger = SalesSetoranService::buildLedger($sales->id_sales);

        return view('seles2.setoran.index', array_merge($ledger, [
            'sales' => $sales,
        ]));
    }
}
