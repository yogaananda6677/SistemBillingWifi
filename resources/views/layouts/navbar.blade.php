<nav class="navbar px-4 py-3 bg-white shadow-sm d-flex justify-content-between align-items-center"
     style="position: sticky; top:0; z-index: 1000;">

    <!-- Logo di Navbar -->
    <div class="d-flex align-items-center gap-3">
        <img src=" {{ asset('img/logo.webp') }} " style="width: 60px;">
        <span class="fw-semibold fs-5">Nalendra Payment</span>
    </div>

    <!-- Username + Toggle -->

    <div class="d-flex align-items-center gap-3">
        @auth
            <span class="fw-semibold"> {{ ucwords(Auth::user()->name)  }} </span>
        @else
            <a href="{{ route('login') }}">Login</a> | <a href="{{ route('register') }}">Register</a>
        @endauth

        <button id="toggleSidebar" class="btn p-0">
            <i class="bi bi-list fs-3"></i>
        </button>
    </div>
</nav>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebarContainer");

    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
    });
});
</script>
