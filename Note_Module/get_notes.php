<?php
require_once("db.php");
require_once("response.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    response("error", "User not authenticated");
}

$stmt = $conn->prepare(
    "SELECT id, title, content, created_at
     FROM notes
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();

$result = $stmt->get_result();

$notes = [];

while ($row = $result->fetch_assoc()) {
    $notes[] = $row;
}

response("success", "Notes retrieved successfully", $notes);