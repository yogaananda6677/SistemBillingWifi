<table class="table table-striped">
    <thead>
        <tr>
            <th>Nama Pengeluaran</th>
            <th>Nominal</th>
            <th>Tanggal Approve</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $d)
        <tr>
            <td>{{ $d->nama_pengeluaran }}</td>
            <td>Rp {{ number_format($d->nominal,0,',','.') }}</td>
            <td>{{ $d->tanggal_approve }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
