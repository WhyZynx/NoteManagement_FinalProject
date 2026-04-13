<?php
session_start();
include __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: setting.php");
    exit();
}

$userId = $_SESSION['user_id'];
$theme = $_POST['theme_mode'] ?? 'light';

if (!in_array($theme, ['light', 'dark'])) {
    $theme = 'light';
}

$stmt = $conn->prepare("
    UPDATE users
    SET theme_mode = ?
    WHERE id = ?
");

$stmt->bind_param("si", $theme, $userId);
$stmt->execute();

header("Location: setting.php?success=1");
exit();