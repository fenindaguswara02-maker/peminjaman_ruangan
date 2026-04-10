document.addEventListener('DOMContentLoaded', function() {
    // Pastikan header tetap di atas
    const header = document.querySelector('header');
    if (header) {
        header.style.zIndex = '40';
    }

    // Sidebar toggle untuk mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            sidebar.classList.toggle('fixed');
        });
    }
});