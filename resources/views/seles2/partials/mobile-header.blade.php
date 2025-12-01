{{-- Header Mobile --}}
<div class="sales-header bg-primary text-white p-3">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">

            @php
                $user = auth()->user();
                $initial = $user ? substr($user->name, 0, 1) : 'S';
                $name = $user ? $user->name : 'Sales';
            @endphp

            <div class="user-avatar me-3 bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" 
                 style="width: 45px; height: 45px; font-weight: bold; font-size: 1.1rem;">
                {{ $initial }}
            </div>

            <div>
                <h6 class="mb-0 fw-bold">Halo, {{ $name }}</h6>
                <small class="opacity-75">Sales</small>
            </div>

        </div>

        <div class="d-flex gap-3">
            <a href="#" class="text-white">
                <i class="bi bi-bell fs-5"></i>
            </a>
            <a href="#" class="text-white" data-bs-toggle="modal" data-bs-target="#mobileMenuModal">
                <i class="bi bi-grid fs-5"></i>
            </a>
        </div>
    </div>
</div>

<style>
.sales-header {
    border-radius: 0 0 20px 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
</style>
