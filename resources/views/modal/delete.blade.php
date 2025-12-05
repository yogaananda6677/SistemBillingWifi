<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">

            <div class="modal-body p-4 text-center">
                {{-- Tombol Close di Pojok Kanan Atas --}}
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"
                    aria-label="Close"></button>

                {{-- Ikon Bubble Modern --}}
                <div class="d-inline-flex align-items-center justify-content-center mb-4"
                    style="width: 80px; height: 80px; background-color: #fee2e2; border-radius: 50%;">
                    {{-- Menggunakan class icon yang sama, tapi diperbesar dan diberi warna merah modern --}}
                    <i class="fas fa-trash-alt" style="font-size: 32px; color: #dc2626;"></i>
                </div>

                {{-- Judul & Deskripsi --}}
                <h4 class="mb-2 fw-bold text-dark" id="deleteModalLabel">Hapus Data?</h4>
                <p class="text-muted mb-4 px-3" style="font-size: 15px; line-height: 1.6;">
                    Anda yakin ingin menghapus data ini secara permanen? <br>
                    <span class="text-danger fw-medium">Tindakan ini tidak dapat dibatalkan.</span>
                </p>

                {{-- Tombol Aksi --}}
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-light px-4 py-2 fw-semibold" data-bs-dismiss="modal"
                        style="border-radius: 12px; min-width: 120px; color: #4b5563; background: #f3f4f6;">
                        Batalkan
                    </button>

                    <form method="POST" id="deleteForm">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4 py-2 fw-semibold"
                            style="border-radius: 12px; min-width: 120px; background-color: #dc2626; border: none; box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.3);">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
