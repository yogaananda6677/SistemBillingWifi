@if(isset($pelanggan) && $pelanggan->count() > 0)
    @foreach($pelanggan as $i => $p)
        @php
            $nomor = ($pelanggan->currentPage() - 1) * $pelanggan->perPage() + $i + 1;

            // Ambil langganan utama (paling baru / aktif)
            $langgananAktif = $p->langganan->sortByDesc('tanggal_mulai')->first();
            $tanggalAktif   = $langgananAktif->tanggal_mulai ?? null;
        @endphp

        <tr>
            {{-- No --}}
            <td>{{ $nomor }}</td>

            {{-- Nama --}}
            <td>{{ $p->nama }}</td>

            {{-- Area --}}
            <td>{{ $p->area->nama_area ?? '-' }}</td>

            {{-- Sales --}}
            <td>{{ $p->sales->user->name ?? '-' }}</td>

            {{-- Paket harga total --}}
            <td>
                @if($langgananAktif && $langgananAktif->paket)
                    <div>{{ $langgananAktif->paket->nama_paket }}</div>
                    <small>
                        Rp {{ number_format($langgananAktif->paket->harga_total ?? 0, 0, ',', '.') }}
                    </small>
                @else
                    -
                @endif
            </td>

            {{-- Tanggal Aktif --}}
            <td>
                @if($tanggalAktif)
                    {{ \Carbon\Carbon::parse($tanggalAktif)->locale('id')->translatedFormat('d F Y') }}
                @else
                    -
                @endif
            </td>

            {{-- IP Address --}}
            <td>{{ $p->ip_address }}</td>

            {{-- Status --}}
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

            {{-- Aksi --}}
            <td>
                <a href="{{ route('pelanggan.edit', $p->id_pelanggan) }}" class="btn btn-sm btn-primary">
                    Edit
                </a>

                <button 
                    type="button"
                    class="btn btn-sm btn-danger btn-delete" 
                    data-url="{{ route('pelanggan.destroy', $p->id_pelanggan) }}">
                    Hapus
                </button>

                <a href="{{ route('pelanggan.show', $p->id_pelanggan) }}" class="btn btn-sm btn-info">
                    Detail
                </a>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="9" class="no-results">Tidak ada data pelanggan yang ditemukan</td>
    </tr>
@endif
