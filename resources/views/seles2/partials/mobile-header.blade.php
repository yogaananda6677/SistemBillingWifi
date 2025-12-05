{{-- Header Mobile - Yellow/Gold Theme --}}
<div class="sales-header p-3"
    style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px; box-shadow: 0 4px 20px rgba(245, 158, 11, 0.25);">

    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">

            @php
                $user = auth()->user();
                $initial = $user ? substr($user->name, 0, 1) : 'S';
                $name = $user ? $user->name : 'Sales';
            @endphp

            {{-- Avatar: Background Putih, Text Kuning Gelap --}}
            <div class="user-avatar me-3 bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                style="width: 45px; height: 45px; font-weight: bold; font-size: 1.2rem; color: #d97706;">
                {{ $initial }}
            </div>

            <div>
                <h6 class="mb-0 fw-bold" style="text-shadow: 0 1px 2px rgba(0,0,0,0.1);">Halo, {{ $name }}</h6>
                <small class="opacity-90" style="color: #fffbeb;">Sales Representative</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            {{-- Notifikasi (Opsional) --}}
            <a href="#" class="text-white position-relative">
                <i class="bi bi-bell fs-4"></i>
                <span
                    class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"
                    style="width: 10px; height: 10px;"></span>
            </a>

            {{-- Dropdown Trigger --}}
            <div class="dropdown">
                <a href="#" class="text-white" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical fs-4"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('seles2.profile') }}">
                            <i class="bi bi-person-circle me-2 text-warning"></i>
                            <span class="fw-medium">Akun Saya</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider my-1">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center py-2 text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                <span class="fw-medium">Logout</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>

<style>
    /* Styling Dropdown khusus header ini */
    .dropdown-menu {
        border-radius: 16px;
        padding: 8px;
        margin-top: 10px !important;
        background-color: #ffffff;
    }

    .dropdown-item {
        border-radius: 8px;
        transition: all 0.2s;
        color: #333;
    }

    .dropdown-item:hover {
        background-color: #fffbeb;
        /* Warna background hover kuning sangat muda */
        color: #d97706;
        /* Text hover kuning gelap */
    }

    .dropdown-item:active {
        background-color: #fcd34d;
    }
</style>
