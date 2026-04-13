<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/response.php';
session_start();

if (!isset($_SESSION["user_id"])) {
    response("error", "User not authenticated");
}

$userId = $_SESSION["user_id"];
$noteId = (int)($_POST["note_id"] ?? 0);

$stmt = $conn->prepare("
    DELETE FROM notes
    WHERE id = ? AND user_id = ?
");

$stmt->bind_param("ii", $noteId, $userId);

if ($stmt->execute()) {
    response("success", "Note deleted successfully");
}

response("error", "Failed to delete note");