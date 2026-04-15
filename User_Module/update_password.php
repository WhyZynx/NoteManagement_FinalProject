<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/security.php';
include __DIR__ . '/../Utils/validation.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: change_password.php");
    exit();
}

$userId = $_SESSION['user_id'];

$old = $_POST['old_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

$stmt = $conn->prepare("SELECT password_hash FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !verifyPassword($old, $user['password_hash'])) {
    header("Location: change_password.php?error=wrong_old");
    exit();
}

if ($new !== $confirm) {
    header("Location: change_password.php?error=confirm");
    exit();
}

$strengthError = validatePasswordStrength($new);

if ($strengthError) {
    header("Location: change_password.php?error=weak");
    exit();
}

if (verifyPassword($new, $user['password_hash'])) {
    header("Location: change_password.php?error=same");
    exit();
}

$newHash = hashPassword($new);

$stmt = $conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
$stmt->bind_param("si", $newHash, $userId);
$stmt->execute();

header("Location: profile.php");
exit();