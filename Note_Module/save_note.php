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
$fontStyle = trim($_POST["font_style"] ?? "Arial");
$noteColor = trim($_POST["note_color"] ?? "#ffffff");


// ===== CHECK PERMISSION =====

// get owner of note
$stmt = $conn->prepare("SELECT user_id FROM notes WHERE id = ?");
$stmt->bind_param("i", $noteId);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();

if (!$note) {
    response("error", "Note not found");
}

$ownerId = $note["user_id"];

$canEdit = false;

// owner
if ($ownerId == $userId) {
    $canEdit = true;
} else {
    // check shared permission
    $stmt = $conn->prepare("
        SELECT permission FROM shared_notes 
        WHERE note_id = ? AND shared_with = ?
    ");
    $stmt->bind_param("ii", $noteId, $userId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res && $res["permission"] === "edit") {
        $canEdit = true;
    }
}

if (!$canEdit) {
    response("error", "No permission to edit this note");
}


// ===== UPDATE NOTE =====

$stmt = $conn->prepare("
    UPDATE notes
    SET
        title = ?,
        content = ?,
        font_size = ?,
        font_style = ?,
        note_color = ?,
        updated_at = NOW()
    WHERE id = ?
");

$stmt->bind_param(
    "ssissi",
    $title,
    $content,
    $fontSize,
    $fontStyle,
    $noteColor,
    $noteId
);

$labels = json_decode($_POST["labels"] ?? "[]", true);
if ($ownerId == $userId) {

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
}

if ($stmt->execute()) {
    response("success", "Note saved successfully");
}

response("error", "Failed to save note");