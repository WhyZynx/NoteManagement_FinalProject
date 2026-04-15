<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/response.php';
session_start();

if (!isset($_SESSION["user_id"])) {
    response("error", "User not authenticated");
}

$userId = $_SESSION["user_id"];
$noteId = (int)($_POST["note_id"] ?? 0);

if ($noteId <= 0) {
    response("error", "Invalid note ID");
}

$title = trim($_POST["title"] ?? "");
$content = trim($_POST["content"] ?? "");

$fontSize = (int)($_POST["font_size"] ?? 16);
$fontStyle = trim($_POST["font_style"] ?? "Inter");
$noteColor = trim($_POST["note_color"] ?? "#ffffff");

$stmt = $conn->prepare("
    UPDATE notes
    SET
        title = ?,
        content = ?,
        font_size = ?,
        font_style = ?,
        note_color = ?,
        updated_at = NOW()
    WHERE id = ? AND user_id = ?
");

$stmt->bind_param(
    "ssissii",
    $title,
    $content,
    $fontSize,
    $fontStyle,
    $noteColor,
    $noteId,
    $userId
);
$labels = json_decode($_POST["labels"] ?? "[]", true);

$conn->query("DELETE FROM note_labels WHERE note_id = $noteId");

if (!empty($labels) && is_array($labels)) {
    $stmtLabel = $conn->prepare("
        INSERT INTO note_labels (note_id, label_id)
        VALUES (?, ?)
    ");

    foreach ($labels as $labelId) {
        $labelId = (int)$labelId;
        $stmtLabel->bind_param("ii", $noteId, $labelId);
        $stmtLabel->execute();
    }
}
if ($stmt->execute()) {
    response("success", "Note saved successfully");
}

response("error", "Failed to save note");