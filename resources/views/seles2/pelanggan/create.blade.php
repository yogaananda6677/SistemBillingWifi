@extends('seles2.layout.master')

@section('content')
    <div class="menu-section">
        <div class="section-header">
            <div class="section-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <h6 class="section-title">Tambah Pelanggan Baru</h6>
        </div>
        <div class="p-3">
            <form>
                <div class="mb-3">
                    <label class="form-label">Nama Pelanggan</label>
                    <input type="text" class="form-control" placeholder="Masukkan nama pelanggan">
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea class="form-control" rows="3" placeholder="Masukkan alamat lengkap"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Wilayah</label>
                    <select class="form-select">
                        <option>Pilih wilayah</option>
                        <option>Kediri Kota</option>
                        <option>Kediri Kabupaten</option>
                        <option>Ngasem</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. Telepon</label>
                    <input type="tel" class="form-control" placeholder="08xxxxxxxxxx">
                </div>
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
                    <i class="bi bi-save me-2"></i>Simpan Pelanggan
                </button>
            </form>
        </div>
    </div>
@endsection
