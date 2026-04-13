<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/preferences.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$pref = getPreferences($conn, $userId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings - MindFlow</title>
    <link rel="stylesheet" href="../Assets/css/theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>

<body class="<?= $pref['theme_mode'] ?>"
      style="font-size: <?= $pref['font_size'] ?>px;
             font-family: <?= $pref['font_style'] ?>;">

    <div class="main-wrapper">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <?php include "sidebar.php"; ?>

        <div class="content">
            <h2 class="page-title">User Preferences</h2>
            <p class="page-subtitle">Explore the depths of your mind, one setting at a time.</p>

            <div class="setting-card">
                <form action="save_preferences.php" method="POST">
                    
                    <div class="setting-group">
                        <label><i class="fas fa-palette"></i> Appearance Theme</label>
                        <select name="theme_mode">
                            <option value="light" <?= $pref['theme_mode']=='light'?'selected':'' ?>>Light Mode (Healing)</option>
                            <option value="dark" <?= $pref['theme_mode']=='dark'?'selected':'' ?>>Dark Mode (Deep)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-save">Save Changes</button>
                </form>
            </div>

            <img src="../Assets/images/web_img/ocean.png" alt="ocean" class="ocean-bg">
        </div>
    </div>
    <script src="../Assets/js/sidebar.js"></script>
</body>
</html>