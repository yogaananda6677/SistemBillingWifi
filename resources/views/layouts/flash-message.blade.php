{{-- ========================= --}}
{{--     FLASH MESSAGE CSS     --}}
{{-- ========================= --}}
<style>
    /* Warna tema kuning */
    .swal2-popup {
        border-radius: 12px !important;
        padding: 20px !important;
    }

    .swal2-title {
        font-weight: 700 !important;
        font-size: 20px !important;
        color: #1f2937 !important;
    }

    .swal2-html-container {
        font-size: 14px !important;
        color: #4b5563 !important;
        margin-top: 10px !important;
    }

    /* Custom warna ikon success */
    .swal2-icon.swal2-success {
        border-color: #f59e0b !important;
        color: #f59e0b !important;
    }

    .swal2-icon.swal2-success .swal2-success-ring {
        border-color: #f59e0b55 !important;
    }

    .swal2-icon.swal2-success [class^="swal2-success-line"] {
        background-color: #f59e0b !important;
    }

    /* Ikon error tetap merah */
    .swal2-icon.swal2-error {
        border-color: #ef4444 !important;
        color: #ef4444 !important;
    }

    .swal2-icon.swal2-error [class^="swal2-x-mark-line"] {
        background-color: #ef4444 !important;
    }

    /* Tombol */
    .swal2-confirm {
        border-radius: 8px !important;
        padding: 8px 20px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
    }

    .swal2-confirm:focus {
        outline: none !important;
        box-shadow: none !important;
    }
</style>

{{-- CDN SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- ========================= --}}
{{--     FLASH MESSAGE JS      --}}
{{-- ========================= --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // --- NOTIFIKASI SUKSES (Fortify atau session umum) ---
        @if (session('status'))
            let message = "{{ session('status') }}";

            // Translate default Fortify message → BI
            if (message === 'profile-information-updated') {
                message = 'Profil berhasil diperbarui!';
            } else if (message === 'password-updated') {
                message = 'Password berhasil diubah!';
            }

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                confirmButtonColor: '#f59e0b',
                timer: 2500,
                timerProgressBar: true
            });
        @endif

        // --- NOTIFIKASI SUKSES (custom session success) ---
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                confirmButtonColor: '#f59e0b',
                timer: 2500,
                timerProgressBar: true
            });
        @endif

        // --- NOTIFIKASI ERROR (validasi Laravel) ---
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: `<ul style="text-align:left;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>`,
                confirmButtonColor: '#d33'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#d33'
            });
        @endif

    });
</script>
