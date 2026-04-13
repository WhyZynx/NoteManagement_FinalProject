<?php
require_once __DIR__ . '/../db.php';
session_start();

$note_id = intval($_GET['note_id'] ?? 0);

$stmt = $conn->prepare("SELECT id, image_path FROM note_images WHERE note_id = ?");
$stmt->bind_param("i", $note_id);
$stmt->execute();

$result = $stmt->get_result();

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $images
]);