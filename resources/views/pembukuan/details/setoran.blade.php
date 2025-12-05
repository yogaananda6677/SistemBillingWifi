<table class="table table-striped">
    <thead>
        <tr>
            <th>Nominal</th>
            <th>Tanggal</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $d)
        <tr>
            <td>Rp {{ number_format($d->nominal,0,',','.') }}</td>
            <td>{{ $d->tanggal_setoran }}</td>
            <td>{{ $d->catatan }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
