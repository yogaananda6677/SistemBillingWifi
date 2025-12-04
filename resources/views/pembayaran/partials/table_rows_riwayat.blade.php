@forelse($pembayaran as $pay)
@php
    $no = method_exists($pembayaran, 'firstItem')
        ? $pembayaran->firstItem() + $loop->index
        : $loop->iteration;

    $pelanggan = $pay->pelanggan;
    $area      = $pelanggan?->area?->nama_area;

    // nama sales yang terkait pembayaran (langsung dari relasi pembayaran->sales->user)
    $salesName = $pay->sales?->user?->name
        ?? $pelanggan?->sales?->user?->name;

    // nama admin (user yang input) dari relasi pembayaran->user
    $adminName = $pay->user?->name;

    if (is_null($pay->id_sales)) {
        // pembayaran via admin
        $sumberText = 'Admin' . ($adminName ? ' - ' . $adminName : '');
        $badgeClass = 'bg-secondary';
    } else {
        // pembayaran via sales
        $sumberText = 'Sales' . ($salesName ? ' - ' . $salesName : '');
        $badgeClass = 'bg-info';
    }

    $modalId = 'modal-detail-pembayaran-' . $pay->id_pembayaran;
@endphp


    <tr>
        <td>{{ $no }}</td>

        <td>
            <strong>{{ $pay->no_pembayaran }}</strong><br>
            <small class="text-muted">ID: {{ $pay->id_pembayaran }}</small>
        </td>

        <td>{{ optional($pay->tanggal_bayar)->format('d/m/Y H:i') }}</td>

        <td>
            <div>{{ $pelanggan->nama ?? '-' }}</div>
            <small class="text-muted">{{ $area ?? '-' }}</small>
        </td>

        <td>
            <span class="badge {{ $badgeClass }}">
                {{ $sumberText }}
            </span>
        </td>


        <td>
            <strong>Rp {{ number_format($pay->nominal, 0, ',', '.') }}</strong>
        </td>

        <td>
            @if($pay->items->isEmpty())
                <span class="text-muted">Tidak ada detail tagihan</span>
            @else
                <button type="button"
                        class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#{{ $modalId }}">
                    Lihat Detail
                </button>
            @endif
        </td>
    </tr>

    @if($pay->items->isNotEmpty())
        @push('modals')
            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title mb-0">
                                    Detail Tagihan – {{ $pay->no_pembayaran }}
                                </h5>
                                <small class="text-muted">
                                    ID Pembayaran: {{ $pay->id_pembayaran }}
                                </small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <div><strong>Pelanggan:</strong> {{ $pelanggan->nama ?? '-' }}</div>
                                <div><strong>Area:</strong> {{ $area ?? '-' }}</div>
                                <div>
                                    <strong>Tanggal Bayar:</strong>
                                    {{ optional($pay->tanggal_bayar)->format('d/m/Y H:i') }}
                                </div>
                                <div>
                                    <strong>Total Pembayaran:</strong>
                                    Rp {{ number_format($pay->nominal, 0, ',', '.') }}
                                </div>
                            </div>

                            <form action="{{ route('pembayaran.item.bulkDestroy') }}" method="POST">
    @csrf
    @method('DELETE')

    <input type="hidden" name="id_pembayaran" value="{{ $pay->id_pembayaran }}">

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;" class="text-center">
                        <input type="checkbox" id="check-all-{{ $pay->id_pembayaran }}">
                    </th>
                    <th style="width: 140px;">Bulan</th>
                    <th>Paket</th>
                    <th style="width: 160px;">Nominal Bayar</th>
                </tr>
            </thead>
            <tbody>
            @foreach($pay->items as $item)
                @php
                    $tagihan    = $item->tagihan;
                    $langganan  = $tagihan?->langganan;
                    $paket      = $langganan?->paket;
                    $bulanTahun = $tagihan
                        ? \Carbon\Carbon::create($tagihan->tahun, $tagihan->bulan, 1)
                            ->translatedFormat('F Y')
                        : '-';
                @endphp
                <tr>
                    <td class="text-center">
                        <input type="checkbox"
                               name="items[]"
                               value="{{ $item->id_payment_item }}"
                               class="checkbox-item-{{ $pay->id_pembayaran }}">
                    </td>
                    <td>{{ $bulanTahun }}</td>
                    <td>{{ $paket->nama_paket ?? '-' }}</td>
                    <td>Rp {{ number_format($item->nominal_bayar, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        <button type="submit"
                class="btn btn-danger"
                onclick="return confirm('Hapus tagihan yang dipilih? Tagihan akan dikembalikan menjadi BELUM LUNAS.');">
            Hapus Terpilih
        </button>
    </div>
</form>

                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>

                    </div>
                </div>
            </div>
        @endpush
    @endif
@empty
    <tr>
        <td colspan="7" class="text-center text-muted">
            Belum ada data pembayaran.
        </td>
    </tr>
@endforelse
