<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">

            <!-- HEADER -->
            <div class="modal-header border-0"
                style="background: #ff4d4f; color: white; border-top-left-radius: 18px; border-top-right-radius: 18px;">
                <h5 class="modal-title fw-bold" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body text-center py-4">

                <div class="mb-2">
                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 42px;"></i>
                </div>

                <p class="mb-1 fw-semibold" style="font-size: 16px; color: #333;">
                    Yakin ingin menghapus data ini?
                </p>

                <p style="font-size: 13px; color: #777;">
                    Tindakan ini tidak bisa dibatalkan.
                </p>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer border-0 d-flex justify-content-between px-4 pb-4">
                <button type="button" class="btn btn-light fw-bold px-4"
                    style="border-radius: 10px; border: 1px solid #ddd;" data-bs-dismiss="modal">
                    Batal
                </button>

                <form method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn text-white fw-bold px-4"
                        style="background: #ff4d4f; border-radius: 10px;">
                        Hapus
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
