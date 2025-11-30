@if(isset($pelanggan) && $pelanggan->count() > 0)
    @foreach($pelanggan as $i => $p)
        @php
            $nomor = ($pelanggan->currentPage() - 1) * $pelanggan->perPage() + $i + 1;

            // status asli dari DB
            $rawStatus = $p->status_pelanggan;

            // status untuk badge/tampilan (boleh dari accessor)
            $statusBadge = $p->status_pelanggan_efektif ?? $rawStatus;

            // tab yang sedang dibuka (baru/aktif/berhenti/isolir) → hanya untuk URL & label
            $statusHalaman = request('status', 'aktif');

            // langganan utama (paling baru)
            $langgananAktif = $p->langganan->sortByDesc('tanggal_mulai')->first();

            // === PILIH TANGGAL SESUAI STATUS ===
            $tanggalKolom = null;

            if ($rawStatus === 'baru') {
                // pelanggan baru → pakai tanggal_registrasi dari tabel pelanggan
                $tanggalKolom = $p->tanggal_registrasi;
            } elseif ($langgananAktif) {
                if ($rawStatus === 'aktif') {
                    $tanggalKolom = $langgananAktif->tanggal_mulai;
                } elseif ($rawStatus === 'isolir') {
                    $tanggalKolom = $langgananAktif->tanggal_isolir;
                } elseif ($rawStatus === 'berhenti') {
                    $tanggalKolom = $langgananAktif->tanggal_berhenti;
                }
            }

            // untuk routing balik ke halaman status yang sama
            $statusQuery = [
                'from'   => 'status',
                'status' => $statusHalaman,
            ];

            // kumpulkan semua tagihan belum lunas (dipakai di isolir & berhenti & modal)
            $tagihanBelumLunas = $p->langganan
                ->flatMap(fn ($l) => $l->tagihan)
                ->where('status_tagihan', 'belum lunas')
                ->sortBy(fn ($t) => $t->tahun * 100 + $t->bulan);

            // ID modal hapus tagihan
            $modalHapusId = 'modalHapusTagihan-' . $p->id_pelanggan;
        @endphp

        <tr>
            {{-- NO --}}
            <td>{{ $nomor }}</td>

            {{-- NAMA --}}
            <td>{{ $p->nama }}</td>

            {{-- AREA --}}
            <td>{{ $p->area->nama_area ?? '-' }}</td>

            {{-- SALES --}}
            <td>{{ $p->sales->user->name ?? '-' }}</td>

            {{-- PAKET + HARGA TOTAL --}}
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

            {{-- TANGGAL (DINAMIS: registrasi / aktif / berhenti / isolir) --}}
            <td>
                @if($tanggalKolom)
                    {{ \Carbon\Carbon::parse($tanggalKolom)->locale('id')->translatedFormat('d F Y') }}
                @else
                    -
                @endif
            </td>

            {{-- IP --}}
            <td>{{ $p->ip_address }}</td>

            {{-- STATUS BADGE --}}
            <td>
                @php
                    // pakai statusHalaman untuk warna badge,
                    // atau fallback ke statusBadge kalau dipakai di halaman "semua"
                    $statusColor = $statusHalaman ?? $statusBadge;
                @endphp

                <span class="badge
                    @if($statusColor == 'baru') bg-secondary
                    @elseif($statusColor == 'aktif') bg-success
                    @elseif($statusColor == 'isolir') bg-warning
                    @elseif($statusColor == 'berhenti') bg-danger
                    @else bg-secondary
                    @endif
                ">
                    {{ ucfirst($statusBadge) }}
                </span>
            </td>

            {{-- AKSI --}}
            <td>
                {{-- BARU / AKTIF --}}
                @if (in_array($statusHalaman, ['baru', 'aktif']))

                    <a href="{{ route('pelanggan.edit', array_merge(['pelanggan' => $p->id_pelanggan], $statusQuery)) }}"
                       class="btn btn-sm btn-primary mb-1">
                        Edit
                    </a>

                    <form action="{{ route('pelanggan.isolir', $p->id_pelanggan) }}"
                          method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-warning mb-1">Isolir</button>
                    </form>

                    <form action="{{ route('pelanggan.berhenti', $p->id_pelanggan) }}"
                          method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-danger mb-1">Berhenti</button>
                    </form>

                    <a href="{{ route('pelanggan.show', array_merge(['pelanggan' => $p->id_pelanggan], $statusQuery)) }}"
                       class="btn btn-sm btn-info mb-1">
                        Detail
                    </a>

                {{-- ISOLIR --}}
                @elseif ($statusHalaman == 'isolir')

                    <a href="{{ route('pelanggan.edit', array_merge(['pelanggan' => $p->id_pelanggan], $statusQuery)) }}"
                       class="btn btn-sm btn-primary mb-1">
                        Edit
                    </a>

                    <form action="{{ route('pelanggan.buka_isolir', $p->id_pelanggan) }}"
                          method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-success mb-1">Buka Isolir</button>
                    </form>

                    <form action="{{ route('pelanggan.berhenti', $p->id_pelanggan) }}"
                          method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-danger mb-1">Berhenti</button>
                    </form>

                    <a href="{{ route('pelanggan.show', array_merge(['pelanggan' => $p->id_pelanggan], $statusQuery)) }}"
                       class="btn btn-sm btn-info mb-1">
                        Detail
                    </a>

                    @if($tagihanBelumLunas->isNotEmpty())
                        <button type="button"
                                class="btn btn-sm btn-outline-danger mb-1"
                                data-bs-toggle="modal"
                                data-bs-target="#{{ $modalHapusId }}">
                            Hapus Tagihan
                        </button>
                    @endif

                {{-- BERHENTI --}}
                @elseif ($statusHalaman == 'berhenti')

                    <a href="{{ route('pelanggan.edit', array_merge(['pelanggan' => $p->id_pelanggan], $statusQuery)) }}"
                       class="btn btn-sm btn-primary mb-1">
                        Edit
                    </a>

                    <form action="{{ route('pelanggan.aktivasi', $p->id_pelanggan) }}"
                          method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-success mb-1">Aktifkan</button>
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

                    @if($tagihanBelumLunas->isNotEmpty())
                        <button type="button"
                                class="btn btn-sm btn-outline-danger mb-1"
                                data-bs-toggle="modal"
                                data-bs-target="#{{ $modalHapusId }}">
                            Hapus Tagihan
                        </button>
                    @endif

                @endif

                {{-- MODAL HAPUS TAGIHAN --}}
                @if($tagihanBelumLunas->isNotEmpty())
                    <div class="modal fade" id="{{ $modalHapusId }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Hapus Tagihan – {{ $p->nama }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <form action="{{ route('tagihan.hapus-pelanggan') }}"
                                      method="POST">
                                    @csrf @method('DELETE')

                                    <input type="hidden" name="id_pelanggan" value="{{ $p->id_pelanggan }}">

                                    <div class="modal-body">
                                        <p class="mb-2">
                                            Pilih tagihan yang akan dihapus untuk
                                            <strong>{{ $p->nama }}</strong>:
                                        </p>

                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered align-middle">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px;">
                                                            <input type="checkbox"
                                                                   onclick="document.querySelectorAll('.check-{{ $p->id_pelanggan }}').forEach(cb => cb.checked = this.checked)">
                                                        </th>
                                                        <th>Bulan</th>
                                                        <th>Total Tagihan</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($tagihanBelumLunas as $t)
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox"
                                                                       class="form-check-input check-{{ $p->id_pelanggan }}"
                                                                       name="tagihan_ids[]"
                                                                       value="{{ $t->id_tagihan }}">
                                                            </td>
                                                            <td>{{ sprintf('%02d',$t->bulan) }}-{{ $t->tahun }}</td>
                                                            <td>Rp {{ number_format($t->total_tagihan,0,',','.') }}</td>
                                                            <td>{{ ucfirst($t->status_tagihan) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <small class="text-muted">
                                            Catatan: Tagihan yang sudah dipilih akan dihapus permanen.
                                        </small>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-danger">Hapus Tagihan Terpilih</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                @endif
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="9" class="no-results text-center">
            Tidak ada data pelanggan yang ditemukan
        </td>
    </tr>
@endif
