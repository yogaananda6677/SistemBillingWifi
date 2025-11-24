@foreach($data as $sales)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $sales->user->name }}</td>
        <td>{{ $sales->user->no_hp }}</td>
        <td>{{ $sales->user->email }}</td>
        <td>••••••••</td>
        <td>{{ $sales->area->nama_area ?? '-' }}</td>
        <td>{{ $sales->pelanggan->count() }} Pelanggan</td>

        <td>
            <a href="{{ route('data-sales.edit', $sales->id_sales) }}" class="btn-action-icon btn-edit">
                <i class="fas fa-pencil-alt"></i>
            </a>
<button type="button"
        class="btn-action-icon btn-delete"
        data-url="{{ route('data-sales.destroy', $sales->id_sales) }}">
    <i class="fas fa-trash-alt"></i>
</button>


        </td>
    </tr>
@endforeach
