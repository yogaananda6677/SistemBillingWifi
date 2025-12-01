@extends('sales.layouts.sales-master')

@section('content')
<div class="menu-section">
    <div class="section-header">
        <div class="section-icon">
            <i class="bi bi-shield-lock"></i>
        </div>
        <h6 class="section-title">Ubah Password</h6>
    </div>
    
    <div class="p-3">
        <form>
            <div class="mb-3">
                <label class="form-label">Password Lama</label>
                <input type="password" class="form-control" placeholder="Masukkan password lama">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" class="form-control" placeholder="Masukkan password baru">
                <div class="form-text">Minimal 8 karakter</div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" placeholder="Ulangi password baru">
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill py-2">
                    <i class="bi bi-key me-2"></i>Ubah Password
                </button>
                <a href="{{ route('sales.profile') }}" class="btn btn-outline-secondary rounded-pill py-2">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection