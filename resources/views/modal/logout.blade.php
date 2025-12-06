<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">

            <div class="modal-body p-4 text-center">
                {{-- Tombol Close di Pojok Kanan Atas --}}
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"
                    aria-label="Close"></button>

                {{-- Ikon Bubble Modern (Kuning Soft) --}}
                <div class="d-inline-flex align-items-center justify-content-center mb-4"
                    style="width: 80px; height: 80px; background-color: #fff9e6; border-radius: 50%;">
                    <i class="fas fa-sign-out-alt" style="font-size: 30px; color: #ffc107;"></i>
                </div>

                {{-- Judul & Deskripsi --}}
                <h4 class="mb-2 fw-bold text-dark">Ingin Keluar?</h4>
                <p class="text-muted mb-4" style="font-size: 15px; line-height: 1.6;">
                    Anda akan diarahkan kembali ke halaman login.<br>
                    Pastikan pekerjaan Anda sudah disimpan.
                </p>

                {{-- Tombol Aksi --}}
                <div class="d-flex justify-content-center gap-3">
                    {{-- Tombol Batal --}}
                    <button type="button" class="btn btn-light px-4 py-2 fw-semibold" data-bs-dismiss="modal"
                        style="border-radius: 12px; min-width: 120px; color: #4b5563; background: #f3f4f6;">
                        Batal
                    </button>

                    {{-- Form Logout Logic (TIDAK BERUBAH) --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn px-4 py-2 fw-semibold"
                            style="border-radius: 12px; min-width: 120px; background-color: #ffc107; color: #000; border: none; box-shadow: 0 4px 6px -1px rgba(255, 193, 7, 0.3);">
                            Ya, Logout
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>