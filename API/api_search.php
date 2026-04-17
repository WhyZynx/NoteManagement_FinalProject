<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
$currentDir = __DIR__;
$levelsUp = substr_count($currentDir, '/') - substr_count($docRoot, '/');
$basePath = str_repeat('../', $levelsUp);

require_once $basePath . 'db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$userId = $_SESSION['user_id'];
$keyword = trim($_GET['keyword'] ?? '');
$labelId = $_GET['label_id'] ?? null;

$searchValue = "%$keyword%";

$sql = "
SELECT DISTINCT n.*
FROM notes n
LEFT JOIN note_labels nl ON n.id = nl.note_id
WHERE n.user_id = ?
AND (n.title LIKE ? OR n.content LIKE ?)
";

if ($labelId) {
    $sql .= " AND nl.label_id = ? ";
}

$stmt = $conn->prepare($sql);

if ($labelId) {
    $stmt->bind_param("issi", $userId, $searchValue, $searchValue, $labelId);
} else {
    $stmt->bind_param("iss", $userId, $searchValue, $searchValue);
}

$stmt->execute();

$result = $stmt->get_result();

$notes = [];

while ($row = $result->fetch_assoc()) {
    $notes[] = $row;
}

header('Content-Type: application/json');
echo json_encode($notes);
?>