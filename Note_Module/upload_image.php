<?php
require_once("db.php");
require_once("response.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    response("error", "User not authenticated");
}

$note_id = intval($_POST["note_id"] ?? 0);

if ($note_id <= 0) {
    response("error", "Invalid note ID");
}

if (!isset($_FILES["image"])) {
    response("error", "No image uploaded");
}

$dir = "uploads/";

if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$file_name = time() . "_" . basename($_FILES["image"]["name"]);
$path = $dir . $file_name;

if (!move_uploaded_file($_FILES["image"]["tmp_name"], $path)) {
    response("error", "Failed to upload image");
}

$stmt = $conn->prepare(
    "INSERT INTO note_images (note_id, image_path, created_at)
     VALUES (?, ?, NOW())"
);

$stmt->bind_param("is", $note_id, $path);
$stmt->execute();

response("success", "Image uploaded successfully", [
    "path" => $path
]);