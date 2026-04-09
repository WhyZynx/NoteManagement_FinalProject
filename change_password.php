<?php
require_once("db.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$old = $_POST["old_password"] ?? "";
$new = $_POST["new_password"] ?? "";

$stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($old, $user["password_hash"])) {
    echo "Old password is incorrect";
    exit();
}

$new_hash = password_hash($new, PASSWORD_BCRYPT);

$update = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
$update->bind_param("si", $new_hash, $_SESSION["user_id"]);
$update->execute();

echo "Password changed successfully";
echo "<br><a href='setting.php'>Back</a>";