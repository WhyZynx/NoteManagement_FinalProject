<?php
global $pref; 
$mode = $pref['theme_mode'] ?? 'light';
$colorStr = $pref['theme_color'] ?? '#5385c7';


$colors = explode('|', $colorStr);
$baseColor = $colors[0];
$secondaryColor = $colors[1] ?? $baseColor;

$current_page = basename($_SERVER['PHP_SELF']);
$menus = [
    ['link' => '../index.php', 'icon' => 'fa-house', 'label' => 'Home'],
    ['link' => 'profile.php', 'icon' => 'fa-address-card', 'label' => 'View Profile'],
    ['link' => 'change_password.php', 'icon' => 'fa-keyboard', 'label' => 'Password'],
    ['link' => 'setting.php', 'icon' => 'fa-gear', 'label' => 'Settings'],
];
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@400;500;600&family=Nunito:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">

<style>
    :root {
        --user-color: <?= htmlspecialchars($baseColor) ?>;
        --user-color-2: <?= htmlspecialchars($secondaryColor) ?>;
    }

    body, input, select, button, a, h1:not(.brand-name), h2, h3, p, span:not(.brand-name):not(.brand-sub), label {
        font-family: <?= $pref['font_style'] ?? "'Inter', sans-serif" ?> !important;
    }
    
    body {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .sidebar-brand, .sidebar-brand .brand-name, .sidebar-brand .brand-sub {
        font-family: 'Inter', 'Segoe UI', sans-serif !important;
    }

    i[class*="fa-"] {
        font-family: "Font Awesome 6 Free" !important;
    }


    .btn-save, .btn-modern-save, .btn-confirm-save { 
        background-color: #0d6efd !important; 
        color: #fff !important; 
        border: none !important; 
        text-shadow: 0 1px 3px rgba(0,0,0,0.3) !important; 
        transition: 0.3s;
    }
    
    .btn-save:hover, .btn-modern-save:hover, .btn-confirm-save:hover { 
        filter: brightness(85%) !important;
    }



    body.hologram {
        --card-bg: rgba(255, 255, 255, 0.4) !important;
        --card-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1) !important;
    }
    body.hologram { 
        background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%) !important; 
        background-attachment: fixed !important;
    }
    body.hologram .sidebar { background: rgba(255, 255, 255, 0.3) !important; backdrop-filter: blur(15px); border-right: 1px solid rgba(255, 255, 255, 0.3); }
    body.hologram .setting-card, body.hologram .info-card, body.hologram .welcome-card, body.hologram .edit-container-card { backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.4); }
    body.hologram .nav-link.active { background: rgba(255, 255, 255, 0.6) !important; }
    
    body.hologram .btn-save, body.hologram .btn-modern-save, body.hologram .btn-confirm-save { background-color: #a18cd1 !important; }


    body.gradient {
        --accent-dark: color-mix(in srgb, var(--user-color) 60%, #333333) !important;
    }
    
    body.gradient, body.gradient .main-wrapper, body.gradient .content { 
        background: linear-gradient(135deg, var(--user-color) 0%, var(--user-color-2) 100%) !important; 
        background-attachment: fixed !important;
    }
    
    body.gradient .sidebar { 
        background-color: rgba(255, 255, 255, 0.8) !important; 
        backdrop-filter: blur(15px);
        border-right: 1px solid rgba(255, 255, 255, 0.4) !important;
    }
    
    body.gradient .setting-card, body.gradient .info-card, body.gradient .welcome-card, body.gradient .edit-container-card { 
        background: rgba(255, 255, 255, 0.95) !important;
        border-top: 4px solid var(--user-color) !important; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
    
    body.gradient .nav-link.active { background-color: color-mix(in srgb, var(--user-color) 15%, transparent) !important; color: var(--accent-dark) !important; }
    body.gradient h1, body.gradient h2, body.gradient h3, body.gradient .nav-link.active i { color: var(--accent-dark) !important; }
    body.gradient .btn-save, body.gradient .btn-modern-save, body.gradient .btn-confirm-save { background: linear-gradient(90deg, var(--user-color), var(--user-color-2)) !important; }


    body.custom { 
        --accent-dark: color-mix(in srgb, var(--user-color) 60%, #333333) !important;
        --bg-light: color-mix(in srgb, var(--user-color) 12%, #ffffff) !important;
        --card-bg: color-mix(in srgb, var(--user-color) 3%, #ffffff) !important;
        --text-dark: color-mix(in srgb, var(--user-color) 20%, #4a4a4a) !important;
        --text-muted: color-mix(in srgb, var(--user-color) 30%, #888888) !important;
        --card-shadow: 0 10px 30px color-mix(in srgb, var(--user-color) 20%, transparent) !important;
    }

    body.custom, 
    body.custom .main-wrapper, 
    body.custom .content { 
        background-color: var(--bg-light) !important; 
    }

    body.custom .sidebar { 
        background-color: color-mix(in srgb, var(--user-color) 8%, #ffffff) !important; 
        border-right: 1px solid color-mix(in srgb, var(--user-color) 20%, transparent) !important;
    }
    body.custom .setting-card, body.custom .info-card, body.custom .welcome-card, body.custom .edit-container-card { border-top: 4px solid var(--user-color) !important; }
    
    body.custom .nav-link.active { background-color: color-mix(in srgb, var(--user-color) 15%, transparent) !important; color: var(--accent-dark) !important; }
    body.custom h1, body.custom h2, body.custom h3, body.custom .nav-link.active i { color: var(--accent-dark) !important; }

    body.custom .btn-save, body.custom .btn-modern-save, body.custom .btn-confirm-save { background-color: var(--user-color) !important; }


    body.dark {
        --bg-light: #1e2233 !important; 
        --card-bg: #272d40 !important;  
        --text-dark: #ffffff !important;
        --text-muted: #a1a9b8 !important; 
        --card-shadow: 0 8px 25px rgba(0,0,0,0.2) !important; 
    }
    body.dark .sidebar { background: #22273b !important; border-right: 1px solid #363d54 !important; }
    body.dark .setting-card, body.dark .info-card, body.dark .welcome-card, body.dark .edit-container-card { border: 1px solid #363d54 !important; }
    
    body.dark input:not(.val-badge-input), body.dark select, body.dark .edit-input-text { 
        background: #1e2233 !important; 
        color: #fff !important; 
        border: 1px solid #3f4760 !important; 
    }
    body.dark input:focus, body.dark select:focus, body.dark .edit-input-text:focus { border-color: #3b82f6 !important; }
    
    body.dark label, body.dark h1, body.dark h2, body.dark h3, body.dark span:not(.status-tag) { color: #f5f5f5 !important; }
    body.dark .text-muted, body.dark .brand-sub { color: #a1a9b8 !important; }
    body.dark .brand-name { color: #fff !important; }
    
    body.dark .nav-link { color: #a1a9b8 !important; }
    body.dark .nav-link:hover:not(.active) { background: rgba(255, 255, 255, 0.08) !important; }
    body.dark .nav-link.active { background: #323950 !important; color: #fff !important; box-shadow: none !important; }
    
    body.dark .ocean-bg { opacity: 0.25; } 

    body.dark .btn-save, body.dark .btn-modern-save, body.dark .btn-confirm-save { background-color: #3b82f6 !important; }

    .content { z-index: 1; }
    .ocean-bg { pointer-events: none; }

    .ocean-bg {
        z-index: 10 !important;
        pointer-events: none;
    }

    .setting-card, .info-card, .welcome-card, .edit-container-card {
        position: relative;
        z-index: 1;
        overflow: visible !important;
    }

    .main-wrapper, .content {
        overflow: visible !important;
    }

</style>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <span class="brand-name">MindFlow</span> <span class="brand-sub">Account</span>
    </div>
    
    <nav class="sidebar-nav">
        <?php foreach ($menus as $menu): ?>
            <a href="<?= $menu['link'] ?>" 
               class="nav-link <?= ($current_page == basename($menu['link'])) ? 'active' : '' ?>">
                <i class="fa-solid <?= $menu['icon'] ?>"></i> <?= $menu['label'] ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="#">Privacy</a>
        <a href="#">Clause</a>
        <a href="#">Help</a>
        <a href="#">Introduce</a>
    </div>
</div>