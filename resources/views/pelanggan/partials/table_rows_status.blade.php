@if(isset($pelanggan) && $pelanggan->count() > 0)
    @foreach($pelanggan as $i => $p)
        @php
            $nomor = ($pelanggan->currentPage() - 1) * $pelanggan->perPage() + $i + 1;

            $status = $p->status_pelanggan_efektif ?? $p->status_pelanggan;
            $statusHalaman = request('status', 'aktif');

            $langgananAktif = $p->langganan->sortByDesc('tanggal_mulai')->first();

            $tanggalKolom = null;
            if ($langgananAktif) {
                if ($statusHalaman === 'isolir') {
                    $tanggalKolom = $langgananAktif->tanggal_isolir;
                } elseif ($statusHalaman === 'berhenti') {
                    $tanggalKolom = $langgananAktif->tanggal_berhenti;
                } else {
                    $tanggalKolom = $langgananAktif->tanggal_mulai;
                }
            }

            $statusQuery = ['from' => 'status', 'status' => $statusHalaman];
        @endphp

        <tr>
            <td>{{ $nomor }}</td>
            <td>{{ $p->nama }}</td>
            <td>{{ $p->area->nama_area ?? '-' }}</td>
            <td>{{ $p->sales->user->name ?? '-' }}</td>

            <td>
                @if($p->langganan->count())
                    {{ $p->langganan->first()->paket->nama_paket ?? '-' }}
                @else
                    -
                @endif
            </td>

            <td>
                @if($tanggalKolom)
                    {{ \Carbon\Carbon::parse($tanggalKolom)->locale('id')->translatedFormat('d F Y') }}
                @else
                    -
                @endif
            </td>

            <td>{{ $p->ip_address }}</td>

            <td>
                <span class="badge 
                    @if($status == 'baru') bg-secondary
                    @elseif($status == 'aktif') bg-success
                    @elseif($status == 'isolir') bg-warning
                    @elseif($status == 'berhenti') bg-danger
                    @endif
                ">
                    {{ ucfirst($status) }}
                </span>
            </td>

            <td>
                {{-- AKSI SAMA SEPERTI VERSI TERAKHIRMU, tapi form biasa --}}
                @if (in_array($status, ['baru', 'aktif']))

                    <a href="{{ route('pelanggan.edit', array_merge(['pelanggan' => $p->id_pelanggan], $statusQuery)) }}"
                       class="btn btn-sm btn-primary mb-1">
                        Edit
                    </a>

                    <form action="{{ route('pelanggan.isolir', $p->id_pelanggan) }}"
                          method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-warning mb-1">
                            Isolir
                        </button>
                    </form>

                    <form action="{{ route('pelanggan.berhenti', $p->id_pelanggan) }}"
                          method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-danger mb-1">
                            Berhenti
                        </button>
                    </form>

                    <a href="{{ route('pelanggan.show', array_merge(['pelanggan' => $p->id_pelanggan], $statusQuery)) }}"
                       class="btn btn-sm btn-info mb-1">
                        Detail
                    </a>

                @elseif ($status == 'isolir')

                    <form action="{{ route('pelanggan.buka_isolir', $p->id_pelanggan) }}"
                          method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-success mb-1">
                            Buka Isolir
                        </button>
                    </form>

                    <form action="{{ route('pelanggan.berhenti', $p->id_pelanggan) }}"
                          method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-danger mb-1">
                            Berhenti
                        </button>
                    </form>

                    <a href="{{ route('pelanggan.show', array_merge(['pelanggan' => $p->id_pelanggan], $statusQuery)) }}"
                       class="btn btn-sm btn-info mb-1">
                        Detail
                    </a>

                @elseif ($status == 'berhenti')

                    <a href="{{ route('pelanggan.edit', array_merge(['pelanggan' => $p->id_pelanggan], $statusQuery)) }}"
                       class="btn btn-sm btn-primary mb-1">
                        Edit
                    </a>

                    <form action="{{ route('pelanggan.aktivasi', $p->id_pelanggan) }}"
                          method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-success mb-1">
                            Aktifkan
                        </button>
                    </form>

                    <button type="button"
                            class="btn btn-sm btn-outline-danger mb-1 btn-delete"
                            data-url="{{ route('pelanggan.destroy', $p->id_pelanggan) }}">
                        Hapus
                    </button>

                    <a href="{{ route('pelanggan.show', array_merge(['pelanggan' => $p->id_pelanggan], $statusQuery)) }}"
                       class="btn btn-sm btn-info mb-1">
                        Detail
                    </a>

                @endif
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="9" class="no-results">Tidak ada data pelanggan yang ditemukan</td>
    </tr>
@endif
