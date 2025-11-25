@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">

    <div class="card shadow-sm p-4">
        <h4 class="mb-4 fw-bold">Edit Sales</h4>

        {{-- FORM --}}
        <form id="formSales" action="{{ route('data-sales.update', $sales->id_sales) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">

                {{-- Nama --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Sales</label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $sales->user->name ?? '') }}" required>
                    <div class="invalid-feedback"></div>
                    @error('name')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- No HP --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" class="form-control"
                           value="{{ old('no_hp', $sales->user->no_hp ?? '') }}" required>
                    <div class="invalid-feedback"></div>
                    @error('no_hp')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Username/Email --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username (email)</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email', $sales->user->email ?? '') }}" required>
                    <div class="invalid-feedback"></div>
                    @error('email')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Password (opsional saat edit) --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Password
                        <small class="text-muted">(kosongkan jika tidak diganti)</small>
                    </label>
                    <input type="password" name="password" class="form-control">
                    <div class="invalid-feedback"></div>
                    @error('password')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Area (multi, tambah/hapus baris) --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Area</label>

                    @php
                        // ambil area yang sudah di-assign via pivot
                        $pivotAreaIds = $sales->areas->pluck('id_area')->toArray();

                        // kalau pivot kosong, pakai id_area legacy
                        if (empty($pivotAreaIds) && $sales->id_area) {
                            $pivotAreaIds = [$sales->id_area];
                        }

                        // pakai old() kalau form sebelumnya error
                        $selectedAreas = old('id_area', $pivotAreaIds ?: [null]);
                        if (!is_array($selectedAreas)) {
                            $selectedAreas = [$selectedAreas];
                        }
                    @endphp

                    <div id="area-container">
                        @foreach($selectedAreas as $index => $selectedArea)
                            <div class="d-flex mb-2 align-items-center area-row">
                                <select name="id_area[]" class="form-control me-2" required>
                                    <option value="">-- Pilih Area --</option>
                                    @foreach($area as $a)
                                        <option value="{{ $a->id_area }}"
                                            {{ (string)$selectedArea === (string)$a->id_area ? 'selected' : '' }}>
                                            {{ $a->nama_area }}
                                        </option>
                                    @endforeach
                                </select>

                                @if($index > 0)
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-area">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <button type="button" id="add-area" class="btn btn-outline-primary btn-sm mt-1">
                        + Tambah Area
                    </button>

                    <small class="text-muted d-block mt-1">
                        Tambahkan area lain bila sales pegang beberapa wilayah.
                    </small>

                    <div id="area-error" class="text-danger mt-1" style="font-size: .875rem;"></div>

                    @error('id_area')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                    @error('id_area.*')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Komisi --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Komisi (Nominal)</label>
                    <input type="number" name="komisi" class="form-control"
                           placeholder="Contoh: 5000"
                           value="{{ old('komisi', $sales->komisi) }}">
                    <div class="invalid-feedback"></div>
                    @error('komisi')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- BUTTON --}}
            <div class="mt-4 d-flex justify-content-end">
                <a href="{{ route('data-sales.index') }}" class="btn btn-secondary me-2">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>

        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('area-container');
    const addBtn    = document.getElementById('add-area');
    const areaError = document.getElementById('area-error');

    if (!container || !addBtn) return;

    // Tambah baris area
    addBtn.addEventListener('click', function () {
        const firstRow = container.querySelector('.area-row');
        if (!firstRow) return;

        const newRow = firstRow.cloneNode(true);

        const select = newRow.querySelector('select');
        if (select) {
            select.value = '';
            select.classList.remove('is-invalid');
        }

        let removeBtn = newRow.querySelector('.remove-area');
        if (!removeBtn) {
            removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-outline-danger btn-sm remove-area';
            removeBtn.textContent = 'Hapus';
            newRow.appendChild(removeBtn);
        }

        container.appendChild(newRow);
        attachAreaChangeListener(select);
        refreshAreaUniqueness();
    });

    // Hapus baris area (delegasi)
    container.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-area')) {
            const rows = container.querySelectorAll('.area-row');
            if (rows.length <= 1) {
                return; // minimal 1 baris
            }
            e.target.closest('.area-row').remove();
            refreshAreaUniqueness();
        }
    });

    function getAreaSelects() {
        return Array.from(container.querySelectorAll('select[name="id_area[]"]'));
    }

    // VALIDASI area unik
    function refreshAreaUniqueness() {
        const selects = getAreaSelects();
        const values = selects
            .map(s => s.value)
            .filter(v => v !== '');

        selects.forEach(s => s.classList.remove('is-invalid'));
        areaError.textContent = '';

        if (values.length === 0) {
            areaError.textContent = 'Minimal pilih satu area.';
            if (selects[0]) selects[0].classList.add('is-invalid');
            return false;
        }

        const seen = {};
        let hasDuplicate = false;
        selects.forEach(select => {
            const val = select.value;
            if (!val) return;
            if (seen[val]) {
                hasDuplicate = true;
                select.classList.add('is-invalid');
                seen[val].classList.add('is-invalid');
            } else {
                seen[val] = select;
            }
        });

        if (hasDuplicate) {
            areaError.textContent = 'Area tidak boleh dipilih lebih dari satu kali.';
            return false;
        }

        return true;
    }

    function attachAreaChangeListener(select) {
        if (!select) return;
        select.addEventListener('change', function () {
            refreshAreaUniqueness();
        });
    }

    // pasang listener awal
    getAreaSelects().forEach(select => attachAreaChangeListener(select));

    // ===== VALIDASI BASIC FIELD LAIN (client-side) =====
    const form = document.getElementById('formSales');

    const validators = {
        name(value) {
            if (!value.trim()) return 'Nama wajib diisi.';
            if (value.trim().length < 3) return 'Nama minimal 3 karakter.';
            return '';
        },
        no_hp(value) {
            if (!value.trim()) return 'No. HP wajib diisi.';
            if (!/^[0-9]+$/.test(value)) return 'No. HP hanya boleh berisi angka.';
            if (value.length < 10 || value.length > 15) return 'No. HP harus 10–15 digit.';
            return '';
        },
        email(value) {
            if (!value.trim()) return 'Email wajib diisi.';
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) return 'Format email tidak valid.';
            return '';
        },
        password(value) {
            if (!value) return ''; // opsional saat edit
            if (value.length < 6) return 'Password minimal 6 karakter.';
            return '';
        },
        komisi(value) {
            if (!value) return '';
            const num = Number(value);
            if (Number.isNaN(num)) return 'Komisi harus berupa angka.';
            if (num < 0) return 'Komisi tidak boleh negatif.';
            return '';
        }
    };

    function getErrorElement(field) {
        const group = field.closest('.mb-3');
        if (!group) return null;
        return group.querySelector('.invalid-feedback');
    }

    function validateField(field) {
        const name = field.name;
        const validator = validators[name];
        if (!validator) return true;

        const errorMessage = validator(field.value);
        const errorEl = getErrorElement(field);

        if (errorMessage) {
            field.classList.add('is-invalid');
            if (errorEl) errorEl.textContent = errorMessage;
            return false;
        } else {
            field.classList.remove('is-invalid');
            if (errorEl) errorEl.textContent = '';
            return true;
        }
    }

    Object.keys(validators).forEach(function (name) {
        const field = form.elements[name];
        if (!field) return;

        field.addEventListener('input', function () {
            validateField(field);
        });

        field.addEventListener('blur', function () {
            validateField(field);
        });
    });

    form.addEventListener('submit', function (e) {
        let firstInvalid = null;

        Object.keys(validators).forEach(function (name) {
            const field = form.elements[name];
            if (!field) return;
            const valid = validateField(field);
            if (!valid && !firstInvalid) {
                firstInvalid = field;
            }
        });

        const areaOk = refreshAreaUniqueness();
        if (!areaOk && !firstInvalid) {
            const selects = getAreaSelects();
            firstInvalid = selects[0] || null;
        }

        if (firstInvalid) {
            e.preventDefault();
            firstInvalid.focus();
        }
    });
});
</script>
@endpush
