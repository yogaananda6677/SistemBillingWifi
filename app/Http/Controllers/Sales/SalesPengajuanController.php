<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Pengeluaran;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class SalesPengajuanController extends Controller
{
    protected array $bulanMap = [
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

    protected function getSales()
    {
        $user = Auth::user();

        // pastikan role sales
        if ($user->role !== 'sales') {
            abort(403, 'Hanya sales yang boleh mengakses halaman ini.');
        }

        return Sales::where('user_id', $user->id)->firstOrFail();
    }

    public function index(Request $request)
    {
        $sales = $this->getSales();

        $query = Pengeluaran::with(['admin', 'adminUser'])
            ->where('id_sales', $sales->id_sales);

        // search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_pengeluaran', 'like', "%{$s}%")
                  ->orWhere('catatan', 'like', "%{$s}%")
                  ->orWhere('nominal', 'like', "%{$s}%");
            });
        }

        // filter status
        if ($request->filled('status')) {
            $query->where('status_approve', $request->status);
        }

        // filter bulan (nama bulan Indonesia)
        if ($request->filled('month') && isset($this->bulanMap[$request->month])) {
            $query->whereMonth('tanggal_pengajuan', $this->bulanMap[$request->month]);
        }

        $pengajuan = $query
            ->orderBy('tanggal_pengajuan', 'desc')
            ->paginate(10);

        // untuk request AJAX (search, filter, pagination)
        if ($request->ajax()) {
            $html = view('sales2.pengajuan.partials.table_rows', compact('pengajuan'))->render();
            $pagination = $pengajuan->links()->toHtml();

            return response()->json([
                'html'       => $html,
                'pagination' => $pagination,
            ]);
        }

        // pertama kali load halaman
        return view('seles2.pengajuan.index', compact('pengajuan'));
    }

    public function create()
    {
        $this->getSales(); // hanya cek role & data sales
        return view('seles2.pengajuan.create');
    }

public function store(Request $request)
{
    $sales = $this->getSales();

    $data = $request->validate([
        'nama_pengeluaran'  => 'required|string|max:255',
        // 'tanggal_pengajuan' DIHAPUS dari validasi
        'nominal'           => 'required|integer|min:1',
        'catatan'           => 'nullable|string',
        'bukti_file'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ]);

    $filePath = null;
    if ($request->hasFile('bukti_file')) {
        $filePath = $request->file('bukti_file')->store('pengeluaran', 'public');
    }

    $pengeluaran = new \App\Models\Pengeluaran();
    $pengeluaran->id_sales          = $sales->id_sales;
    $pengeluaran->nama_pengeluaran  = $data['nama_pengeluaran'];
    $pengeluaran->tanggal_pengajuan = now(); // ⬅️ otomatis tanggal & jam saat ini
    $pengeluaran->nominal           = $data['nominal'];
    $pengeluaran->catatan           = $data['catatan'] ?? null;
    $pengeluaran->bukti_file        = $filePath;
    $pengeluaran->status_approve    = 'pending';
    $pengeluaran->save();

    return redirect()
        ->route('sales.pengajuan.index')
        ->with('success', 'Pengajuan berhasil dikirim, menunggu persetujuan admin.');
}


    public function edit($id)
    {
        $sales = $this->getSales();

        $pengajuan = Pengeluaran::where('id_pengeluaran', $id)
            ->where('id_sales', $sales->id_sales)
            ->firstOrFail();

        // Biar fair: hanya boleh edit jika masih pending
        if ($pengajuan->status_approve !== 'pending') {
            return redirect()
                ->route('sales.pengajuan.index')
                ->with('error', 'Pengajuan yang sudah diproses tidak bisa diubah.');
        }

        return view('seles2.pengajuan.edit', compact('pengajuan'));
    }

public function update(Request $request, $id)
{
    $sales = $this->getSales();

    $pengajuan = Pengeluaran::where('id_pengeluaran', $id)
        ->where('id_sales', $sales->id_sales)
        ->firstOrFail();

    if ($pengajuan->status_approve !== 'pending') {
        return redirect()
            ->route('sales.pengajuan.index')
            ->with('error', 'Pengajuan yang sudah diproses tidak bisa diubah.');
    }

    $data = $request->validate([
        'nama_pengeluaran'  => 'required|string|max:255',
        // 'tanggal_pengajuan' DIHAPUS dari validasi
        'nominal'           => 'required|integer|min:1',
        'catatan'           => 'nullable|string',
        'bukti_file'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ]);

    if ($request->hasFile('bukti_file')) {
        if ($pengajuan->bukti_file) {
            Storage::disk('public')->delete($pengajuan->bukti_file);
        }
        $pengajuan->bukti_file = $request->file('bukti_file')->store('pengeluaran', 'public');
    }

    $pengajuan->nama_pengeluaran  = $data['nama_pengeluaran'];
    // $pengajuan->tanggal_pengajuan TIDAK diubah, biarkan tanggal awal
    $pengajuan->nominal           = $data['nominal'];
    $pengajuan->catatan           = $data['catatan'] ?? null;
    $pengajuan->save();

    return redirect()
        ->route('sales.pengajuan.index')
        ->with('success', 'Pengajuan berhasil diperbarui.');
}


    public function destroy($id)
    {
        $sales = $this->getSales();

        $pengajuan = Pengeluaran::where('id_pengeluaran', $id)
            ->where('id_sales', $sales->id_sales)
            ->firstOrFail();

        if ($pengajuan->status_approve !== 'pending') {
            return redirect()
                ->route('sales.pengajuan.index')
                ->with('error', 'Pengajuan yang sudah diproses tidak bisa dihapus.');
        }

        if ($pengajuan->bukti_file) {
            Storage::disk('public')->delete($pengajuan->bukti_file);
        }

        $pengajuan->delete();

        return redirect()
            ->route('sales.pengajuan.index')
            ->with('success', 'Pengajuan berhasil dihapus.');
    }

    public function showBukti(Pengeluaran $pengeluaran)
{
    // pastikan user adalah sales yang punya pengajuan ini
    $sales = $this->getSales();

    if ($pengeluaran->id_sales != $sales->id_sales) {
        abort(403, 'Tidak boleh mengakses bukti pengajuan sales lain.');
    }

    if (!$pengeluaran->bukti_file) {
        abort(404, 'Bukti tidak ditemukan.');
    }

    $path = storage_path('app/public/'.$pengeluaran->bukti_file);

    if (!file_exists($path)) {
        abort(404, 'File bukti tidak ditemukan.');
    }

    return response()->file($path);
}


}
