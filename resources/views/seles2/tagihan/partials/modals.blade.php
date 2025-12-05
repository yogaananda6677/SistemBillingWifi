@foreach ($pelanggan as $p)
    @php
        // === LOGIKA PHP ASLI (TIDAK DIUBAH) ===
        $langganan = $p->langganan->sortByDesc('tanggal_mulai')->first();
        $tagihanList = $langganan?->tagihan ?? collect();

        if (!$langganan) {
            continue;
        }

        $tagihanBelumLunas = $tagihanList
            ->where('status_tagihan', 'belum lunas')
            ->sortBy(fn($t) => $t->tahun * 100 + $t->bulan);

        $lastPaid = $tagihanList
            ->where('status_tagihan', 'lunas')
            ->sortBy(fn($t) => $t->tahun * 100 + $t->bulan)
            ->last();

        if ($tagihanBelumLunas->isNotEmpty()) {
            $firstUnpaid = $tagihanBelumLunas->first();
            $startDate = Carbon::create($firstUnpaid->tahun, $firstUnpaid->bulan, 1);
            $noteStart = 'Mulai dari tagihan yang belum lunas paling awal';
        } elseif ($lastPaid) {
            $startDate = Carbon::create($lastPaid->tahun, $lastPaid->bulan, 1)->addMonth();
            $noteStart = 'Mulai setelah bulan terakhir yang sudah lunas';
        } else {
            $startDate = now()->startOfMonth();
            $noteStart = 'Belum ada tagihan, mulai dari bulan ini';
        }

        $startYm = $startDate->format('Y-m');
        $startLabel = $startDate->translatedFormat('F Y');

        $maxMonths = 60;
        $endPreview = $startDate->copy()->addMonths($maxMonths - 1);
        $endLabel = $endPreview->translatedFormat('F Y');

        $hargaPerBulan = optional($langganan->paket)->harga_total ?? 0;

        $modalId = 'modal-bayar-sales-' . $p->id_pelanggan;

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

    {{-- MODAL MODERN TEMA AMBER --}}
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content form-bayar-periode-sales border-0 shadow-lg rounded-4"
                action="{{ route('seles2.tagihan.bayar-banyak') }}" method="POST" data-start-ym="{{ $startYm }}"
                data-start-label="{{ $startLabel }}" data-max-bulan="{{ $maxMonths }}"
                data-harga-per-bulan="{{ $hargaPerBulan }}" data-nama-pelanggan="{{ $p->nama }}"
                data-bulan-tagihan='@json($bulanTagihan)'>

                @csrf

                <input type="hidden" name="id_langganan" value="{{ $langganan->id_langganan }}">
                <input type="hidden" name="start_ym" value="{{ $startYm }}">

                {{-- Header Bersih --}}
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Bayar Periode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body pt-2">
                    {{-- Nama Pelanggan --}}
                    <h6 class="text-primary fw-bold mb-3">{{ $p->nama }}</h6>

                    {{-- Info Alert --}}
                    <div
                        class="alert alert-light border border-warning border-opacity-25 d-flex gap-2 align-items-start mb-3">
                        <i class="bi bi-info-circle-fill text-warning mt-1"></i>
                        <div>
                            <div class="small text-muted mb-1">Mulai dibayar dari:</div>
                            <div class="fw-bold text-dark">{{ $startLabel }}</div>
                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                {{ $noteStart }}.<br>
                                Maksimal {{ $maxMonths }} bulan (s.d {{ $endLabel }}).
                            </div>
                        </div>
                    </div>

                    {{-- Input Jumlah Bulan --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Jumlah bulan yang ingin dibayar</label>
                        <div class="input-group">
                            <input type="number" name="jumlah_bulan"
                                class="form-control input-jumlah-bulan text-center fw-bold" min="1"
                                max="{{ $maxMonths }}" value="1" style="font-size: 1.2rem; color: #d97706;">
                            <span class="input-group-text bg-light">Bulan</span>
                        </div>
                        <small class="text-danger warning-jumlah-bulan d-none mt-1 d-block">
                            Jumlah bulan tidak boleh kosong atau 0.
                        </small>
                    </div>

                    {{-- Preview Box --}}
                    <div class="preview-bayar-box p-3 mb-0 text-preview-bayar rounded-3 bg-light border">
                        @if ($hargaPerBulan > 0)
                            {{-- Placeholder sebelum JS jalan --}}
                            Perkiraan total: <strong>Rp {{ number_format($hargaPerBulan, 0, ',', '.') }}</strong>
                        @else
                            Perkiraan total akan dihitung setelah dikirim.
                        @endif
                    </div>
                </div>

                {{-- Footer Tombol --}}
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-cash-coin me-1"></i> Lanjut Bayar
                    </button>
                </div>

            </form>
        </div>
    </div>
@endforeach
