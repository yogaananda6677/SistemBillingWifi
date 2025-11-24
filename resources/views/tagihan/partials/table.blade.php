@foreach($dataTagihan as $i => $t)
<tr>
    <td>{{ $i + $dataTagihan->firstItem() }}</td>
    <td>{{ $t->langganan?->pelanggan?->nama ?? '-' }}</td>
    <td>{{ $t->langganan?->paket?->nama_paket ?? '-' }}</td>
    <td>Rp {{ number_format($t->harga_dasar,0,',','.') }}</td>
    <td>Rp {{ number_format($t->ppn_nominal,0,',','.') }}</td>
    <td>Rp {{ number_format($t->total_tagihan,0,',','.') }}</td>
    <td>{{ \Carbon\Carbon::parse($t->jatuh_tempo)->format('d-m-Y') }}</td>
    <td>
        @if($t->status_tagihan == 'lunas')
            <span class="badge bg-success">Lunas</span>
        @else
            <span class="badge bg-warning text-dark">Belum Lunas</span>
        @endif
    </td>
</tr>
@endforeach
@if($dataTagihan->isEmpty())
<tr>
    <td colspan="8" class="text-center text-muted">Belum ada tagihan</td>
</tr>
@endif
