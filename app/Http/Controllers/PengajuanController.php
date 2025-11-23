<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use Carbon\Carbon;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengeluaran::with(['sales.user','approvedBy']);

        // search: search by sales name, nama_pengeluaran, atau nominal
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s){
                $q->whereHas('sales.user', function($q2) use ($s){
                    $q2->where('name','like',"%{$s}%");
                })
                ->orWhere('nama_pengeluaran','like',"%{$s}%")
                ->orWhere('nominal','like',"%{$s}%");
            });
        }

        // optional: filter by month name (received from frontend)
        if ($request->filled('month')) {
            try {
                $monthName = $request->month;
                $monthIndex = Carbon::createFromFormat('F', $monthName)->month; // if english names; if not, skip
                $query->whereMonth('tanggal_pengajuan', $monthIndex);
            } catch(\Exception $e) {
                // safe fallback: ignore month if parse fails
            }
        }

        $pengajuan = $query->orderBy('tanggal_pengajuan','desc')->paginate(10)->withQueryString();

        // If AJAX requested, return JSON with rendered partial and pagination html
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('sales.pengajuan.partials.rows', compact('pengajuan'))->render();
            $pagination = view('vendor.pagination.bootstrap-4', ['paginator' => $pengajuan])->render();
            return response()->json([
                'html' => $html,
                'pagination' => $pagination,
            ]);
        }

        return view('sales.pengajuan.index', compact('pengajuan'));
    }

    // optional methods for approve/reject (if ingin nanti)
}
