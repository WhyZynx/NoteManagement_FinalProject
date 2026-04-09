<?php
require_once("db.php");
require_once("response.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    response("error", "User not authenticated");
}

$note_id = intval($_POST["note_id"] ?? 0);
$title = trim($_POST["title"] ?? "");
$content = trim($_POST["content"] ?? "");

if ($note_id <= 0) {
    response("error", "Invalid note ID");
}

if ($title === "" || $content === "") {
    response("error", "Title and content are required");
}

$stmt = $conn->prepare(
    "UPDATE notes
     SET title = ?, content = ?
     WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ssii", $title, $content, $note_id, $_SESSION["user_id"]);

if ($stmt->execute()) {
    response("success", "Note updated successfully");
} else {
    response("error", "Failed to update note");
}