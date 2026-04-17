<?php
session_start();
include __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => "Not logged in"
        ]);
        exit();
    }

    header("Location: ../Auth_Module/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: setting.php");
    exit();
}

$userId = $_SESSION['user_id'];

if (isset($_POST['key']) && isset($_POST['value'])) {
    $allowedKeys = ['theme_mode', 'theme_color', 'font_size', 'font_style', 'note_color', 'view_mode'];

    $key = $_POST['key'];
    $value = $_POST['value'];

    if (!in_array($key, $allowedKeys)) {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => "Invalid key"
        ]);
        exit();
    }

    if ($key === 'theme_mode') {
         if (!in_array($value, ['light', 'dark', 'hologram', 'custom', 'gradient'])) {
             $value = 'light';
         }
    }

    $sql = "UPDATE users SET $key = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $value, $userId);
    $stmt->execute();

    header('Content-Type: application/json');
    echo json_encode([
        "status" => "success"
    ]);
    exit();
}

$theme = $_POST['theme_mode'] ?? 'light';
$themeColor = $_POST['theme_color'] ?? '#5385c7'; 
$fontSize = (int)($_POST['font_size'] ?? 16);
$fontStyle = $_POST['font_style'] ?? "'Inter', sans-serif";

if (!in_array($theme, ['light', 'dark', 'hologram', 'custom', 'gradient'])) {
    $theme = 'light';
}

$stmt = $conn->prepare("
    UPDATE users 
    SET theme_mode=?, theme_color=?, font_size=?, font_style=? 
    WHERE id=?
");

$stmt->bind_param("ssisi", $theme, $themeColor, $fontSize, $fontStyle, $userId);
$stmt->execute();

header("Location: setting.php?success=1");
exit();
?>