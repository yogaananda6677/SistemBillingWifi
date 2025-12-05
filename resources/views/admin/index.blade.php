@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4">
        {{-- Header dengan gradient --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary shadow-lg border-0">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="h3 mb-0 text-white">Daftar Admin</h1>
                                <p class="text-white opacity-8 mb-0">Kelola akun administrator sistem</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button onclick="openModal()" class="btn btn-light btn-lg shadow-sm rounded-pill px-4">
                                    <i class="bi bi-plus-circle me-2"></i>Tambah Admin Baru
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alert Success
        @if (session('success'))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                            <div>
                                <h6 class="mb-0">Berhasil!</h6>
                                <p class="mb-0">{{ session('success') }}</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        @endif --}}

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start border-info border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                    Total Admin
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $admins->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start border-success border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                    Password Default
                                </div>
                                <div class="h6 mb-0 fw-medium text-gray-800">admin123456</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-shield-check fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">Daftar Administrator</h6>
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" id="searchInput" class="form-control border-start-0"
                                    placeholder="Cari admin...">
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 fw-semibold text-muted">NAMA ADMIN</th>
                                        {{-- <th class="fw-semibold text-muted">USERNAME</th> --}}
                                        <th class="fw-semibold text-muted">EMAIL</th>
                                        <th class="fw-semibold text-muted">NO. HP</th>
                                        <th class="fw-semibold text-muted text-center">STATUS</th>
                                        <th class="fw-semibold text-muted text-end pe-4">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody id="adminTable">
                                    @forelse ($admins as $adm)
                                        <tr class="border-bottom">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        <i class="bi bi-person-fill text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold">{{ $adm->name }}</h6>
                                                        <small class="text-muted">ID: {{ $adm->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- <td>
                                                <span class="badge bg-light text-dark border">
                                                    @<span class="fw-medium">{{ $adm->username }}</span>
                                                </span>
                                            </td> --}}
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-envelope me-2 text-muted"></i>
                                                    {{ $adm->email }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-phone me-2 text-muted"></i>
                                                    {{ $adm->no_hp ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                                    <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i>
                                                    Aktif
                                                </span>
                                            </td>
                                            <td class="pe-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <!-- Edit Button -->
                                                    {{-- <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                        onclick="editAdmin({{ $adm->id }})">
                                                        <i class="bi bi-pencil me-1"></i> Edit
                                                    </button> --}}

                                                    <!-- Delete Form -->

                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger btn-delete rounded-pill px-3"
                                                        data-url="{{ route('admin.destroy', $adm->id) }}">
                                                        <i class="bi bi-trash me-1"></i> Hapus
                                                    </button>

                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="noDataRow">
                                            <td colspan="6" class="text-center py-5">
                                                <div class="py-5">
                                                    <i class="bi bi-people display-1 text-muted opacity-25"></i>
                                                    <h5 class="text-muted mt-3">Belum ada data admin</h5>
                                                    <p class="text-muted mb-0">Tambahkan admin baru untuk memulai</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Jika menggunakan pagination di controller, ganti dengan kode ini --}}
                        {{--
                    @if ($admins instanceof \Illuminate\Pagination\LengthAwarePaginator && $admins->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Menampilkan {{ $admins->firstItem() }} - {{ $admins->lastItem() }} dari {{ $admins->total() }} admin
                            </div>
                            <div>
                                {{ $admins->links() }}
                            </div>
                        </div>
                    </div>
                    @endif
                    --}}

                        {{-- Info jumlah data --}}
                        @if ($admins->count() > 0)
                            <div class="card-footer bg-white border-0 py-3">
                                <div class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Menampilkan {{ $admins->count() }} admin
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Admin --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient-primary text-white border-0">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>Tambah Admin Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('admin.store') }}" method="POST" id="adminForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                                <input type="text" name="name" class="form-control border-start-0"
                                    placeholder="Masukkan nama lengkap" required>
                            </div>
                        </div>

                        {{-- <div class="mb-3">
                            <label class="form-label fw-semibold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-at text-muted"></i>
                                </span>
                                <input type="text" name="username" class="form-control border-start-0"
                                    placeholder="Masukkan username" required>
                            </div>
                            <div class="form-text text-danger" id="usernameError" style="display: none;">
                                Username sudah digunakan
                            </div>
                        </div> --}}

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input type="email" name="email" class="form-control border-start-0"
                                    placeholder="Masukkan email" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nomor HP</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-phone text-muted"></i>
                                </span>
                                <input type="tel" name="no_hp" class="form-control border-start-0"
                                    placeholder="Masukkan nomor HP">
                            </div>
                        </div>

                        {{-- <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-key text-muted"></i>
                                </span>
                                <input type="password" name="password" class="form-control border-start-0"
                                    placeholder="Masukkan password" required minlength="8">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Password minimal 8 karakter dengan kombinasi huruf dan angka
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-key-fill text-muted"></i>
                                </span>
                                <input type="password" name="password_confirmation" class="form-control border-start-0"
                                    placeholder="Ulangi password" required minlength="8">
                            </div>
                        </div> --}}

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Batal
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-save me-1"></i>Simpan Admin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Styles Custom --}}
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
        }

        .card {
            border-radius: 1rem !important;
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .avatar-sm {
            width: 40px;
            height: 40px;
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .border-start {
            border-left-width: 4px !important;
        }

        .modal-content {
            border-radius: 1rem !important;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
            border-color: #4e73df;
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 4px;
            transition: all 0.3s ease;
        }

        .password-strength.weak {
            width: 25%;
            background-color: #dc3545;
        }

        .password-strength.medium {
            width: 50%;
            background-color: #ffc107;
        }

        .password-strength.strong {
            width: 75%;
            background-color: #198754;
        }

        .password-strength.very-strong {
            width: 100%;
            background-color: #198754;
        }
    </style>

    {{-- Scripts --}}
    <script>
        // Modal functions
        function openModal() {
            const modal = new bootstrap.Modal(document.getElementById('modalCreate'));
            modal.show();
        }

        // Toggle password visibility
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const passwordInput = document.querySelector('input[name="password"]');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        // Search functionality
        document.getElementById('searchInput')?.addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#adminTable tr:not(#noDataRow)');
            const noDataRow = document.getElementById('noDataRow');
            let hasVisibleRows = false;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Tampilkan pesan jika tidak ada hasil pencarian
            if (noDataRow) {
                if (!hasVisibleRows && searchTerm !== '') {
                    noDataRow.style.display = '';
                    noDataRow.querySelector('h5').textContent = 'Tidak ditemukan';
                    noDataRow.querySelector('p').textContent = 'Tidak ada admin yang cocok dengan pencarian';
                } else if (rows.length === 0) {
                    noDataRow.style.display = '';
                } else {
                    noDataRow.style.display = 'none';
                }
            }
        });

        // Edit admin function
        function editAdmin(id) {
            // Implement edit functionality here
            alert('Edit admin dengan ID: ' + id + '\n\nFitur edit akan diimplementasikan kemudian.');
        }

        // Password strength indicator
        const passwordInput = document.querySelector('input[name="password"]');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strengthDiv = document.getElementById('passwordStrength') || createStrengthIndicator();

                if (password.length === 0) {
                    strengthDiv.style.width = '0%';
                    return;
                }

                let strength = 0;

                // Length check
                if (password.length >= 8) strength += 1;
                if (password.length >= 12) strength += 1;

                // Character variety checks
                if (/[a-z]/.test(password)) strength += 1;
                if (/[A-Z]/.test(password)) strength += 1;
                if (/[0-9]/.test(password)) strength += 1;
                if (/[^a-zA-Z0-9]/.test(password)) strength += 1;

                // Update strength indicator
                if (strength <= 2) {
                    strengthDiv.className = 'password-strength weak';
                    strengthDiv.style.width = '25%';
                } else if (strength <= 4) {
                    strengthDiv.className = 'password-strength medium';
                    strengthDiv.style.width = '50%';
                } else if (strength <= 6) {
                    strengthDiv.className = 'password-strength strong';
                    strengthDiv.style.width = '75%';
                } else {
                    strengthDiv.className = 'password-strength very-strong';
                    strengthDiv.style.width = '100%';
                }
            });

            function createStrengthIndicator() {
                const div = document.createElement('div');
                div.id = 'passwordStrength';
                div.className = 'password-strength';
                passwordInput.parentNode.parentNode.appendChild(div);
                return div;
            }
        }


        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            const deleteForm = document.getElementById('deleteForm');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const url = this.dataset.url;
                    deleteForm.action = url; // set action form
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                    deleteModal.show();
                });
            });
        });


        // Form validation
        document.getElementById('adminForm')?.addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="password_confirmation"]').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password minimal 8 karakter!');
                return;
            }
        });

        // Check username availability (contoh)
        const usernameInput = document.querySelector('input[name="username"]');
        if (usernameInput) {
            let timeout = null;

            usernameInput.addEventListener('input', function() {
                clearTimeout(timeout);
                const username = this.value;

                if (username.length < 3) return;

                timeout = setTimeout(() => {
                    // Simulasi check username
                    // Di implementasi sebenarnya, ini akan melakukan AJAX request ke server
                    console.log('Checking username:', username);
                }, 500);
            });
        }
    </script>
@endsection
