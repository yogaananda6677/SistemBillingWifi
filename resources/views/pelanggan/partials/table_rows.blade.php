@if(isset($pelanggan) && $pelanggan->count() > 0)
    @foreach($pelanggan as $i => $p)
        @php
            $nomor = ($pelanggan->currentPage() - 1) * $pelanggan->perPage() + $i + 1;
            $totalTagihan = 0;
            foreach($p->langganan as $l) {
                $totalTagihan += $l->paket->harga ?? 0;
            }
        @endphp
        <tr>
            <td>{{ $nomor }}</td>
            <td>{{ $p->nama }}</td>
            <td>{{ $p->area->nama_area ?? '-' }}</td>
            <td>
                @foreach($p->langganan as $l)
                    {{ $l->paket->nama_paket ?? '-' }} ({{ $l->paket->kecepatan ?? '-' }} Mbps)<br>
                @endforeach
            </td>
            <td>Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
            <td>{{ $p->ip_address }}</td>
            <td>
                @if ($p->status_pelanggan == 'aktif')
                    <span class="badge bg-success">Aktif</span>
                @elseif($p->status_pelanggan == 'baru')
                    <span class="badge bg-warning">Baru</span>
                @elseif($p->status_pelanggan == 'isolir')
                    <span class="badge bg-secondary">Isolir</span>
                @else
                    <span class="badge bg-danger">Berhenti</span>
                @endif
            </td>
            <td>
                <a href="{{ route('pelanggan.edit', $p->id_pelanggan) }}" class="btn btn-sm btn-primary">Edit</a>
                <button class="btn btn-sm btn-danger btn-delete" data-url="{{ route('pelanggan.destroy', $p->id_pelanggan) }}">
                    Hapus
                </button>
                <a href="{{ route('pelanggan.show', $p->id_pelanggan) }}" class="btn btn-sm btn-info">Detail</a>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="8" class="no-results">Tidak ada data pelanggan yang ditemukan</td>
    </tr>
@endif

