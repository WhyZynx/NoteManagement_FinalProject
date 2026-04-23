<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../Utils/email.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit();
}

$owner_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'share_note') {

    $note_id = intval($_POST['note_id'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $permission = $_POST['permission'] ?? 'read';

    if (!$note_id || !$email) {
        echo json_encode(["status" => "error", "message" => "Missing data"]);
        exit();
    }

    if (!in_array($permission, ['read', 'edit'])) {
        echo json_encode(["status" => "error", "message" => "Invalid permission"]);
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (!$res) {
        echo json_encode(["status" => "error", "message" => "Email not found"]);
        exit();
    }

    $shared_with = $res['id'];

    if ($shared_with == $owner_id) {
        echo json_encode(["status" => "error", "message" => "Cannot share to yourself"]);
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $note_id, $owner_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        echo json_encode(["status" => "error", "message" => "Unauthorized"]);
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM shared_notes WHERE note_id = ? AND shared_with = ?");
    $stmt->bind_param("ii", $note_id, $shared_with);
    $stmt->execute();

    if ($stmt->get_result()->fetch_assoc()) {
        $stmt = $conn->prepare("UPDATE shared_notes SET permission = ? WHERE note_id = ? AND shared_with = ?");
        $stmt->bind_param("sii", $permission, $note_id, $shared_with);
        $stmt->execute();
    } else {

        $stmt = $conn->prepare("INSERT INTO shared_notes (note_id, owner_id, shared_with, permission) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $note_id, $owner_id, $shared_with, $permission);
        $stmt->execute();
    }

    sendShareEmail($email, $note_id, $permission);

    echo json_encode([
        "status" => "success",
        "message" => "Note shared successfully"
    ]);
}