<?php

include __DIR__ . '/../db.php';

session_start();

if (!isset($_GET["token"])) {
    header("Location: ../index.php");
    exit();
}

$token = $_GET["token"];

$stmt = $conn->prepare("SELECT id FROM users WHERE verify_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    $update = $conn->prepare("
        UPDATE users 
        SET is_verified = 1,
            verify_token = NULL
        WHERE id = ?
    ");

    $update->bind_param("i", $user["id"]);
    $update->execute();

    $_SESSION["is_verified"] = 1;

    $_SESSION["success_message"] = "Account verified successfully";
}

header("Location: ../index.php");
exit();
?>