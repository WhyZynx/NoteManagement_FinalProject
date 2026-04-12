<?php
session_start();
include __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$sql = "SELECT avatar FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

$displayName = trim($_POST['display_name']);
$avatarPath = $user['avatar'];

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {

    $fileName = time() . '_' . basename($_FILES['avatar']['name']);
    $uploadDir = __DIR__ . '/../uploads/avatars/';
    $uploadPath = $uploadDir . $fileName;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath);

    $avatarPath = 'uploads/avatars/' . $fileName;
}

$sql = "UPDATE users SET display_name = ?, avatar = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $displayName, $avatarPath, $userId);
$stmt->execute();

header("Location: profile.php");
exit();