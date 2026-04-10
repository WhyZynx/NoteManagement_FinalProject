<?php
require_once("db.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT display_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<h2>PROFILE</h2>

<form action="update_profile.php" method="POST">
    Display Name:
    <input type="text" name="display_name" value="<?= $user['display_name'] ?>" required>
    <br><br>

    Email:
    <b><?= $user['email'] ?></b>
    <br><br>

    <button type="submit">Update Profile</button>
</form>

<br>

<a href="setting.php">Settings</a><br>
<a href="logout.php">Logout</a>