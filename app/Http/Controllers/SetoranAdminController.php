<?php

namespace App\Http\Controllers;

use App\Services\SalesSetoranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetoranAdminController extends Controller
{
    // ======================== //
    // 1. LIST SEMUA SALES      //
    // ======================== //
    public function index()
    {
        $salesList = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->select('s.id_sales', 'u.name as nama_sales')
            ->orderBy('u.name')
            ->get()
            ->map(function ($row) {
                $ledger = SalesSetoranService::buildLedger($row->id_sales);

                // total akumulasi
                $row->total_pendapatan  = $ledger['totalPendapatan'];
                $row->total_komisi      = $ledger['totalKomisi'];
                $row->total_pengeluaran = $ledger['totalPengeluaran'];
                $row->total_wajib       = $ledger['totalWajib'];
                $row->total_setoran     = $ledger['totalSetoran'];
                $row->saldo_global      = $ledger['saldoGlobal']; // total_setoran - total_wajib

                // 👉 dipakai di Blade
                $row->target_setor = $ledger['totalWajib'];              // kolom "Target Setor"
                $row->total_setor  = $ledger['totalSetoran'];           // kolom "Setor"
                $row->sisa         = $row->target_setor - $row->total_setor; // positif = masih kurang

                return $row;
            });

        return view('setoran.index', [
            'sales' => $salesList,
        ]);
    }

    // ======================== //
    // 2. RIWAYAT DETAIL SALES  //
    // ======================== //
    public function riwayat($id_sales, Request $request)
    {
        $tahun = (int) $request->get('tahun', now()->year);
        $bulan = (int) $request->get('bulan', now()->month);
        $namaBulan = now()->setYear($tahun)->setMonth($bulan)->translatedFormat('F');

        // Data sales
        $sales = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->select('s.id_sales', 'u.name as nama_sales')
            ->where('s.id_sales', $id_sales)
            ->first();

        if (!$sales) {
            abort(404);
        }

        // Bangun ledger global dari service
        $ledger = SalesSetoranService::buildLedger($id_sales);

        $ym = sprintf('%04d-%02d', $tahun, $bulan);

        // Wajib setor bulan ini (pendapatan - komisi - pengeluaran)
        $wajibBulan = $ledger['monthlyKewajiban'][$ym]['wajib'] ?? 0;

        // Hitung alokasi setoran yang benar-benar menutup kewajiban bulan ini
        // dan kelebihan di bulan ini (lebih == true)
        $alokUntukBulanIni   = 0; // dipakai untuk nutup kewajiban bulan ini
        $kelebihanBulanIni   = 0; // setoran ekstra di bulan ini (lebih)

        foreach ($ledger['allocDetail'] as $entries) {
            foreach ($entries as $aloc) {
                if ($aloc['periode'] === $ym) {
                    if (!empty($aloc['lebih'])) {
                        $kelebihanBulanIni += $aloc['nominal'];
                    } else {
                        $alokUntukBulanIni += $aloc['nominal'];
                    }
                }
            }
        }

        // Sisa kewajiban bulan ini = wajib - alokasi
        $sisaBulan = $wajibBulan - $alokUntukBulanIni;
        if ($sisaBulan < 0) {
            $sisaBulan = 0; // jaga-jaga, secara teori nggak akan negatif
        }

        // Ambil hanya setoran yang DIBAYAR bulan ini (untuk tabel riwayat)
        $setoransBulanIni = $ledger['setorans']->filter(function ($st) use ($tahun, $bulan) {
            return (int) substr($st->tanggal_setoran, 0, 4) === $tahun
                && (int) substr($st->tanggal_setoran, 5, 2) === $bulan;
        });

        return view('setoran.riwayat', [
            'sales'          => $sales,
            'setorans'       => $setoransBulanIni,
            'allocDetail'    => $ledger['allocDetail'],
            'saldoPerBulan'  => $ledger['saldoPerBulan'],
            'saldoGlobal'    => $ledger['saldoGlobal'],

            'tahun'          => $tahun,
            'bulan'          => $bulan,
            'namaBulan'      => $namaBulan,

            'wajibBulan'     => $wajibBulan,
            'alokBulanIni'   => $alokUntukBulanIni,
            'kelebihanBulan' => $kelebihanBulanIni,
            'sisaBulan'      => $sisaBulan,
        ]);
    }

    // ======================== //
    // 3. FORM TAMBAH SETORAN  //
    // ======================== //
    public function create($id_sales)
    {
        $sales = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->select('s.id_sales', 'u.name as nama_sales')
            ->where('s.id_sales', $id_sales)
            ->first();

        if (!$sales) {
            abort(404);
        }

        return view('setoran.create', [
            'sales' => $sales,
        ]);
    }

    // ======================== //
    // 4. SIMPAN SETORAN       //
    // ======================== //
    public function store(Request $request)
    {
        $request->validate([
            'id_sales' => ['required', 'exists:sales,id_sales'],
            'nominal'  => ['required', 'numeric', 'min:1'],
            'catatan'  => ['nullable', 'string'],
        ]);

        DB::table('setoran')->insert([
            'id_sales'        => $request->id_sales,
            'id_admin'        => DB::table('admins')
                                    ->where('user_id', Auth::id())
                                    ->value('id_admin'),
            'tanggal_setoran' => now(),
            'nominal'         => $request->nominal,
            'catatan'         => $request->catatan,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()
            ->route('admin.setoran.riwayat', $request->id_sales)
            ->with('success', 'Setoran berhasil disimpan.');
    }
}
