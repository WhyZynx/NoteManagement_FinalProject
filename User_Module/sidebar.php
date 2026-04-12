<?php
$current_page = basename($_SERVER['PHP_SELF']);
$menus = [
    ['link' => '../index.php', 'icon' => 'fa-house', 'label' => 'Home'],
    ['link' => 'profile.php', 'icon' => 'fa-address-card', 'label' => 'View Profile'],
    ['link' => 'change_password.php', 'icon' => 'fa-keyboard', 'label' => 'Password'],
    ['link' => 'setting.php', 'icon' => 'fa-gear', 'label' => 'Settings'],
];
?>

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