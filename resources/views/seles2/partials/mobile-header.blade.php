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

        <div class="d-flex align-items-center gap-3">


            {{-- Dropdown Trigger --}}
            <div class="dropdown">
                <a href="#" class="text-white" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical fs-4"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('seles2.profile') }}">
                            <i class="bi bi-person-gear me-2"></i> Akun Saya
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>

<style>
    .dropdown-menu {
    border-radius: 12px;
    padding: 8px 0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.dropdown-item {
    padding: 10px 16px;
    font-size: 0.9rem;
}

</style>