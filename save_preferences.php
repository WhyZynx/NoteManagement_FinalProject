<?php
require_once("db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["user_id"];
$theme = $_POST["theme_mode"];
$font_size = $_POST["font_size"];

$stmt = $conn->prepare("UPDATE users SET theme_mode = ?, font_size = ? WHERE id = ?");
$stmt->bind_param("sii", $theme, $font_size, $id);

if ($stmt->execute()) {
    header("Location: setting.php");
} else {
    echo "Lỗi lưu settings!";
}