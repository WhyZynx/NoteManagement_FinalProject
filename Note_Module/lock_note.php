<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/response.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    response("error", "Unauthorized");
}

$userId = $_SESSION['user_id'];
$noteId = intval($_POST['note_id'] ?? 0);
$action = $_POST['action'] ?? 'lock'; 

if ($noteId <= 0) {
    response("error", "Invalid note ID");
}


$stmt = $conn->prepare("SELECT user_id FROM notes WHERE id = ?");
$stmt->bind_param("i", $noteId);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();

if (!$note) {
    response("error", "Note not found");
}


$isOwner = ($note['user_id'] == $userId);
if (!$isOwner) {
    $s = $conn->prepare("SELECT permission FROM shared_notes WHERE note_id = ? AND shared_with = ?");
    $s->bind_param("ii", $noteId, $userId);
    $s->execute();
    $share = $s->get_result()->fetch_assoc();
    if (!$share || $share['permission'] !== 'edit') {
        response("error", "Unauthorized");
    }
}


if ($action === 'lock') {
    $password = $_POST['password'] ?? '';
    if (strlen($password) < 4) {
        response("error", "Password too short");
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);


    $check = $conn->prepare("SELECT note_id FROM note_passwords WHERE note_id = ?");
    $check->bind_param("i", $noteId);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();

    if ($exists) {
        $stmtPw = $conn->prepare("UPDATE note_passwords SET password_hash = ? WHERE note_id = ?");
        $stmtPw->bind_param("si", $hashed, $noteId);
    } else {
        $stmtPw = $conn->prepare("INSERT INTO note_passwords (note_id, password_hash) VALUES (?, ?)");
        $stmtPw->bind_param("is", $noteId, $hashed);
    }
    $stmtPw->execute();

    $upd = $conn->prepare("UPDATE notes SET is_locked = 1 WHERE id = ?");
    $upd->bind_param("i", $noteId);
    $upd->execute();

    response("success", "Note locked");
}

if ($action === 'unlock') {
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT password_hash FROM note_passwords WHERE note_id = ?");
    $stmt->bind_param("i", $noteId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        response("error", "Note is not locked");
    }

    if (!password_verify($password, $row['password_hash'])) {
        response("error", "Wrong password");
    }


    $del = $conn->prepare("DELETE FROM note_passwords WHERE note_id = ?");
    $del->bind_param("i", $noteId);
    $del->execute();


    $upd = $conn->prepare("UPDATE notes SET is_locked = 0 WHERE id = ?");
    $upd->bind_param("i", $noteId);
    $upd->execute();

    response("success", "Note unlocked");
}


if ($action === 'verify') {
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT password_hash FROM note_passwords WHERE note_id = ?");
    $stmt->bind_param("i", $noteId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        response("error", "Note is not locked");
    }

    if (!password_verify($password, $row['password_hash'])) {
        response("error", "Wrong password");
    }

    response("success", "Password correct");
}

response("error", "Unknown action");
?>
