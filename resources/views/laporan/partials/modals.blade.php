@php
    use Carbon\Carbon;
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
                @if($row->detail_pembayaran->isEmpty())
                    <p class="text-muted mb-0">Tidak ada pembayaran pada periode ini.</p>
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
                                @foreach($row->detail_pembayaran as $item)
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
                                        Rp {{ number_format($row->pendapatan, 0, ',', '.') }}
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
                @if($row->detail_komisi->isEmpty())
                    <p class="text-muted mb-0">Tidak ada komisi pada periode ini.</p>
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
                                @foreach($row->detail_komisi as $item)
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
                                        Rp {{ number_format($row->total_komisi, 0, ',', '.') }}
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
                @if($row->detail_pengeluaran->isEmpty())
                    <p class="text-muted mb-0">Tidak ada pengeluaran approved pada periode ini.</p>
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
                                @foreach($row->detail_pengeluaran as $item)
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
                                        Rp {{ number_format($row->total_pengeluaran, 0, ',', '.') }}
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

{{-- SETORAN (sales saja yang punya) --}}
<div class="modal fade" id="setoranModal-{{ $key }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Setoran – {{ $row->label }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($row->detail_setoran->isEmpty())
                    <p class="text-muted mb-0">Belum ada setoran pada periode ini.</p>
                @else
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
                                @foreach($row->detail_setoran as $item)
                                    <tr>
                                        <td>{{ $item->tanggal_setoran ? Carbon::parse($item->tanggal_setoran)->format('d/m/Y H:i') : '-' }}</td>
                                        <td>{{ $item->nama_admin ?? '-' }}</td>
                                        <td>{{ $item->catatan ?? '-' }}</td>
                                        <td class="text-end">
                                            Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Total Setoran</td>
                                    <td class="text-end">
                                        Rp {{ number_format($row->total_setoran, 0, ',', '.') }}
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
