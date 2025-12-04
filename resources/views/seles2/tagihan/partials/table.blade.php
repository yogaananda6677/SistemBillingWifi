@php
    use Carbon\Carbon;
@endphp

@forelse($pelanggan as $i => $p)
    @php
        $langganan   = $p->langganan->sortByDesc('tanggal_mulai')->first();
        $paket       = $langganan?->paket;
        $tagihanList = $langganan?->tagihan ?? collect();

        $today = now();
        $noUrut = method_exists($pelanggan, 'firstItem')
            ? $pelanggan->firstItem() + $i
            : $i + 1;

        $infoTagihan     = '-';
        $statusText      = 'Tidak ada langganan';
        $statusClass     = 'bg-secondary';
        $mulaiBayarLbl   = '-';
        $tanggalJatuhLbl = '-';

        if ($langganan) {
            if ($tagihanList->isNotEmpty()) {
                $unpaid  = $tagihanList->where('status_tagihan','belum lunas')
                                       ->sortBy(fn($t)=>$t->tahun*100+$t->bulan);
                $paid    = $tagihanList->where('status_tagihan','lunas')
                                       ->sortBy(fn($t)=>$t->tahun*100+$t->bulan);
                $overdue = $unpaid->filter(fn($t)=>Carbon::parse($t->jatuh_tempo)->lt($today));

                $lastPaid = $paid->last();

                if ($unpaid->isNotEmpty()) {
                    $tanggalJatuhLbl = Carbon::parse($unpaid->first()->jatuh_tempo)
                        ->translatedFormat('d F Y');
                } elseif ($tagihanList->last()) {
                    $tanggalJatuhLbl = Carbon::parse($tagihanList->last()->jatuh_tempo)
                        ->translatedFormat('d F Y');
                }

                if ($lastPaid) {
                    $lastPaidLabel = Carbon::create($lastPaid->tahun,$lastPaid->bulan,1)
                        ->translatedFormat('F Y');
                    $infoTagihan = 'Lunas s/d ' . $lastPaidLabel;
                }

                if ($unpaid->count() > 0) {
                    $infoTagihan .= ' • Belum lunas ' . $unpaid->count() . ' bulan';
                }

                if ($unpaid->count() === 0) {
                    $statusText  = 'Lunas';
                    $statusClass = 'bg-success';
                } elseif ($overdue->count() > 0) {
                    $statusText  = 'Ada tunggakan (' . $overdue->count() . ' bln)';
                    $statusClass = 'bg-danger';
                } else {
                    $statusText  = 'Ada tagihan berjalan';
                    $statusClass = 'bg-warning text-dark';
                }

                if ($unpaid->isNotEmpty()) {
                    $d = $unpaid->first();
                    $mulaiBayarLbl = Carbon::create($d->tahun,$d->bulan,1)->translatedFormat('F Y');
                } elseif ($lastPaid) {
                    $mulaiBayarLbl = Carbon::create($lastPaid->tahun,$lastPaid->bulan,1)->addMonth()
                        ->translatedFormat('F Y');
                }
            } else {
                $statusText      = 'Belum pernah ditagihkan';
                $statusClass     = 'bg-info text-dark';
                $infoTagihan     = 'Belum ada tagihan dibuat';
                $tanggalJatuhLbl = '-';
                $mulaiBayarLbl   = now()->startOfMonth()->translatedFormat('F Y');
            }
        }
    @endphp

    <tr>
        <td>{{ $noUrut }}</td>
        <td>{{ $p->nama }}</td>
        <td>{{ $p->area->nama_area ?? '-' }}</td>

        <td>
            @if($paket)
                <div>{{ $paket->nama_paket }}</div>
                <small class="text-muted">
                    Rp {{ number_format($paket->harga_total ?? 0, 0, ',', '.') }}
                </small>
            @else
                <span class="text-muted">Tidak ada langganan</span>
            @endif
        </td>

        <td>{{ $tanggalJatuhLbl }}</td>
        <td>{{ $infoTagihan }}</td>

        <td>
            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
        </td>

        <td>{{ $mulaiBayarLbl }}</td>

        <td>
            @if($langganan)
                <button class="btn btn-primary btn-sm w-100"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-bayar-sales-{{ $p->id_pelanggan }}">
                    Bayar Periode
                </button>
            @else
                <button class="btn btn-secondary btn-sm w-100" disabled>
                    Tidak ada langganan
                </button>
            @endif
        </td>
    </tr>

@empty
    <tr>
        <td colspan="9" class="text-center text-muted py-3">
            Tidak ada pelanggan untuk ditampilkan.
        </td>
    </tr>
@endforelse
