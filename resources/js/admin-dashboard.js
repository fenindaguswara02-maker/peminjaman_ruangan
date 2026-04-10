// public/assets/js/admin-dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle untuk mobile
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('-translate-x-full');
    });
    
    // Inisialisasi Chart.js
    if(document.getElementById('salesChart')) {
        // Kode chart
    }
});