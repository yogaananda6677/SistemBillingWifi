<table class="table table-striped">
    <thead>
        <tr>
            <th>Nominal Komisi</th>
            <th>Jumlah</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $d)
        <tr>
            <td>Rp {{ number_format($d->nominal_komisi,0,',','.') }}</td>
            <td>{{ $d->jumlah_komisi }}</td>
            <td>{{ $d->created_at }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
