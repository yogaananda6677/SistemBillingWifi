@extends('layouts.master')

@section('content')

<div class="container-fluid p-4">

    <h5 class="fw-bold mb-3 text-secondary">
        Tambah Pelanggan
    </h5>

    <div class="card shadow-sm border-0 p-4" style="border-radius: 14px;">
        <form id="formPelanggan" action="{{ route('pelanggan.store') }}" method="POST">
            @csrf

            <!-- NAMA -->
            <div class="mb-3">
                <label class="fw-semibold">Nama</label>
                <input type="text" id="nama" name="nama" class="form-control border-warning"
                       placeholder="Nama Pelanggan" required>
                <div class="invalid-feedback"></div>
                @error('nama')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>

            <!-- TELEPON -->
            <div class="mb-3">
                <label class="fw-semibold">No. Telepon</label>
                <input type="text" id="nomor_hp" name="nomor_hp" class="form-control border-warning"
                       placeholder="123456789012" required>
                <div class="invalid-feedback"></div>
                @error('nomor_hp')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>

            <!-- NIK -->
            <div class="mb-3">
                <label class="fw-semibold">NIK</label>
                <input type="text" id="nik" name="nik" class="form-control border-warning"
                       placeholder="1234567890123456" required>
                <div class="invalid-feedback"></div>
                @error('nik')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>

            <!-- ALAMAT -->
            <div class="mb-3">
                <label class="fw-semibold">Alamat</label>
                <input type="text" id="alamat" name="alamat" class="form-control border-warning"
                       placeholder="Alamat Pelanggan" required>
                <div class="invalid-feedback"></div>
                @error('alamat')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>

            <!-- IP ADDRESS -->
            <div class="mb-3">
                <label class="fw-semibold">IP Address</label>
                <input type="text" id="ip_address" name="ip_address" class="form-control border-warning"
                       placeholder="192.168.192.100" required>
                <div class="invalid-feedback"></div>
                @error('ip_address')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>

            <!-- TANGGAL REGISTRASI -->
            <div class="md-6 mb-3">
                <label class="form-label fw-semibold">Tanggal Registrasi</label>
                <input type="date" id="tanggal_registrasi" name="tanggal_registrasi"
                       class="form-control border-warning" required>
                <div class="invalid-feedback"></div>
                @error('tanggal_registrasi')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>

            {{-- STATUS PELANGGAN (diset otomatis jadi "baru", tidak ditampilkan di form) --}}
            <input type="hidden" name="status_pelanggan" value="aktif">

            <!-- PAKET LAYANAN -->
            <div class="mb-3">
                <label class="fw-semibold">Paket Layanan</label>
                <select id="id_paket" name="id_paket" class="form-select border-warning" required>
                    <option value="" disabled selected>Pilih Paket Layanan</option>
                    @foreach ($dataPaket ?? [] as $paket)
                        <option value="{{ $paket->id_paket }}">
                            {{ $paket->nama_paket }} - {{ $paket->kecepatan }} Mbps
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
                @error('id_paket')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>

            <!-- AREA -->
            <div class="mb-3">
                <label class="fw-semibold">Area</label>
<select name="id_area" id="id_area" class="form-select border-warning" required>
    <option value="" disabled selected>Pilih Area</option>
    @foreach ($dataArea ?? [] as $area)
        <option value="{{ $area->id_area }}">{{ ucwords($area->nama_area) }}</option>
    @endforeach
</select>
                <div class="invalid-feedback"></div>
                @error('id_area')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>

            <!-- SALES -->
            <div class="mb-3">
                <label class="fw-semibold">Sales</label>

<select name="id_sales" id="id_sales" class="form-select border-warning" required>
    <option value="" disabled selected>Pilih Sales</option>
</select>
                <div class="invalid-feedback"></div>
                @error('id_sales')
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>

            <!-- BUTTONS -->
            <div class="d-flex justify-content-end mt-4 gap-3">
                <a href="{{ route('pelanggan.index') }}" class="btn btn-light px-4"
                   style="border-radius: 30px; border:1px solid #ddd;">
                   Batal
                </a>

                <button type="submit" class="btn px-4"
                        style="background:#f2be00; border-radius:30px; color:white;">
                    Tambah
                </button>
            </div>

        </form>
    </div>

</div>

@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const areaSelect  = document.getElementById('id_area');
    const salesSelect = document.getElementById('id_sales');

    if (!areaSelect || !salesSelect) return;

    areaSelect.addEventListener('change', function () {
        const areaId = this.value;

        salesSelect.innerHTML = '<option value="" disabled selected>Memuat...</option>';

        fetch(`{{ url('/get-sales-by-area') }}/${areaId}`)
            .then(response => response.json())
            .then(data => {
                salesSelect.innerHTML = '<option value="" disabled selected>Pilih Sales</option>';

                if (!data || data.length === 0) {
                    const opt = document.createElement('option');
                    opt.disabled = true;
                    opt.textContent = 'Tidak ada sales di area ini';
                    salesSelect.appendChild(opt);
                    return;
                }

                data.forEach(function (sales) {
                    const option = document.createElement('option');
                    option.value = sales.id_sales;
                    option.textContent = sales.user?.name ?? 'Nama tidak tersedia';
                    salesSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error(error);
                salesSelect.innerHTML =
                    '<option value="" disabled selected>Error memuat sales</option>';
            });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formPelanggan');

    const validators = {
        nama(value) {
            if (!value.trim()) return 'Nama wajib diisi.';
            if (value.trim().length < 3) return 'Nama minimal 3 karakter.';
            return '';
        },
        nomor_hp(value) {
            if (!value.trim()) return 'No. Telepon wajib diisi.';
            if (!/^[0-9]+$/.test(value)) return 'No. Telepon hanya boleh berisi angka.';
            if (value.length < 10 || value.length > 15) return 'No. Telepon harus 10–15 digit.';
            return '';
        },
        nik(value) {
            if (!value.trim()) return 'NIK wajib diisi.';
            if (!/^[0-9]+$/.test(value)) return 'NIK hanya boleh berisi angka.';
            if (value.length !== 16) return 'NIK harus 16 digit.';
            return '';
        },
        alamat(value) {
            if (!value.trim()) return 'Alamat wajib diisi.';
            if (value.trim().length < 5) return 'Alamat terlalu pendek.';
            return '';
        },
        ip_address(value) {
            if (!value.trim()) return 'IP Address wajib diisi.';
            const ipv4Regex = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
            if (!ipv4Regex.test(value)) return 'Format IP Address tidak valid.';
            return '';
        },
        tanggal_registrasi(value) {
            if (!value) return 'Tanggal registrasi wajib diisi.';
            const today = new Date();
            today.setHours(0,0,0,0);
            const tgl = new Date(value);
            return '';
        },
        id_paket(value) {
            if (!value) return 'Paket layanan wajib dipilih.';
            return '';
        },
        id_area(value) {
            if (!value) return 'Area wajib dipilih.';
            return '';
        },
        id_sales(value) {
            if (!value) return 'Sales wajib dipilih.';
            return '';
        }
    };

    function getErrorElement(field) {
        // cari .invalid-feedback di dalam group yang sama
        const group = field.closest('.mb-3, .md-6');
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

    // Pasang event listener realtime
    Object.keys(validators).forEach(function (name) {
        const field = form.elements[name];
        if (!field) return;

        const eventType = (field.tagName === 'SELECT' || field.type === 'date') ? 'change' : 'input';

        field.addEventListener(eventType, function () {
            validateField(field);
        });

        field.addEventListener('blur', function () {
            validateField(field);
        });
    });

    // Validasi semua sebelum submit
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

        if (firstInvalid) {
            e.preventDefault();
            firstInvalid.focus();
        }
    });
});
</script>
@endpush