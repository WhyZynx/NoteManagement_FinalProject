<?php
require_once("db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["user_id"];
$old = $_POST["old_password"];
$new = $_POST["new_password"];

$stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && password_verify($old, $user["password_hash"])) {

    $new_hash = password_hash($new, PASSWORD_BCRYPT);

    $update = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $update->bind_param("si", $new_hash, $id);
    $update->execute();

    echo "Đổi mật khẩu thành công! <a href='setting.php'>Quay lại</a>";

} else {
    echo "Sai mật khẩu cũ!";
}