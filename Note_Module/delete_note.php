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

$stmt = $conn->prepare(
    "DELETE FROM notes
     WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $note_id, $_SESSION["user_id"]);

if ($stmt->execute()) {
    response("success", "Note deleted successfully");
} else {
    response("error", "Failed to delete note");
}