<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/response.php';
session_start();

if (!isset($_SESSION["user_id"])) {
    response("error", "User not authenticated");
}

$stmt = $conn->prepare("SELECT *
                        FROM notes
                        WHERE user_id = ?
                        ORDER BY updated_at DESC ");

$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();

$result = $stmt->get_result();

$notes = [];

while ($row = $result->fetch_assoc()) {
    $notes[] = $row;
}

response("success", "Notes retrieved successfully", $notes);