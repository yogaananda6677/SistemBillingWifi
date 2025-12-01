@extends('sales.layouts.sales-master')

@section('content')
<div class="menu-section">
    <div class="section-header">
        <div class="section-icon">
            <i class="bi bi-person-gear"></i>
        </div>
        <h6 class="section-title">Edit Profil</h6>
    </div>
    
    <div class="p-3">
        <form>
            <!-- Avatar -->
            <div class="text-center mb-4">
                <div class="profile-avatar mx-auto mb-2">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill">
                    <i class="bi bi-camera me-1"></i>Ubah Foto
                </button>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" value="{{ auth()->user()->name }}">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="{{ auth()->user()->email }}">
            </div>
            
            <div class="mb-3">
                <label class="form-label">No. Telepon</label>
                <input type="tel" class="form-control" placeholder="08xxxxxxxxxx">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea class="form-control" rows="3" placeholder="Alamat lengkap..."></textarea>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill py-2">
                    <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                </button>
                <a href="{{ route('sales.profile') }}" class="btn btn-outline-secondary rounded-pill py-2">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection