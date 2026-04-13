<?php
require_once __DIR__ . '/../db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["status" => "error"]);
    exit;
}

$userId = $_SESSION["user_id"];
$imageId = intval($_POST["image_id"] ?? 0);

if ($imageId <= 0) {
    echo json_encode(["status" => "error"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT ni.image_path
    FROM note_images ni
    JOIN notes n ON ni.note_id = n.id
    WHERE ni.id = ? AND n.user_id = ?
");

$stmt->bind_param("ii", $imageId, $userId);
$stmt->execute();

$result = $stmt->get_result();
$image = $result->fetch_assoc();

if (!$image) {
    echo json_encode(["status" => "error"]);
    exit;
}

$filePath = __DIR__ . "/../" . $image["image_path"];

if (file_exists($filePath)) {
    unlink($filePath);
}

$stmt = $conn->prepare("DELETE FROM note_images WHERE id = ?");
$stmt->bind_param("i", $imageId);
$stmt->execute();

echo json_encode(["status" => "success"]);