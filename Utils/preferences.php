<?php
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT theme_mode, font_size, font_style
    FROM users
    WHERE id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$preferences = $stmt->get_result()->fetch_assoc();

$themeMode = $preferences['theme_mode'] ?? 'light';
$fontSize = $preferences['font_size'] ?? 16;
$fontStyle = $preferences['font_style'] ?? 'Sans-serif';
?>