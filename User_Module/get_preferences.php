<?php
require_once __DIR__ . '/../db.php';
session_start();

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["status" => "error"]);
    exit;
}

$userId = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT theme_mode, font_size, font_style, view_mode
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

echo json_encode([
    "status" => "success",
    "data" => $user
]);