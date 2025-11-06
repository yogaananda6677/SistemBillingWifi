@php
    use Carbon\Carbon;
    // $row dan $key datang dari include
@endphp

{{-- PENDAPATAN --}}
<div class="modal fade" id="pendapatanModal-{{ $key }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pendapatan – {{ $row->label }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $detailPembayaran = $row->detail_pembayaran ?? collect();
                    $totalPendapatan  = $row->pendapatan ?? 0;
                @endphp

                @if($detailPembayaran->isEmpty())
                    @if($totalPendapatan > 0)
                        <p class="text-muted mb-0">
                            Detail transaksi tidak tersedia. Total pendapatan pada periode ini:
                            <strong>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong>
                        </p>
                    @else
                        <p class="text-muted mb-0">Tidak ada pembayaran pada periode ini.</p>
                    @endif
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No. Pembayaran</th>
                                    <th>Pelanggan</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailPembayaran as $item)
                                    <tr>
                                        <td>{{ $item->tanggal_bayar ? Carbon::parse($item->tanggal_bayar)->format('d/m/Y H:i') : '-' }}</td>
                                        <td>{{ $item->no_pembayaran }}</td>
                                        <td>{{ $item->nama_pelanggan ?? '-' }}</td>
                                        <td class="text-end">
                                            Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Total</td>
                                    <td class="text-end">
                                        Rp {{ number_format($detailPembayaran->sum('nominal'), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- KOMISI (hanya sales) --}}
@if($row->jenis === 'sales')
<div class="modal fade" id="komisiModal-{{ $key }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Komisi – {{ $row->label }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $detailKomisi  = $row->detail_komisi ?? collect();
                    $totalKomisi   = $row->total_komisi ?? 0;
                @endphp

                @if($detailKomisi->isEmpty())
                    @if($totalKomisi > 0)
                        <p class="text-muted mb-0">
                            Detail komisi per transaksi tidak tersedia. Total komisi pada periode ini:
                            <strong>Rp {{ number_format($totalKomisi, 0, ',', '.') }}</strong>
                        </p>
                    @else
                        <p class="text-muted mb-0">Tidak ada komisi pada periode ini.</p>
                    @endif
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal Bayar</th>
                                    <th>No. Pembayaran</th>
                                    <th>Pelanggan</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-end">Nominal Komisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailKomisi as $item)
                                    <tr>
                                        <td>{{ $item->tanggal_bayar ? Carbon::parse($item->tanggal_bayar)->format('d/m/Y H:i') : '-' }}</td>
                                        <td>{{ $item->no_pembayaran }}</td>
                                        <td>{{ $item->nama_pelanggan ?? '-' }}</td>
                                        <td class="text-end">{{ $item->jumlah_komisi }}</td>
                                        <td class="text-end">
                                            Rp {{ number_format($item->nominal_komisi, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="4" class="text-end">Total Komisi</td>
                                    <td class="text-end">
                                        Rp {{ number_format($detailKomisi->sum('nominal_komisi'), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- PENGELUARAN --}}
<div class="modal fade" id="pengeluaranModal-{{ $key }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pengeluaran – {{ $row->label }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $detailPengeluaran = $row->detail_pengeluaran ?? collect();
                    $totalPengeluaran  = $row->total_pengeluaran ?? 0;
                @endphp

                @if($detailPengeluaran->isEmpty())
                    @if($totalPengeluaran > 0)
                        <p class="text-muted mb-0">
                            Detail pengeluaran per transaksi tidak tersedia. Total pengeluaran approved periode ini:
                            <strong>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong>
                        </p>
                    @else
                        <p class="text-muted mb-0">Tidak ada pengeluaran approved pada periode ini.</p>
                    @endif
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal Approve</th>
                                    <th>Nama Pengeluaran</th>
                                    <th>Catatan</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailPengeluaran as $item)
                                    <tr>
                                        <td>{{ $item->tanggal_approve ? Carbon::parse($item->tanggal_approve)->format('d/m/Y H:i') : '-' }}</td>
                                        <td>{{ $item->nama_pengeluaran }}</td>
                                        <td>{{ $item->catatan ?? '-' }}</td>
                                        <td class="text-end">
                                            Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Total Pengeluaran</td>
                                    <td class="text-end">
                                        Rp {{ number_format($detailPengeluaran->sum('nominal'), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
{{-- SETORAN --}}
<div class="modal fade" id="setoranModal-{{ $key }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Setoran – {{ $row->label }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $detailSetoran      = $row->detail_setoran ?? collect();
                    // ini yang kamu pakai di kolom "Setoran" tabel utama
                    $totalDialokBulanIni = $row->total_setoran ?? 0;
                    // ini total nominal setorannya (apa adanya)
                    $totalNominalSetor   = $detailSetoran->sum('nominal');
                @endphp

                @if($detailSetoran->isEmpty())
                    <p class="text-muted mb-0">
                        Belum ada setoran pada periode ini.
                    </p>
                @else
                    <p class="small mb-2 text-muted">
                        Kolom <strong>Nominal</strong> = jumlah uang yang disetor pada tanggal tersebut.  
                        Baris <strong>"Total dialokasikan ke bulan ini"</strong> di bawah
                        mengikuti perhitungan akumulasi (menutup kekurangan bulan-bulan sebelumnya).
                    </p>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal Setoran</th>
                                    <th>Admin Penerima</th>
                                    <th>Catatan</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailSetoran as $item)
                                    <tr>
                                        <td>
                                            {{ $item->tanggal_setoran
                                                ? \Carbon\Carbon::parse($item->tanggal_setoran)->format('d/m/Y H:i')
                                                : '-' }}
                                        </td>
                                        <td>{{ $item->nama_admin ?? '-' }}</td>
                                        <td>{{ $item->catatan ?? '-' }}</td>
                                        <td class="text-end">
                                            Rp {{ number_format($item->nominal ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Total nominal setoran ini</td>
                                    <td class="text-end">
                                        Rp {{ number_format($totalNominalSetor, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end small">
                                        Total yang <strong>dialokasikan ke bulan ini</strong>
                                    </td>
                                    <td class="text-end small fw-bold">
                                        Rp {{ number_format($totalDialokBulanIni, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endif

