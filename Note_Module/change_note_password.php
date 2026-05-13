<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/response.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    response("error", "Unauthorized");
}

$userId  = $_SESSION['user_id'];
$noteId  = intval($_POST['note_id'] ?? 0);
$oldPw   = $_POST['old_password'] ?? '';
$newPw   = $_POST['new_password'] ?? '';

if ($noteId <= 0 || !$oldPw || !$newPw) {
    response("error", "Missing data");
}

if (strlen($newPw) < 4) {
    response("error", "New password too short (min 4 chars)");
}


$stmt = $conn->prepare("SELECT user_id FROM notes WHERE id = ?");
$stmt->bind_param("i", $noteId);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();

if (!$note || $note['user_id'] != $userId) {
    response("error", "Unauthorized");
}


$stmt = $conn->prepare("SELECT password_hash FROM note_passwords WHERE note_id = ?");
$stmt->bind_param("i", $noteId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    response("error", "Note is not locked");
}

if (!password_verify($oldPw, $row['password_hash'])) {
    response("error", "Wrong current password");
}


$newHash = password_hash($newPw, PASSWORD_DEFAULT);
$upd = $conn->prepare("UPDATE note_passwords SET password_hash = ? WHERE note_id = ?");
$upd->bind_param("si", $newHash, $noteId);
$upd->execute();

response("success", "Password changed successfully");
?>
