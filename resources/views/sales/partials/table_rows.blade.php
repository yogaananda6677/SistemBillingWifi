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

        $pelangganCount = $sales->pelanggan->count();

        // Format komisi (boleh disesuaikan)
        $komisiText = $sales->komisi !== null
            ? 'Rp ' . number_format($sales->komisi, 0, ',', '.')
            : '-';
    @endphp

    <tr>
        <td>{{ $nomor++ }}</td>
        <td>{{ $sales->user->name ?? '-' }}</td>
        <td>{{ $sales->user->no_hp ?? '-' }}</td>
        <td>{{ $sales->user->email ?? '-' }}</td>

        {{-- AREA as BADGE --}}
        <td>
            @if(count($areaNames) == 0)
                <span class="text-muted">-</span>
            @else
                @foreach($areaNames as $area)
                    <span class="badge bg-warning text-dark me-1">
                        {{ $area }}
                    </span>
                @endforeach
            @endif
        </td>

        {{-- KOMISI --}}
        <td>{{ $komisiText }}</td>

        {{-- TOTAL PELANGGAN --}}
        <td>{{ $pelangganCount }}</td>

        {{-- AKSI --}}
        <td>
            <a href="{{ route('data-sales.edit', $sales->id_sales) }}"
               class="btn btn-sm btn-warning text-dark me-1" title="Edit">
                <i class="fas fa-edit me-1"></i> Edit
            </a>

            <button type="button"
                    class="btn btn-sm btn-danger btn-delete"
                    data-url="{{ route('data-sales.destroy', $sales->id_sales) }}"
                    title="Hapus">
                <i class="fas fa-trash-alt me-1"></i> Hapus
            </button>
        </td>
    </tr>
@empty
    <tr>
        {{-- sekarang kolom: No, Nama, No HP, Email, Area, Komisi, Pelanggan, Aksi = 8 --}}
        <td colspan="8" class="text-center text-muted">
            Tidak ada data sales.
        </td>
    </tr>
@endforelse
