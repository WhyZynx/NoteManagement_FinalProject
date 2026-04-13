<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/response.php';
session_start();

if (!isset($_SESSION["user_id"])) {
    response("error", "User not authenticated");
}

$userId = $_SESSION["user_id"];

$stmt = $conn->prepare(" INSERT INTO notes (
        user_id,
        title,
        content,
        font_size,
        font_style,
        note_color,
        view_mode,
        created_at,
        updated_at
    ) VALUES (?, '', '', 16, 'Inter', '#ffffff', 'grid', NOW(), NOW())
    ");

$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    response("success", "Note created", [
        "note_id" => $conn->insert_id
    ]);
}

response("error", "Failed to create note");