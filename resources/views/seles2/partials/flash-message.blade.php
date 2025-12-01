{{-- Flash Messages Mobile --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show m-3 rounded-3" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-check-circle-fill me-2"></i>
        <div class="flex-grow-1">
            {{ session('success') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show m-3 rounded-3" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div class="flex-grow-1">
            {{ session('error') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
@endif