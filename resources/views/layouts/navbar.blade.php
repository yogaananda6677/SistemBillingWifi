<nav class="navbar bg-white shadow-sm px-4 py-3 d-flex justify-content-between align-items-center"
     style="position: sticky; top: 0; z-index: 1000;">

    <div class="d-flex align-items-center gap-3">
        <img src="/img/logo.webp" alt="Logo" style="width: 48px; height: auto;">
        <span class="fw-bold fs-5 text-dark">Nalendra</span>
    </div>

    <div class="d-flex align-items-center gap-3">
        @auth
            <span class="fw-semibold"> {{ ucwords(Auth::user()->name)  }} </span>
        @else
            <a href="{{ route('login') }}" class="text-decoration-none">Login</a> | 
            <a href="{{ route('register') }}" class="text-decoration-none">Register</a>
        @endauth

        <button id="toggleSidebar" class="btn border-0 p-0 d-flex align-items-center">
            <i class="bi bi-list fs-2 text-dark"></i>
        </button>
    </div>
</nav>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebarContainer");

    if (!toggleBtn || !sidebar) return;

    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
    });
});
</script>