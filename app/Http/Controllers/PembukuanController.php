<?php

namespace App\Http\Controllers;

use App\Services\SalesSetoranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembukuanController extends Controller
{

public function index(Request $request)
{
    $selectedYear  = (int) ($request->input('tahun') ?? now()->year);
    $selectedMonth = (int) ($request->input('bulan') ?? now()->month);
    $keyPeriode    = sprintf('%04d-%02d', $selectedYear, $selectedMonth);

    // ====== STAT GLOBAL (biarkan seperti sebelumnya) ======
    $jumlahPelanggan = DB::table('pembayaran as p')
        ->whereYear('p.tanggal_bayar', $selectedYear)
        ->whereMonth('p.tanggal_bayar', $selectedMonth)
        ->distinct('p.id_pelanggan')
        ->count('p.id_pelanggan');

    $jumlahPembayaran = DB::table('pembayaran as p')
        ->whereYear('p.tanggal_bayar', $selectedYear)
        ->whereMonth('p.tanggal_bayar', $selectedMonth)
        ->sum('p.nominal');

    $jumlahPengeluaran = DB::table('pengeluaran as pg')
        ->where('pg.status_approve', 'approved')
        ->whereYear('pg.tanggal_approve', $selectedYear)
        ->whereMonth('pg.tanggal_approve', $selectedMonth)
        ->sum('pg.nominal');

    $stat = [
        'jumlah_pelanggan'   => $jumlahPelanggan,
        'jumlah_pembayaran'  => $jumlahPembayaran,
        'jumlah_pengeluaran' => $jumlahPengeluaran,
    ];

    $rows = collect();

    // ============================
    //  REKAP PER SALES
    // ============================
    $sales = DB::table('sales as s')
        ->join('users as u', 'u.id', '=', 's.user_id')
        ->select('s.id_sales', 'u.id as user_id', 'u.name')
        ->orderBy('u.name')
        ->get();

    foreach ($sales as $s) {

        // Ledger global: kewajiban + alokasi setoran lintas bulan
        $ledger = SalesSetoranService::buildLedger($s->id_sales);

        // Data kewajiban bulan ini
        $dataBulanIni = $ledger['monthlyKewajiban'][$keyPeriode] ?? [
            'pendapatan'  => 0,
            'komisi'      => 0,
            'pengeluaran' => 0,
            'wajib'       => 0,
        ];

        // Data alokasi setoran bulan ini (INI YANG PENTING)
        $saldoBulanIni = $ledger['saldoPerBulan'][$keyPeriode] ?? [
            'wajib'   => $dataBulanIni['wajib'],
            'dibayar' => 0,
            'kurang'  => $dataBulanIni['wajib'], // awalnya semua kurang
        ];

        $pendapatan  = (float) $dataBulanIni['pendapatan'];
        $komisi      = (float) $dataBulanIni['komisi'];
        $pengeluaran = (float) $dataBulanIni['pengeluaran'];

        // Wajib setor bulan ini (sesuai rumusmu)
        $wajib       = (float) $saldoBulanIni['wajib'];   // sama dengan pendapatan - komisi - pengeluaran
        // Setoran YANG SUDAH DIALOKASIKAN ke bulan ini
        $dibayar     = (float) $saldoBulanIni['dibayar']; // hasil alokasi dari semua setoran, bisa dari bulan lain
        // Kurang (+) atau kelebihan (-) setelah alokasi
        $kurang      = (float) $saldoBulanIni['kurang'];  // = wajib - dibayar

        // Supaya konsisten: selisih positif = kelebihan, negatif = masih kurang
        $selisihBulanIni = $dibayar - $wajib; // kebalikan dari 'kurang'

        // ========== DETAIL PERIODE (masih berdasarkan tanggal transaksi) ==========

        // Detail PEMBAYARAN bulan ini
        $detailPembayaran = DB::table('pembayaran as p')
            ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->select(
                'p.no_pembayaran',
                'p.tanggal_bayar',
                'p.nominal',
                'pl.nama as nama_pelanggan'
            )
            ->where('p.id_sales', $s->id_sales)
            ->whereYear('p.tanggal_bayar', $selectedYear)
            ->whereMonth('p.tanggal_bayar', $selectedMonth)
            ->orderBy('p.tanggal_bayar')
            ->get();

        // Detail KOMISI bulan ini
        $detailKomisi = DB::table('transaksi_komisi as tk')
            ->join('pembayaran as p', 'p.id_pembayaran', '=', 'tk.id_pembayaran')
            ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
            ->select(
                'p.no_pembayaran',
                'p.tanggal_bayar',
                'tk.nominal_komisi',
                'tk.jumlah_komisi',
                'pl.nama as nama_pelanggan'
            )
            ->where('tk.id_sales', $s->id_sales)
            ->whereYear('p.tanggal_bayar', $selectedYear)
            ->whereMonth('p.tanggal_bayar', $selectedMonth)
            ->orderBy('p.tanggal_bayar')
            ->get();

        // Detail PENGELUARAN bulan ini
        $detailPengeluaran = DB::table('pengeluaran as pg')
            ->select(
                'pg.nama_pengeluaran',
                'pg.tanggal_approve',
                'pg.nominal',
                'pg.catatan'
            )
            ->where('pg.id_sales', $s->id_sales)
            ->where('pg.status_approve', 'approved')
            ->whereYear('pg.tanggal_approve', $selectedYear)
            ->whereMonth('pg.tanggal_approve', $selectedMonth)
            ->orderBy('pg.tanggal_approve')
            ->get();

        // Detail SETORAN bulan ini (berdasarkan tanggal setor)
        $detailSetoran = DB::table('setoran as st')
            ->join('admins as a', 'a.id_admin', '=', 'st.id_admin')
            ->join('users as ua', 'ua.id', '=', 'a.user_id')
            ->select(
                'st.tanggal_setoran',
                'st.nominal',
                'st.catatan',
                'ua.name as nama_admin'
            )
            ->where('st.id_sales', $s->id_sales)
            ->whereYear('st.tanggal_setoran', $selectedYear)
            ->whereMonth('st.tanggal_setoran', $selectedMonth)
            ->orderBy('st.tanggal_setoran')
            ->get();

        $rows->push((object) [
            'jenis'              => 'sales',
            'label'              => $s->name . ' (sales)',
            'user_id'            => $s->user_id,
            'id_ref'             => $s->id_sales,

            'pendapatan'         => $pendapatan,
            'total_komisi'       => $komisi,
            'total_pengeluaran'  => $pengeluaran,
            'total_bersih'       => $wajib,        // ini sama dengan "harus setor bulan ini"
            'total_setoran'      => $dibayar,      // BUKAN setoran di bulan ini, tapi yang SUDAH DIALOKASIKAN KE BULAN INI
            'selisih'            => $selisihBulanIni, // >0 kelebihan, <0 masih kurang

            'saldo_global'       => $ledger['saldoGlobal'], // akumulasi semua bulan

            'detail_pembayaran'  => $detailPembayaran,
            'detail_komisi'      => $detailKomisi,
            'detail_pengeluaran' => $detailPengeluaran,
            'detail_setoran'     => $detailSetoran,
        ]);
    }



        // ============================
        //  REKAP PER ADMIN
        // ============================
        $admins = DB::table('admins as a')
            ->join('users as u', 'u.id', '=', 'a.user_id')
            ->select(
                'u.id as user_id',
                'u.name',
                DB::raw('MIN(a.id_admin) as id_admin')
            )
            ->groupBy('u.id', 'u.name')
            ->orderBy('u.name')
            ->get();

        foreach ($admins as $a) {
            // Pendapatan admin = pembayaran yang diinput user admin ini
            $pendapatanAdmin = DB::table('pembayaran as p')
                ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->whereYear('p.tanggal_bayar', $selectedYear)
                ->whereMonth('p.tanggal_bayar', $selectedMonth)
                ->where('p.id_user', $a->user_id)
                ->sum('p.nominal');

            $detailPembayaranAdmin = DB::table('pembayaran as p')
                ->leftJoin('pelanggan as pl', 'pl.id_pelanggan', '=', 'p.id_pelanggan')
                ->select(
                    'p.no_pembayaran',
                    'p.tanggal_bayar',
                    'p.nominal',
                    'pl.nama as nama_pelanggan'
                )
                ->whereYear('p.tanggal_bayar', $selectedYear)
                ->whereMonth('p.tanggal_bayar', $selectedMonth)
                ->where('p.id_user', $a->user_id)
                ->orderBy('p.tanggal_bayar')
                ->get();

            // Admin: komisi & pengeluaran dianggap 0, semua pendapatan = sudah setor
            $rows->push((object) [
                'jenis'              => 'admin',
                'label'              => $a->name . ' (admin)',
                'user_id'            => $a->user_id,
                'id_ref'             => $a->id_admin,

                'pendapatan'         => $pendapatanAdmin,
                'total_komisi'       => 0,
                'total_pengeluaran'  => 0,
                'total_bersih'       => $pendapatanAdmin,
                'total_setoran'      => $pendapatanAdmin,
                'selisih'            => 0,
                'saldo_global'       => 0,

                'detail_pembayaran'  => $detailPembayaranAdmin,
                'detail_komisi'      => collect(),
                'detail_pengeluaran' => collect(),
                'detail_setoran'     => collect(),
            ]);
        }

    // ============================
    //  REKAP PER ADMIN (boleh tetap seperti sebelumnya)
    // ============================
    // ... (bagian admin sama seperti yang sudah kamu punya) ...

    $rekap = $rows->sortBy('label')->values();

    return view('pembukuan.index', [
        'rekap'         => $rekap,
        'selectedYear'  => $selectedYear,
        'selectedMonth' => $selectedMonth,
        'stat'          => $stat,
    ]);

    }

    public function show(Request $request, $salesId)
    {
        // sementara tetap balik ke index, karena detail per sales bisa pakai halaman lain
        return redirect()->route('pembukuan.index');
    }
}
