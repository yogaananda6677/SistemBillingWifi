@php
    $nomor = ($data->currentPage() - 1) * $data->perPage() + 1;
@endphp

@forelse ($data as $sales)
    @php
        // Ambil nama area dari relasi many-to-many
        $areaNames = $sales->areas->pluck('nama_area')->toArray();

        // Kalau pivot kosong, fallback ke relasi lama (kolom id_area)
        if (empty($areaNames) && $sales->area) {
            $areaNames = [$sales->area->nama_area];
        }

        $areaText = $areaNames ? implode(', ', $areaNames) : '-';
        $pelangganCount = $sales->pelanggan->count();
    @endphp

    <tr>
        <td>{{ $nomor++ }}</td>
        <td>{{ $sales->user->name ?? '-' }}</td>
        <td>{{ $sales->user->no_hp ?? '-' }}</td>
        <td>{{ $sales->user->email ?? '-' }}</td>
        <td>{{ $areaText }}</td>
        <td>{{ $pelangganCount }}</td>
        <td>
            <a href="{{ route('data-sales.edit', $sales->id_sales) }}"
               class="btn-action-icon btn-edit" title="Edit">
                <i class="fas fa-edit"></i>
            </a>

            <button type="button"
                    class="btn-action-icon btn-delete"
                    data-url="{{ route('data-sales.destroy', $sales->id_sales) }}"
                    title="Hapus">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center text-muted">
            Tidak ada data sales.
        </td>
    </tr>
@endforelse
