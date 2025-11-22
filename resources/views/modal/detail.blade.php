<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title fw-bold">Detail Pelanggan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex flex-column flex-md-row align-items-center gap-4">
            <div class="text-center">
                <i class="bi bi-person-circle" style="font-size: 80px;"></i>
            </div>
            <div class="flex-grow-1">
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Nama</div>
                    <div class="col-sm-8" id="detail-nama"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">No. Telepon</div>
                    <div class="col-sm-8" id="detail-nomor"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">NIK</div>
                    <div class="col-sm-8" id="detail-nik"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Alamat</div>
                    <div class="col-sm-8" id="detail-alamat"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">IP Address</div>
                    <div class="col-sm-8" id="detail-ip"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Tanggal Registrasi</div>
                    <div class="col-sm-8" id="detail-tanggal"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Status</div>
                    <div class="col-sm-8">
                        <span class="badge" id="detail-status"></span>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Paket</div>
                    <div class="col-sm-8" id="detail-paket"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Area</div>
                    <div class="col-sm-8" id="detail-area"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Sales</div>
                    <div class="col-sm-8" id="detail-sales"></div>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
