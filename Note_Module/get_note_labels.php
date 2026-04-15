<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/response.php';
session_start();

$userId = $_SESSION["user_id"];
$noteId = (int)($_GET["note_id"] ?? 0);

$stmt = $conn->prepare("
    SELECT l.id, l.label_name
    FROM labels l
    JOIN note_labels nl ON l.id = nl.label_id
    WHERE nl.note_id = ? AND l.user_id = ?
");

$stmt->bind_param("ii", $noteId, $userId);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(["status" => "success", "data" => $data]);