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
    <title>Setting</title>
    <link rel="stylesheet" href="../Assets/css/theme.css">
</head>

<body class="<?= $pref['theme_mode'] ?>"
      style="font-size: <?= $pref['font_size'] ?>px;
             font-family: <?= $pref['font_style'] ?>;">

<?php include "sidebar.php"; ?>

<div class="content">

<h2>Settings</h2>

<form action="save_preferences.php" method="POST">

    <label>Theme</label><br>
    <select name="theme_mode">
        <option value="light" <?= $pref['theme_mode']=='light'?'selected':'' ?>>Light</option>
        <option value="dark" <?= $pref['theme_mode']=='dark'?'selected':'' ?>>Dark</option>
    </select>

    <br><br>

    <label>Font Size</label><br>
    <input type="number" name="font_size"
           min="12" max="30"
           value="<?= $pref['font_size'] ?>">

    <br><br>
    <label>Font Style</label><br>
    <select name="font_style">
        <option value="Sans-serif" <?= $pref['font_style']=='Sans-serif'?'selected':'' ?>>Sans-serif</option>
        <option value="Serif" <?= $pref['font_style']=='Serif'?'selected':'' ?>>Serif</option>
        <option value="Monospace" <?= $pref['font_style']=='Monospace'?'selected':'' ?>>Monospace</option>
    </select>

    <br><br>

    <button type="submit">Save Preferences</button>
</form>
<a href="profile.php">Back</a>

</div>

</body>
</html>