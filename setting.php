<?php
require_once("db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<h2>SETTINGS</h2>

<h3>Đổi mật khẩu</h3>
<form action="change_password.php" method="POST">
    Mật khẩu cũ:
    <input type="password" name="old_password" required><br><br>

    Mật khẩu mới:
    <input type="password" name="new_password" required><br><br>

    <button type="submit">Đổi mật khẩu</button>
</form>

<hr>

<h3>User Preferences</h3>
<form action="save_preferences.php" method="POST">

    Theme:
    <select name="theme_mode">
        <option value="light" <?= ($user['theme_mode'] == 'light') ? 'selected' : '' ?>>Light</option>
        <option value="dark" <?= ($user['theme_mode'] == 'dark') ? 'selected' : '' ?>>Dark</option>
    </select>
    <br><br>

    Font size:
    <input type="number" name="font_size" value="<?= $user['font_size'] ?? 16 ?>">
    <br><br>

    <button type="submit">Lưu</button>
</form>

<br>
<a href="profile.php">← Quay lại Profile</a>