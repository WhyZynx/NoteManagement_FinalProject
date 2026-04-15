<?php
require_once __DIR__ . '/../db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$userId = $_SESSION['user_id'];
$keyword = trim($_GET['keyword'] ?? '');

$sql = "SELECT * FROM notes 
        WHERE user_id = ? 
        AND (title LIKE ? OR content LIKE ?)
        ORDER BY updated_at DESC";

$searchValue = "%$keyword%";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $userId, $searchValue, $searchValue);
$stmt->execute();

$result = $stmt->get_result();

$notes = [];

while ($row = $result->fetch_assoc()) {
    $notes[] = $row;
}

header('Content-Type: application/json');
echo json_encode($notes);
?>