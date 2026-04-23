<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Not logged in"
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT 
            n.id,
            n.title,
            n.content,
            n.font_size,
            n.font_style,
            n.note_color,
            n.created_at,
            n.updated_at,
            s.permission,
            s.created_at AS shared_at,
            u.email AS owner_email
        FROM shared_notes s
        JOIN notes n ON s.note_id = n.id
        JOIN users u ON s.owner_id = u.id
        WHERE s.shared_with = ?
        ORDER BY s.created_at DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Query prepare failed"
    ]);
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$notes = [];

while ($row = $result->fetch_assoc()) {
    $notes[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $notes
]);