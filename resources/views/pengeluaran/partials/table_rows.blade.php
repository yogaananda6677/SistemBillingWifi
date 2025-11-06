@php
    // helper: map status_approve => label teks
    function statusLabel($s){
        if ($s === 'approved') return 'Setuju';
        if ($s === 'rejected') return 'Tolak';
        return 'Menunggu';
    }
@endphp

@if($pengajuan->count() == 0)
    <tr>
        <td colspan="9" class="small-muted text-center">Tidak ada data pengajuan.</td>
    </tr>
@else
    @foreach($pengajuan as $idx => $p)
        <tr>
            {{-- No --}}
            <td>{{ ($pengajuan->currentPage()-1) * $pengajuan->perPage() + $idx + 1 }}</td>

            {{-- Nama sales --}}
            <td>
                @if($p->sales && $p->sales->user)
                    {{ $p->sales->user->name }} <br>
                    <small class="small-muted">{{ $p->sales->user->email }}</small>
                @else
                    -
                @endif
            </td>
            <td>
    {{ optional($p->area)->nama_area ?? '-' }}
</td>


            {{-- Tanggal pengajuan --}}
            <td>
                {{ \Carbon\Carbon::parse($p->tanggal_pengajuan)->format('d M Y') }} <br>
                <small>{{ \Carbon\Carbon::parse($p->tanggal_pengajuan)->format('H:i') }} WIB</small>
            </td>

            {{-- Nama pengeluaran --}}
            <td>{{ $p->nama_pengeluaran }}</td>

            {{-- Nominal --}}
            <td>Rp. {{ number_format($p->nominal, 0, ',', '.') }}</td>

            {{-- Bukti --}}
            {{-- Wilayah --}}

            <td>
@if($p->bukti_file)
    <a href="{{ route('admin.pengajuan.bukti', $p->id_pengeluaran) }}"
       target="_blank"
       class="file-link">
        File
    </a>
@else
    -
@endif


            </td>

            {{-- Admin yang approve/reject --}}
            <td>
                @if($p->id_admin && $p->adminUser)
                    {{ $p->adminUser->name }} <br>
                    <small class="small-muted">
                        {{ \Carbon\Carbon::parse($p->tanggal_approve)->format('d M Y | H:i') }}
                    </small>
                @else
                    -
                @endif
            </td>

            {{-- Status (klik untuk ubah) --}}
            <td>
                <span
                    class="status-badge status {{ $p->status_approve }}"
                    data-id="{{ $p->id_pengeluaran }}"
                    data-current="{{ $p->status_approve }}"
                    style="cursor:pointer;"
                >
                    {{ statusLabel($p->status_approve) }}
                </span>
            </td>
        </tr>
    @endforeach
@endif
