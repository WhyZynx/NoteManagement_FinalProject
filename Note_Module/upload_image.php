<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/response.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    response("error", "User not authenticated");
}

$userId = $_SESSION["user_id"];

$note_id = intval($_POST["note_id"] ?? 0);

if ($note_id <= 0) {
    response("error", "Invalid note ID");
}

if (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== 0) {
    response("error", "No image uploaded");
}

$allowedTypes = ["image/jpeg", "image/png", "image/webp"];

if (!in_array($_FILES["image"]["type"], $allowedTypes)) {
    response("error", "Invalid file type");
}

$stmt = $conn->prepare("
    SELECT id 
    FROM notes 
    WHERE id = ? AND user_id = ?
");

$stmt->bind_param("ii", $note_id, $userId);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    response("error", "Unauthorized note access");
}

$dir = __DIR__ . "/../Assets/uploads/";

if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
$file_name = time() . "_" . bin2hex(random_bytes(5)) . "." . $ext;

$full_path = $dir . $file_name;

if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
    response("error", "Failed to upload image");
}

$relative_path = "Assets/uploads/" . $file_name;

$stmt = $conn->prepare("
    INSERT INTO note_images (note_id, image_path, created_at)
    VALUES (?, ?, NOW())
");

$stmt->bind_param("is", $note_id, $relative_path);
$stmt->execute();

response("success", "Image uploaded successfully", [
    "path" => $relative_path
]);