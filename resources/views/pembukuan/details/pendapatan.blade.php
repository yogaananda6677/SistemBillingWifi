<table class="table table-striped">
    <thead>
        <tr>
            <th>Pelanggan</th>
            <th>Nominal</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $d)
        <tr>
            <td>{{ $d->nama }}</td>
            <td>Rp {{ number_format($d->nominal,0,',','.') }}</td>
            <td>{{ $d->tanggal_bayar }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
