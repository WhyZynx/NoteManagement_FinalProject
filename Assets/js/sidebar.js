
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const btn = document.querySelector('.menu-toggle');
    const icon = btn.querySelector('i');

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        sidebar.classList.toggle('active');
        
        if (sidebar.classList.contains('active')) {
            icon.classList.replace('fa-bars', 'fa-xmark');
        } else {
            icon.classList.replace('fa-xmark', 'fa-bars');
        }
    });

    document.addEventListener('click', function(event) {
        if (sidebar.classList.contains('active') && !sidebar.contains(event.target) && !btn.contains(event.target)) {
            sidebar.classList.remove('active');
            icon.classList.replace('fa-xmark', 'fa-bars');
        }
    });
});
