<?php
require_once("db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["user_id"];
$display_name = $_POST["display_name"];

$stmt = $conn->prepare("UPDATE users SET display_name = ? WHERE id = ?");
$stmt->bind_param("si", $display_name, $id);

if ($stmt->execute()) {
    header("Location: profile.php");
} else {
    echo "Lỗi cập nhật!";
}