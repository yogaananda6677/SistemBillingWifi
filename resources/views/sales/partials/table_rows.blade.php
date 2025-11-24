@foreach($data as $sales)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $sales->user->name }}</td>
        <td>{{ $sales->user->no_hp }}</td>
        <td>{{ $sales->user->email }}</td>
        <td>••••••••</td>
        <td>{{ $sales->area->nama_area ?? '-' }}</td>
        <td>{{ $sales->pelanggan->count() }} Pelanggan</td>

<td class="d-flex gap-1">

    {{-- Tombol EDIT --}}
    <a href="{{ route('data-sales.edit', $sales->id_sales) }}" 
       class="btn btn-warning btn-sm d-flex align-items-center text-dark fw-bold">
        <i class="fas fa-pencil-alt me-1"></i> Edit
    </a>

    {{-- Tombol HAPUS --}}
    @if($sales->pelanggan->count() == 0)
        <button type="button" 
                class="btn btn-danger btn-sm d-flex align-items-center btn-delete"
                data-url="{{ route('data-sales.destroy', $sales->id_sales) }}">
            <i class="fas fa-trash me-1"></i> Hapus
        </button>
    @else
        <button type="button" 
                class="btn btn-secondary btn-sm d-flex align-items-center"
                disabled
                title="Sales ini masih memiliki pelanggan dan tidak dapat dihapus">
            <i class="fas fa-lock me-1"></i> Tidak Bisa Hapus
        </button>
    @endif

</td>

    </tr>
@endforeach
