@php
    // helper: map status_approve => tampil
    function statusLabel($s){
        if($s === 'approved') return '<span class="status approved">Setuju</span>';
        if($s === 'rejected') return '<span class="status rejected">Tolak</span>';
        return '<span class="status pending">Menunggu</span>';
    }
@endphp

@if($pengajuan->count() == 0)
    <tr>
        <td colspan="8" class="small-muted text-center">Tidak ada data pengajuan.</td>
    </tr>
@else
    @foreach($pengajuan as $idx => $p)
    <tr>
        <td>{{ ($pengajuan->currentPage()-1) * $pengajuan->perPage() + $idx + 1 }}</td>

        {{-- Nama sales: melalui relation sales -> user --}}
        <td>
            @if($p->sales && $p->sales->user)
                {{ $p->sales->user->name }} <br>
                <small class="small-muted">{{ $p->sales->user->email }}</small>
            @else
                -
            @endif
        </td>

        <td>
            {{ \Carbon\Carbon::parse($p->tanggal_pengajuan)->format('d M Y') }} <br>
            <small>{{ \Carbon\Carbon::parse($p->tanggal_pengajuan)->format('H:i') }} WIB</small>
        </td>

        <td>{{ $p->nama_pengeluaran }}</td>

        <td>Rp. {{ number_format($p->nominal, 0, ',', '.') }}</td>

        <td>
            @if($p->bukti_file)
                <a href="{{ asset('storage/bukti/' . $p->bukti_file) }}" target="_blank" class="file-link">File</a>
            @else
                -
            @endif
        </td>

        <td>
            @if($p->tanggal_approve && $p->status_approve != 'pending' && $p->approvedBy)
                {{ $p->approvedBy->name ?? '-' }} <br>
                <small class="small-muted">{{ \Carbon\Carbon::parse($p->tanggal_approve)->format('d M Y | H:i') }}</small>
            @else
                -
            @endif
        </td>

        <td>{!! statusLabel($p->status_approve) !!}</td>
    </tr>
    @endforeach
@endif
