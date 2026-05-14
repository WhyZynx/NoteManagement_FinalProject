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
$action = $_REQUEST['action'] ?? '';

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

    $stmt = $conn->prepare("SELECT permission FROM shared_notes WHERE note_id = ? AND shared_with = ?");
    $stmt->bind_param("ii", $note_id, $shared_with);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        if ($existing['permission'] === $permission) {
            echo json_encode(["status" => "error", "message" => "User already has this permission"]);
            exit();
        }
        $stmt = $conn->prepare("UPDATE shared_notes SET permission = ? WHERE note_id = ? AND shared_with = ?");
        $stmt->bind_param("sii", $permission, $note_id, $shared_with);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO shared_notes (note_id, owner_id, shared_with, permission) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $note_id, $owner_id, $shared_with, $permission);
        $stmt->execute();
    }

    try {
        sendShareEmail($email, $note_id, $permission);
    } catch(Exception $e) {
        error_log("Failed to send share email: " . $e->getMessage());
    }

    echo json_encode(["status" => "success", "message" => "Note shared successfully"]);
    exit();
}

if ($action === 'get_shared_users') {
    $note_id = isset($_GET['note_id']) ? intval($_GET['note_id']) : 0;
    
    if ($note_id > 0 && $owner_id > 0) {
        $stmt = $conn->prepare("SELECT u.email, s.permission FROM shared_notes s JOIN users u ON s.shared_with = u.id WHERE s.note_id = ? AND s.owner_id = ?");
        $stmt->bind_param("ii", $note_id, $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        echo json_encode(["status" => "success", "data" => $users]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request"]);
    }
    exit();
}

if ($action === 'revoke_share') {
    $note_id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if ($note_id > 0 && $email != '' && $owner_id > 0) {
        $stmtUser = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmtUser->bind_param("s", $email);
        $stmtUser->execute();
        $resUser = $stmtUser->get_result();
        
        if ($resUser->num_rows > 0) {
            $target_user_id = $resUser->fetch_assoc()['id'];
            
            $stmtDel = $conn->prepare("DELETE FROM shared_notes WHERE note_id = ? AND owner_id = ? AND shared_with = ?");
            $stmtDel->bind_param("iii", $note_id, $owner_id, $target_user_id);
            
            if ($stmtDel->execute()) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Database error"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "User not found"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid parameters"]);
    }
    exit();
}
?>