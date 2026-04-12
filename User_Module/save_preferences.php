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
$fontSize = (int)($_POST['font_size'] ?? 16);
$fontStyle = $_POST['font_style'] ?? 'Sans-serif';

if ($fontSize < 12) $fontSize = 12;
if ($fontSize > 30) $fontSize = 30;

$stmt = $conn->prepare("
    UPDATE users 
    SET theme_mode=?, font_size=?, font_style=? 
    WHERE id=?
");

$stmt->bind_param("sisi", $theme, $fontSize, $fontStyle, $userId);
$stmt->execute();

header("Location: setting.php");
exit();