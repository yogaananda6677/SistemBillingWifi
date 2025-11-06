<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use Carbon\Carbon;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        // tambahkan 'area' di with()
        $query = Pengeluaran::with(['sales.user', 'admin', 'adminUser', 'area']);

        // filter status
        if ($request->filled('status')) {
            $query->where('status_approve', $request->status);
        }

        // search: sales name, nama_pengeluaran, atau nominal
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereHas('sales.user', function ($q2) use ($s) {
                    $q2->where('name', 'like', "%{$s}%");
                })
                ->orWhere('nama_pengeluaran', 'like', "%{$s}%")
                ->orWhere('nominal', 'like', "%{$s}%");
            });
        }

        // filter bulan (nama bulan Indonesia dari frontend)
        if ($request->filled('month')) {
            $bulanMap = [
                'Januari'   => 1,
                'Februari'  => 2,
                'Maret'     => 3,
                'April'     => 4,
                'Mei'       => 5,
                'Juni'      => 6,
                'Juli'      => 7,
                'Agustus'   => 8,
                'September' => 9,
                'Oktober'   => 10,
                'November'  => 11,
                'Desember'  => 12,
            ];

            $monthName = $request->month;
            if (isset($bulanMap[$monthName])) {
                $query->whereMonth('tanggal_pengajuan', $bulanMap[$monthName]);
            }
        }

        $pengajuan = $query
            ->orderBy('tanggal_pengajuan', 'desc')
            ->paginate(10)
            ->withQueryString();

        // response AJAX (dipakai script di blade)
        if ($request->ajax() || $request->wantsJson()) {
            $html       = view('pengeluaran.partials.table_rows', compact('pengajuan'))->render();
            $pagination = $pengajuan->links()->toHtml();

            return response()->json([
                'html'       => $html,
                'pagination' => $pagination,
            ]);
        }

        // pertama kali load halaman
        return view('pengeluaran.index', compact('pengajuan'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_approve' => 'required|in:pending,approved,rejected',
        ]);

        $data = Pengeluaran::findOrFail($id);

        $data->status_approve  = $request->status_approve;
        $data->id_admin        = auth()->user()->admin->id_admin ?? null;
        $data->tanggal_approve = now();
        $data->save();

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    public function showBukti(Pengeluaran $pengeluaran)
    {
        // INI VERSI ADMIN:
        // tidak pakai getSales() lagi, cukup cek file ada atau tidak.
        // Kalau mau, bisa tambahkan cek role admin:
        // if (auth()->user()->role !== 'admin') abort(403);

        if (!$pengeluaran->bukti_file) {
            abort(404, 'Bukti tidak ditemukan.');
        }

        $path = storage_path('app/public/' . $pengeluaran->bukti_file);

        if (!file_exists($path)) {
            abort(404, 'File bukti tidak ditemukan.');
        }

        return response()->file($path);
    }
}
