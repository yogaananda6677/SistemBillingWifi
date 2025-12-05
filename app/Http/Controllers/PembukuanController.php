<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PembukuanController extends Controller
{
    /**
     * Halaman rekap pembukuan per sales
     */
    public function index(Request $request)
    {
        // === 1. Ambil filter dari request ===
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $areaId    = $request->input('area_id');
        $salesId   = $request->input('sales_id');

        // Default: pakai bulan berjalan kalau tidak diisi
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate   = Carbon::now()->endOfMonth()->toDateString();
        }

        // Bikin range datetime (karena di DB banyak yg datetime)
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime   = $endDate . ' 23:59:59';

        // === 2. Data untuk dropdown filter ===
        $areas = DB::table('area')
            ->orderBy('nama_area')
            ->get();

        $salesOptions = DB::table('sales as s')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->leftJoin('area as a', 's.id_area', '=', 'a.id_area')
            ->select(
                's.id_sales',
                'u.name as nama_sales',
                'u.no_hp',
                'a.nama_area'
            )
            ->orderBy('u.name')
            ->get();

        // === 3. Query summary global (total pendapatan, komisi, dsb) ===

        // 3.1 Total Pendapatan (pembayaran.nominal)
        $pembayaranSummaryQuery = DB::table('pembayaran as p')
            ->leftJoin('sales as s', 'p.id_sales', '=', 's.id_sales')
            ->leftJoin('area as a', 's.id_area', '=', 'a.id_area')
            ->whereBetween('p.tanggal_bayar', [$startDateTime, $endDateTime]);

        if ($salesId) {
            $pembayaranSummaryQuery->where('p.id_sales', $salesId);
        }
        if ($areaId) {
            $pembayaranSummaryQuery->where('s.id_area', $areaId);
        }

        $totalPendapatan = $pembayaranSummaryQuery->sum('p.nominal');

        // 3.2 Total Komisi (transaksi_komisi)
        // asumsi: total per baris = nominal_komisi * jumlah_komisi
        $komisiSummaryQuery = DB::table('transaksi_komisi as tk')
            ->leftJoin('sales as s', 'tk.id_sales', '=', 's.id_sales')
            ->leftJoin('area as a', 's.id_area', '=', 'a.id_area')
            ->whereBetween('tk.created_at', [$startDateTime, $endDateTime]);

        if ($salesId) {
            $komisiSummaryQuery->where('tk.id_sales', $salesId);
        }
        if ($areaId) {
            $komisiSummaryQuery->where('s.id_area', $areaId);
        }

        $totalKomisi = $komisiSummaryQuery->sum(DB::raw('tk.nominal_komisi * tk.jumlah_komisi'));

        // 3.3 Total Pengeluaran approved
        $pengeluaranSummaryQuery = DB::table('pengeluaran as pg')
            ->leftJoin('sales as s', 'pg.id_sales', '=', 's.id_sales')
            ->leftJoin('area as a', 's.id_area', '=', 'a.id_area')
            ->where('pg.status_approve', 'approved')
            ->whereBetween('pg.tanggal_pengajuan', [$startDateTime, $endDateTime]);

        if ($salesId) {
            $pengeluaranSummaryQuery->where('pg.id_sales', $salesId);
        }
        if ($areaId) {
            $pengeluaranSummaryQuery->where('s.id_area', $areaId);
        }

        $totalPengeluaran = $pengeluaranSummaryQuery->sum('pg.nominal');

        // 3.4 Total Setoran
        $setoranSummaryQuery = DB::table('setoran as st')
            ->leftJoin('sales as s', 'st.id_sales', '=', 's.id_sales')
            ->leftJoin('area as a', 's.id_area', '=', 'a.id_area')
            ->whereBetween('st.tanggal_setoran', [$startDateTime, $endDateTime]);

        if ($salesId) {
            $setoranSummaryQuery->where('st.id_sales', $salesId);
        }
        if ($areaId) {
            $setoranSummaryQuery->where('s.id_area', $areaId);
        }

        $totalSetoran = $setoranSummaryQuery->sum('st.nominal');

        // 3.5 Hitung total harus setor & belum setor
        $totalHarusSetor    = $totalPendapatan - $totalKomisi - $totalPengeluaran;
        $totalBelumDisetor  = $totalHarusSetor - $totalSetoran;

        $summary = [
            'total_pendapatan'    => $totalPendapatan,
            'total_komisi'        => $totalKomisi,
            'total_pengeluaran'   => $totalPengeluaran,
            'total_belum_disetor' => $totalBelumDisetor,
        ];

        // === 4. Subquery per jenis transaksi untuk REKAP per sales ===

        // 4.1 Subquery pendapatan per sales
        $pembayaranSub = DB::table('pembayaran')
            ->select(
                'id_sales',
                DB::raw('SUM(nominal) as pendapatan_kotor')
            )
            ->whereBetween('tanggal_bayar', [$startDateTime, $endDateTime])
            ->groupBy('id_sales');

        if ($salesId) {
            $pembayaranSub->where('id_sales', $salesId);
        }

        // 4.2 Subquery komisi per sales
        $komisiSub = DB::table('transaksi_komisi')
            ->select(
                'id_sales',
                DB::raw('SUM(nominal_komisi * jumlah_komisi) as total_komisi')
            )
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy('id_sales');

        if ($salesId) {
            $komisiSub->where('id_sales', $salesId);
        }

        // 4.3 Subquery pengeluaran approved per sales
        $pengeluaranSub = DB::table('pengeluaran')
            ->select(
                'id_sales',
                DB::raw('SUM(nominal) as pengeluaran_approved')
            )
            ->where('status_approve', 'approved')
            ->whereBetween('tanggal_pengajuan', [$startDateTime, $endDateTime])
            ->groupBy('id_sales');

        if ($salesId) {
            $pengeluaranSub->where('id_sales', $salesId);
        }

        // 4.4 Subquery setoran per sales
        $setoranSub = DB::table('setoran')
            ->select(
                'id_sales',
                DB::raw('SUM(nominal) as sudah_setor')
            )
            ->whereBetween('tanggal_setoran', [$startDateTime, $endDateTime])
            ->groupBy('id_sales');

        if ($salesId) {
            $setoranSub->where('id_sales', $salesId);
        }

        // === 5. Query utama rekap per sales ===

        $rekapQuery = DB::table('sales as s')
            ->leftJoin('users as u', 's.user_id', '=', 'u.id')
            ->leftJoin('area as a', 's.id_area', '=', 'a.id_area')
            ->leftJoinSub($pembayaranSub, 'pb', function ($join) {
                $join->on('s.id_sales', '=', 'pb.id_sales');
            })
            ->leftJoinSub($komisiSub, 'km', function ($join) {
                $join->on('s.id_sales', '=', 'km.id_sales');
            })
            ->leftJoinSub($pengeluaranSub, 'pg', function ($join) {
                $join->on('s.id_sales', '=', 'pg.id_sales');
            })
            ->leftJoinSub($setoranSub, 'st', function ($join) {
                $join->on('s.id_sales', '=', 'st.id_sales');
            })
            ->select(
                's.id_sales',
                'u.name as nama_sales',
                'u.no_hp',
                'a.nama_area',
                DB::raw('COALESCE(pb.pendapatan_kotor, 0) as pendapatan_kotor'),
                DB::raw('COALESCE(km.total_komisi, 0) as total_komisi'),
                DB::raw('COALESCE(pg.pengeluaran_approved, 0) as pengeluaran_approved'),
                DB::raw('COALESCE(st.sudah_setor, 0) as sudah_setor')
            )
            ->selectRaw('
                (COALESCE(pb.pendapatan_kotor, 0)
                 - COALESCE(km.total_komisi, 0)
                 - COALESCE(pg.pengeluaran_approved, 0)) as harus_setor
            ')
            ->selectRaw('
                ((COALESCE(pb.pendapatan_kotor, 0)
                 - COALESCE(km.total_komisi, 0)
                 - COALESCE(pg.pengeluaran_approved, 0))
                 - COALESCE(st.sudah_setor, 0)) as belum_disetor
            ')
            ->orderBy('u.name');

        // Filter area di query utama
        if ($areaId) {
            $rekapQuery->where('s.id_area', $areaId);
        }

        if ($salesId) {
            $rekapQuery->where('s.id_sales', $salesId);
        }

        // Pakai paginate biar enak di tabel
        $rekapSales = $rekapQuery->paginate(15)->withQueryString();

        // === 6. Kirim ke view ===
        return view('pembukuan.index', [
            'areas'        => $areas,
            'salesOptions' => $salesOptions,
            'summary'      => $summary,
            'rekapSales'   => $rekapSales,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
        ]);
    }

    /**
     * Detail pembukuan untuk 1 sales
     * /pembukuan/{id_sales}
     */
    public function show(Request $request, $idSales)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate   = Carbon::now()->endOfMonth()->toDateString();
        }

        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime   = $endDate . ' 23:59:59';

        // Info sales
        $sales = DB::table('sales as s')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->leftJoin('area as a', 's.id_area', '=', 'a.id_area')
            ->where('s.id_sales', $idSales)
            ->select(
                's.*',
                'u.name as nama_sales',
                'u.no_hp',
                'a.nama_area'
            )
            ->first();

        if (!$sales) {
            abort(404, 'Data sales tidak ditemukan');
        }

        // Detail Pendapatan
        $pendapatan = DB::table('pembayaran as p')
            ->leftJoin('pelanggan as pl', 'p.id_pelanggan', '=', 'pl.id_pelanggan')
            ->where('p.id_sales', $idSales)
            ->whereBetween('p.tanggal_bayar', [$startDateTime, $endDateTime])
            ->select(
                'p.*',
                'pl.nama as nama_pelanggan'
            )
            ->orderBy('p.tanggal_bayar', 'desc')
            ->get();

        // Detail Komisi
        $komisi = DB::table('transaksi_komisi as tk')
            ->leftJoin('pembayaran as p', 'tk.id_pembayaran', '=', 'p.id_pembayaran')
            ->leftJoin('pelanggan as pl', 'p.id_pelanggan', '=', 'pl.id_pelanggan')
            ->where('tk.id_sales', $idSales)
            ->whereBetween('tk.created_at', [$startDateTime, $endDateTime])
            ->select(
                'tk.*',
                'p.tanggal_bayar',
                'p.no_pembayaran',
                'pl.nama as nama_pelanggan',
                DB::raw('(tk.nominal_komisi * tk.jumlah_komisi) as total_komisi')
            )
            ->orderBy('tk.created_at', 'desc')
            ->get();

        // Detail Pengeluaran
        $pengeluaran = DB::table('pengeluaran as pg')
            ->where('pg.id_sales', $idSales)
            ->whereBetween('pg.tanggal_pengajuan', [$startDateTime, $endDateTime])
            ->orderBy('pg.tanggal_pengajuan', 'desc')
            ->get();

        // Detail Setoran
        $setoran = DB::table('setoran as st')
            ->leftJoin('admins as ad', 'st.id_admin', '=', 'ad.id_admin')
            ->leftJoin('users as u', 'ad.user_id', '=', 'u.id')
            ->where('st.id_sales', $idSales)
            ->whereBetween('st.tanggal_setoran', [$startDateTime, $endDateTime])
            ->select(
                'st.*',
                'u.name as nama_admin'
            )
            ->orderBy('st.tanggal_setoran', 'desc')
            ->get();

        // Hitung kecil ringkasan khusus sales ini
        $pendapatanTotal = $pendapatan->sum('nominal');
        $komisiTotal     = $komisi->sum('total_komisi');
        $pengeluaranApproved = $pengeluaran->where('status_approve', 'approved')->sum('nominal');
        $setoranTotal    = $setoran->sum('nominal');

        $harusSetor   = $pendapatanTotal - $komisiTotal - $pengeluaranApproved;
        $belumSetor   = $harusSetor - $setoranTotal;

        return view('pembukuan.show', [
            'sales'                => $sales,
            'startDate'            => $startDate,
            'endDate'              => $endDate,
            'pendapatan'           => $pendapatan,
            'komisi'               => $komisi,
            'pengeluaran'          => $pengeluaran,
            'setoran'              => $setoran,
            'ringkasanSales'       => [
                'pendapatan'   => $pendapatanTotal,
                'komisi'       => $komisiTotal,
                'pengeluaran'  => $pengeluaranApproved,
                'harus_setor'  => $harusSetor,
                'sudah_setor'  => $setoranTotal,
                'belum_setor'  => $belumSetor,
            ],
        ]);
    }
}
