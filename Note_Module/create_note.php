<?php
require_once("db.php");
require_once("response.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    response("error", "User not authenticated");
}

$title = trim($_POST["title"] ?? "");
$content = trim($_POST["content"] ?? "");

if ($title === "" || $content === "") {
    response("error", "Title and content are required");
}

$stmt = $conn->prepare(
    "INSERT INTO notes (user_id, title, content, created_at)
     VALUES (?, ?, ?, NOW())"
);

$stmt->bind_param("iss", $_SESSION["user_id"], $title, $content);

if ($stmt->execute()) {
    response("success", "Note created successfully");
} else {
    response("error", "Failed to create note");
}