@extends('seles2.layout.master')

@section('title', 'Pembukuan Sales Bulanan')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">

    {{-- FILTER BULAN & TAHUN --}}
    <div class="mb-6">
        <form method="GET" action="{{ route('seles2.pembukuan.index') }}"
            class="bg-white p-4 rounded-lg shadow-sm border flex flex-wrap gap-4 items-end">


            <div>
                <label class="text-xs text-gray-600">Bulan</label>
                <select name="bulan" class="border rounded-md px-2 py-1 text-sm">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}"
                            {{ (int)($selectedMonth ?? now()->month) === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs text-gray-600">Tahun</label>
                    <select name="tahun" class="border rounded-md px-2 py-1 text-sm">
                        @foreach(range(now()->year - 3, now()->year + 1) as $y)
                            <option value="{{ $y }}"
                                {{ (int)($selectedYear ?? now()->year) === $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
            </div>

            <button class="bg-indigo-600 text-white px-3 py-2 rounded-md text-sm">
                Filter
            </button>
        </form>
    </div>

    @if($rekap->isEmpty())
        <div class="p-3 bg-yellow-100 text-yellow-700 text-sm rounded">
            Data tidak ditemukan untuk bulan & tahun ini.
        </div>
    @endif

    {{-- MOBILE VIEW (CARD) --}}
    <div class="md:hidden space-y-3">
        @foreach($rekap as $row)
        <div class="bg-white p-4 rounded-lg shadow border">
            <div class="flex justify-between mb-2">
                <div>
                    <div class="text-xs text-gray-500">Sales</div>
                    <div class="font-semibold">{{ $row->nama_sales }}</div>
                </div>

                <span class="text-xs px-2 py-1 rounded
                    {{ $row->selisih_setoran < 0 ? 'bg-red-100 text-red-600' :
                       ($row->selisih_setoran > 0 ? 'bg-green-100 text-green-600' : 'bg-gray-200 text-gray-700') }}">
                    Rp {{ number_format($row->selisih_setoran,0,',','.') }}
                </span>
            </div>

            <div class="text-sm grid grid-cols-2 gap-y-1">
                <span>Pendapatan</span>
                <span class="text-right font-medium">Rp {{ number_format($row->total_pendapatan,0,',','.') }}</span>

                <span>Komisi</span>
                <span class="text-right font-medium">Rp {{ number_format($row->total_komisi,0,',','.') }}</span>

                <span>Pengeluaran</span>
                <span class="text-right font-medium">Rp {{ number_format($row->total_pengeluaran,0,',','.') }}</span>

                <span>Harus Disetor</span>
                <span class="text-right font-semibold">Rp {{ number_format($row->harus_disetorkan,0,',','.') }}</span>

                <span>Setoran</span>
                <span class="text-right font-semibold">Rp {{ number_format($row->total_setoran,0,',','.') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- DESKTOP VIEW --}}
    <div class="hidden md:block mt-4">
        <div class="bg-white rounded-lg shadow border overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Sales</th>
                        <th class="px-4 py-2 text-right">Pendapatan</th>
                        <th class="px-4 py-2 text-right">Komisi</th>
                        <th class="px-4 py-2 text-right">Pengeluaran</th>
                        <th class="px-4 py-2 text-right">Harus Disetor</th>
                        <th class="px-4 py-2 text-right">Setoran</th>
                        <th class="px-4 py-2 text-right">Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekap as $row)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $row->nama_sales }}</td>

                        <td class="px-4 py-2 text-right">
                            Rp {{ number_format($row->total_pendapatan,0,',','.') }}
                        </td>

                        <td class="px-4 py-2 text-right">
                            Rp {{ number_format($row->total_komisi,0,',','.') }}
                        </td>

                        <td class="px-4 py-2 text-right">
                            Rp {{ number_format($row->total_pengeluaran,0,',','.') }}
                        </td>

                        <td class="px-4 py-2 text-right font-semibold">
                            Rp {{ number_format($row->harus_disetorkan,0,',','.') }}
                        </td>

                        <td class="px-4 py-2 text-right font-semibold">
                            Rp {{ number_format($row->total_setoran,0,',','.') }}
                        </td>

                        <td class="px-4 py-2 text-right">
                            <span class="px-2 py-1 rounded text-xs
                                {{ $row->selisih_setoran < 0 ? 'bg-red-100 text-red-600' :
                                   ($row->selisih_setoran > 0 ? 'bg-green-100 text-green-600' : 'bg-gray-200 text-gray-700') }}">
                                Rp {{ number_format($row->selisih_setoran,0,',','.') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
