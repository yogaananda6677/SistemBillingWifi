@php
    use Carbon\Carbon;
@endphp

@foreach($pelanggan as $p)
    @php
        $langganan   = $p->langganan->sortByDesc('tanggal_mulai')->first();
        $tagihanList = $langganan?->tagihan ?? collect();

        if (!$langganan) {
            continue;
        }

        $tagihanBelumLunas = $tagihanList->where('status_tagihan', 'belum lunas')
                                         ->sortBy(fn($t) => $t->tahun * 100 + $t->bulan);

        $lastPaid = $tagihanList->where('status_tagihan', 'lunas')
            ->sortBy(fn($t) => $t->tahun * 100 + $t->bulan)
            ->last();

        if ($tagihanBelumLunas->isNotEmpty()) {
            $firstUnpaid = $tagihanBelumLunas->first();
            $startDate   = Carbon::create($firstUnpaid->tahun, $firstUnpaid->bulan, 1);
            $noteStart   = 'Mulai dari tagihan yang belum lunas paling awal';
        } elseif ($lastPaid) {
            $startDate = Carbon::create($lastPaid->tahun, $lastPaid->bulan, 1)->addMonth();
            $noteStart = 'Mulai setelah bulan terakhir yang sudah lunas';
        } else {
            $startDate = now()->startOfMonth();
            $noteStart = 'Belum ada tagihan, mulai dari bulan ini';
        }

        $startYm    = $startDate->format('Y-m');
        $startLabel = $startDate->translatedFormat('F Y');

        $maxMonths  = 60;
        $endPreview = $startDate->copy()->addMonths($maxMonths - 1);
        $endLabel   = $endPreview->translatedFormat('F Y');

        $hargaPerBulan = optional($langganan->paket)->harga_total ?? 0;

        $modalId = 'modal-bayar-' . $p->id_pelanggan;

        // 🔹 DAFTAR BULAN TAGIHAN SEBENARNYA (>= startDate), urut naik
        $bulanTagihan = $tagihanList
            ->sortBy(fn($t) => $t->tahun * 100 + $t->bulan)
            ->filter(function ($t) use ($startDate) {
                $curr = Carbon::create($t->tahun, $t->bulan, 1);
                return $curr->greaterThanOrEqualTo($startDate);
            })
            ->map(fn($t) => Carbon::create($t->tahun, $t->bulan, 1)->format('Y-m'))
            ->values()
            ->toArray();
    @endphp

    <div class="modal fade" id="{{ $modalId }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
<form class="modal-content form-bayar-periode-admin"
      action="{{ route('admin.tagihan.bayar-banyak') }}"
      method="POST"
      data-start-ym="{{ $startYm }}"
      data-start-label="{{ $startLabel }}"
      data-max-bulan="{{ $maxMonths }}"
      data-harga-per-bulan="{{ $hargaPerBulan }}"
      data-nama-pelanggan="{{ $p->nama }}"
      data-bulan-tagihan='@json($bulanTagihan)'>

                @csrf

                <input type="hidden" name="id_langganan" value="{{ $langganan->id_langganan }}">
                <input type="hidden" name="start_ym" value="{{ $startYm }}">

                <div class="modal-header">
                    <h5 class="modal-title">Bayar Periode – {{ $p->nama }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-1">
                        <strong>Mulai dibayar dari:</strong> {{ $startLabel }}
                    </p>
                    <p class="text-muted" style="font-size: 12px;">
                        {{ $noteStart }}.<br>
                        Maksimal {{ $maxMonths }} bulan (perkiraan sampai {{ $endLabel }}).
                    </p>

                    <div class="mb-3">
                        <label class="form-label">Jumlah bulan yang ingin dibayar</label>
                        <input type="number"
                               name="jumlah_bulan"
                               class="form-control input-jumlah-bulan"
                               min="1"
                               max="{{ $maxMonths }}"
                               value="1">
<small class="text-muted">
    Contoh: isi 12 → sistem akan membayar 12 bulan ke depan
    mulai {{ $startLabel }}. Bulan yang belum punya tagihan akan
    diberi keterangan "tidak ada tagihan".
</small>

                    </div>

                    <div class="preview-bayar-box py-2 px-3 mb-0 text-preview-bayar" style="font-size: 13px;">
                        {{-- akan diisi / dioverride oleh JS --}}
                        @if($hargaPerBulan > 0)
                            Perkiraan total: <strong>Rp {{ number_format($hargaPerBulan, 0, ',', '.') }}</strong>
                            (Rp {{ number_format($hargaPerBulan, 0, ',', '.') }} x 1 bulan).
                        @else
                            Perkiraan total akan dihitung setelah dikirim (harga paket tidak terbaca).
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        Lanjut Bayar
                    </button>
                </div>

            </form>
        </div>
    </div>
@endforeach
