<?php
require_once __DIR__ . '/../db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$noteId = intval($_POST['note_id'] ?? 0);

if ($noteId <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid note ID"]);
    exit();
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT is_pinned FROM notes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $noteId, $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(["status" => "error", "message" => "Note not found"]);
    exit();
}

if ($row['is_pinned'] == 1) {
    $stmt = $conn->prepare("UPDATE notes SET is_pinned = 0, pinned_at = NULL WHERE id = ?");
} else {
    $stmt = $conn->prepare("UPDATE notes SET is_pinned = 1, pinned_at = NOW() WHERE id = ?");
}
if (isset($_POST['force_pin'])) {
    $forcePinValue = intval($_POST['force_pin']);
    if ($forcePinValue == 1) {
        $stmt = $conn->prepare("UPDATE notes SET is_pinned = 1, pinned_at = NOW() WHERE id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE notes SET is_pinned = 0, pinned_at = NULL WHERE id = ?");
    }
    $stmt->bind_param("i", $noteId);
    $stmt->execute();
    echo json_encode(["status" => "success"]);
    exit();
}

$stmt->bind_param("i", $noteId);
$stmt->execute();

echo json_encode(["status" => "success"]);