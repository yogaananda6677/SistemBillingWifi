@extends('seles2.layout.master')

@section('content')
    <div class="pelanggan-page">

        {{-- 1. HEADER (Gradient Amber) --}}
        <div class="pelanggan-header d-flex align-items-center justify-content-between px-3 pt-3 pb-5">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('sales.pengajuan.index') }}" class="back-btn">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h5 class="mb-0 fw-bold text-white">Edit Pengajuan</h5>
            </div>
        </div>

        {{-- 2. FORM CARD (Overlay) --}}
        <div class="px-3" style="margin-top: -35px; position: relative; z-index: 20;">
            <div class="bg-white rounded-4 shadow-sm p-4 border border-light">

                {{-- Flash Messages --}}
                @if (session('success'))
                    <div
                        class="alert alert-success py-2 mb-3 small rounded-3 border-success border-opacity-25 bg-success bg-opacity-10 text-success fw-bold">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div
                        class="alert alert-danger py-2 mb-3 small rounded-3 border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger fw-bold">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Form Edit --}}
                <form action="{{ route('sales.pengajuan.update', $pengajuan->id_pengeluaran) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Error Alert --}}
                    @if ($errors->any())
                        <div
                            class="alert alert-danger py-2 small rounded-3 mb-3 border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- INFO STATIC --}}
                    <div class="d-flex gap-2 mb-3">
                        <div class="flex-grow-1">
                            <label class="form-label-sm text-muted">Sales</label>
                            <input type="text" class="form-control form-control-sm bg-light"
                                value="{{ auth()->user()->name }}" disabled>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-label-sm text-muted">Tgl Pengajuan</label>
                            <input type="text" class="form-control form-control-sm bg-light"
                                value="{{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->translatedFormat('d M Y, H:i') }}"
                                disabled>
                        </div>
                    </div>

                    {{-- NAMA PENGELUARAN --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Nama Pengeluaran <span
                                class="text-danger">*</span></label>
                        <input type="text" name="nama_pengeluaran"
                            class="form-control form-control-custom @error('nama_pengeluaran') is-invalid @enderror"
                            value="{{ old('nama_pengeluaran', $pengajuan->nama_pengeluaran) }}" required>
                    </div>

                    {{-- NOMINAL --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Nominal (Rp) <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted fw-bold">Rp</span>
                            <input type="number" name="nominal"
                                class="form-control form-control-custom border-start-0 ps-1 fw-bold text-dark @error('nominal') is-invalid @enderror"
                                value="{{ old('nominal', $pengajuan->nominal) }}" min="1" style="font-size: 1.1rem;"
                                required>
                        </div>
                    </div>
                    {{-- WILAYAH --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Wilayah <span class="text-danger">*</span></label>
                        <select name="id_area"
                                class="form-select form-select-sm form-control-custom @error('id_area') is-invalid @enderror">
                            <option value="">-- Pilih Wilayah --</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id_area }}"
                                    {{ old('id_area', $pengajuan->id_area) == $area->id_area ? 'selected' : '' }}>
                                    {{ $area->nama_area }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_area')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>


                    {{-- CATATAN --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Catatan <span
                                class="text-muted fw-normal">(Opsional)</span></label>
                        <textarea name="catatan" class="form-control form-control-custom @error('catatan') is-invalid @enderror" rows="3"
                            placeholder="Tambahkan keterangan...">{{ old('catatan', $pengajuan->catatan) }}</textarea>
                    </div>

                    {{-- BUKTI FILE (Preview Existing) --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small">Bukti Lampiran</label>

                        {{-- Jika ada file sebelumnya --}}
                        @if ($pengajuan->bukti_file)
                            <div
                                class="mb-2 p-2 bg-light border rounded-3 d-flex align-items-center justify-content-between">
                                <span class="small text-muted text-truncate me-2">
                                    <i class="bi bi-paperclip me-1"></i> File saat ini tersimpan
                                </span>
                                <a href="{{ route('sales.pengajuan.bukti', $pengajuan->id_pengeluaran) }}" target="_blank"
                                    class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" style="font-size: 0.75rem;">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                            </div>
                        @endif

                        <input type="file" name="bukti_file"
                            class="form-control form-control-custom form-control-file @error('bukti_file') is-invalid @enderror"
                            accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text small text-muted">
                            <i class="bi bi-info-circle me-1"></i> Kosongkan jika tidak ingin mengubah bukti.
                        </div>
                    </div>

                    {{-- BUTTONS --}}
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-amber rounded-pill py-2 fw-bold shadow-sm text-white">
                            <i class="bi bi-check-circle-fill me-2"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('sales.pengajuan.index') }}"
                            class="btn btn-light rounded-pill py-2 fw-bold text-secondary">
                            Batal
                        </a>
                    </div>

                </form>
            </div>
        </div>

        {{-- Footer spacer --}}
        <div style="height: 100px;"></div>
    </div>
@endsection

@push('styles')
    <style>
        /* Global Page Style */
        .pelanggan-page {
            background: #f9fafb;
            min-height: 100vh;
        }

        /* 1. HEADER (Gradient Amber) */
        .pelanggan-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.25);
            margin: -16px -16px 0 -16px;
        }

        .back-btn {
            color: white;
            text-decoration: none;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            justify-content: center;
            transition: 0.2s;
        }

        .back-btn:active {
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0.9);
        }

        /* 2. FORM STYLING */
        .form-label-sm {
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 2px;
            display: block;
        }

        .form-control-custom {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .form-control-custom:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
            background-color: #fff;
        }

        /* Input Group styling */
        .input-group-text {
            border: 1px solid #e5e7eb;
            border-right: none;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .input-group .form-control-custom {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        /* File Input Styling */
        .form-control-file {
            padding: 8px;
            background-color: #f9fafb;
        }

        /* 3. BUTTONS */
        .btn-amber {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            transition: all 0.2s;
        }

        .btn-amber:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
            color: white;
        }

        .btn-amber:active {
            transform: scale(0.98);
        }
    </style>
@endpush
