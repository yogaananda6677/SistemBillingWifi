@if(isset($pelanggan) && $pelanggan->count() > 0)
    @foreach($pelanggan as $i => $p)
    @php
        $nomor = ($pelanggan->currentPage() - 1) * $pelanggan->perPage() + $i + 1;

        // Ambil tanggal aktif dari langganan
        // Kalau cuma satu langganan per pelanggan, cukup first()
        $langgananAktif = $p->langganan->sortByDesc('tanggal_mulai')->first();
        $tanggalAktif   = $langgananAktif->tanggal_mulai ?? null;
    @endphp


        <tr>
            <td>{{ $nomor }}</td>
            <td>{{ $p->nama }}</td>
            <td>{{ $p->area->nama_area ?? '-' }}</td>

            {{-- 🔹 Kolom SALES ditambahkan di sini --}}
            <td>{{ $p->sales->user->name ?? '-' }}</td>

            <td>
                @foreach($p->langganan as $l)
                    {{ $l->paket->nama_paket ?? '-' }} ({{ $l->paket->kecepatan ?? '-' }} Mbps)<br>
                @endforeach
            </td>
            <td>
                @if($tanggalAktif)
                    {{ \Carbon\Carbon::parse($tanggalAktif)->locale('id')->translatedFormat('d F Y') }}
                @endif
            </td>
            <td>{{ $p->ip_address }}</td>
            <td>
                @php
                    $status = $p->status_pelanggan_efektif; // pakai accessor di model
                @endphp

                @if ($status == 'aktif')
                    <span class="badge bg-success">Aktif</span>
                @elseif ($status == 'baru')
                    <span class="badge bg-warning text-dark">Baru</span>
                @elseif ($status == 'isolir')
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
        {{-- sebelumnya colspan="8", sekarang jadi 9 karena ada 9 kolom --}}
        <td colspan="9" class="no-results">Tidak ada data pelanggan yang ditemukan</td>
    </tr>
@endif
