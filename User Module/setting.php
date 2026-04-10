<?php
require_once("db.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT theme_mode, font_size FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<h2>SETTINGS</h2>

<form action="save_preferences.php" method="POST">
    Theme:
    <select name="theme_mode">
        <option value="light" <?= $user['theme_mode'] == 'light' ? 'selected' : '' ?>>Light</option>
        <option value="dark" <?= $user['theme_mode'] == 'dark' ? 'selected' : '' ?>>Dark</option>
    </select>
    <br><br>

    Font Size:
    <input type="number" name="font_size" value="<?= $user['font_size'] ?>" min="12" max="40">
    <br><br>

    <button type="submit">Save Preferences</button>
</form>

<br>

<h3>Change Password</h3>

<form action="change_password.php" method="POST">
    Old Password:
    <input type="password" name="old_password" required>
    <br><br>

    New Password:
    <input type="password" name="new_password" required>
    <br><br>

    <button type="submit">Change Password</button>
</form>

<br>

<a href="profile.php">Back to Profile</a>