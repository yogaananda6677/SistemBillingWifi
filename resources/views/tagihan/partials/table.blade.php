@php
    use Carbon\Carbon;
@endphp

@forelse($pelanggan as $i => $p)
    @php
        $langganan   = $p->langganan->sortByDesc('tanggal_mulai')->first();
        $paket       = $langganan?->paket;
        $tagihanList = $langganan?->tagihan ?? collect();

        $today = now();
        $noUrut = $pelanggan->firstItem()
            ? $pelanggan->firstItem() + $i
            : $i + 1;

        // Tagihan bulan ini
        $tagihanBulanIni = $tagihanList->first(fn($t) =>
            $t->tahun == $today->year && $t->bulan == $today->month
        );

        // Tanggal jatuh tempo (bulan ini)
        $tanggalJatuhTempo = $tagihanBulanIni
            ? Carbon::parse($tagihanBulanIni->jatuh_tempo)->translatedFormat('d F Y')
            : '-';

        // Hitung status tagihan
        $unpaid = $tagihanList->where('status_tagihan', 'belum lunas');
        $tunggakan = $unpaid->filter(fn($t) =>
            Carbon::parse($t->jatuh_tempo)->lt($today)
        );
        $tunggakanCount = $tunggakan->count();

        // Tagihan terakhir lunas
        $lastPaid = $tagihanList->where('status_tagihan', 'lunas')
            ->sortBy(fn($t) => $t->tahun * 100 + $t->bulan)
            ->last();

        // Belum bayar bulan ini?
        $belumBayarBulanIni = $tagihanBulanIni &&
            $tagihanBulanIni->status_tagihan === 'belum lunas';

        // TOTAL TAGIHAN = total tunggakan x harga total paket
        // TOTAL TAGIHAN = jumlah total_tagihan dari semua tunggakan
        $totalTagihan = $tunggakan->sum('total_tagihan');

        $totalTagihanLabel = $totalTagihan > 0
            ? 'Rp ' . number_format($totalTagihan, 0, ',', '.')
            : '-';


        // WARNA BADGE & TEKS STATUS TAGIHAN
        if ($tunggakanCount >= 2) {
            $badgeClass = 'bg-danger text-white';
            $textStatus = "Belum Bayar $tunggakanCount Bulan";
        } elseif ($tunggakanCount == 1) {
            $badgeClass = 'bg-warning text-dark';
            $textStatus = "Belum Bayar 1 Bulan";
        } elseif ($belumBayarBulanIni) {
            $badgeClass = 'bg-secondary text-white';
            $textStatus = "Belum Bayar";
        } else {
            $badgeClass = 'bg-success text-white';
            $textStatus = "Lunas";
        }

        $modalId = "modalTunggakan-" . $p->id_pelanggan;

        // ===============================
        // STATUS PELANGGAN (aktif / isolir / berhenti / baru)
        // ===============================
        $pelangganStatus = $p->status_pelanggan_efektif ?? $p->status_pelanggan;
        $pelangganStatusLabel = ucfirst($pelangganStatus ?? '-');

        switch ($pelangganStatus) {
            case 'aktif':
                $pelangganStatusClass = 'bg-success';
                break;
            case 'baru':
                $pelangganStatusClass = 'bg-secondary';
                break;
            case 'isolir':
                $pelangganStatusClass = 'bg-warning text-dark';
                break;
            case 'berhenti':
                $pelangganStatusClass = 'bg-danger';
                break;
            default:
                $pelangganStatusClass = 'bg-secondary';
        }
    @endphp

    <tr>
        {{-- NO --}}
        <td>{{ $noUrut }}</td>

        {{-- NAMA --}}
        <td>{{ $p->nama }}</td>

        {{-- AREA & SALES --}}
        <td>
            <div>{{ $p->area->nama_area ?? '-' }}</div>
            <small class="text-muted">{{ $p->sales->user->name ?? '-' }}</small>
        </td>

        {{-- PAKET LAYANAN (atas nama paket, bawah harga total paket) --}}
        <td>
            @if($paket)
                <div>{{ $paket->nama_paket }}</div>
                <small class="text-muted">
                    Rp {{ number_format($paket->harga_total ?? 0, 0, ',', '.') }}
                </small>
            @else
                -
            @endif
        </td>

        {{-- IP ADDRESS --}}
        <td>{{ $p->ip_address }}</td>

        {{-- TANGGAL JATUH TEMPO --}}
        <td>{{ $tanggalJatuhTempo }}</td>

        {{-- TOTAL TAGIHAN = total tunggakan x harga total paket --}}
        <td>{{ $totalTagihanLabel }}</td>

        {{-- STATUS --}}
        <td>
            {{-- STATUS TAGIHAN --}}
            @if($tunggakanCount > 0)
                <button class="badge {{ $badgeClass }} border-0"
                        data-bs-toggle="modal"
                        data-bs-target="#{{ $modalId }}"
                        style="cursor:pointer;">
                    {{ $textStatus }}
                </button>
            @else
                <span class="badge {{ $badgeClass }}">{{ $textStatus }}</span>
            @endif

            {{-- TERAKHIR BAYAR --}}
            @if($lastPaid)
                @php
                    $lastPaidDate = Carbon::create($lastPaid->tahun, $lastPaid->bulan, 1);
                @endphp
                <br>
                <small class="text-muted">
                    Terakhir Bayar: {{ $lastPaidDate->translatedFormat('F Y') }}
                </small>
            @else
                <br>
                <small class="text-danger">Belum Pernah Bayar</small>
            @endif

            {{-- STATUS PELANGGAN (AKTIF / ISOLIR / BERHENTI / BARU) --}}
            <br>
            <small>
                <span class="badge {{ $pelangganStatusClass }} mt-1">
                    Pelanggan: {{ $pelangganStatusLabel }}
                </span>
            </small>

            {{-- MODAL DETAIL TUNGGAKAN --}}
            @if($tunggakanCount > 0)
                <div class="modal fade" id="{{ $modalId }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">Detail Tunggakan – {{ $p->nama }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <ul class="list-group">
                                    @foreach($tunggakan as $t)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <strong>
                                                    {{ Carbon::create($t->tahun, $t->bulan, 1)->translatedFormat('F Y') }}
                                                </strong>
                                            </span>
                                            <span>
                                                Rp {{ number_format($t->total_tagihan, 0, ',', '.') }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-dark" data-bs-dismiss="modal">Tutup</button>
                            </div>

                        </div>
                    </div>
                </div>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center text-muted">
            Tidak ada data pelanggan/tagihan
        </td>
    </tr>
@endforelse
