<?php
session_start();
include __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: profile.php");
    exit();
}

$userId = $_SESSION['user_id'];
$name = trim($_POST['display_name'] ?? '');

$stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$avatar = $user['avatar'];

if (!empty($_FILES['avatar']['name'])) {

    $file = time() . '_' . basename($_FILES['avatar']['name']);
    $path = __DIR__ . '/../uploads/avatars/';

    if (!is_dir($path)) mkdir($path, 0777, true);

    move_uploaded_file($_FILES['avatar']['tmp_name'], $path . $file);

    $avatar = 'uploads/avatars/' . $file;
}

$stmt = $conn->prepare("UPDATE users SET display_name=?, avatar=? WHERE id=?");
$stmt->bind_param("ssi", $name, $avatar, $userId);
$stmt->execute();

header("Location: profile.php");
exit();