<?php
include __DIR__ . '/../db.php';

session_start();

if (!isset($_GET["token"])) {
    echo "<script>window.close();</script>";
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

    if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] == $user["id"]) {
        $_SESSION["is_verified"] = 1;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Email</title>
</head>
<body>
<script>
localStorage.setItem("verify_success", "1");

if (window.opener) {
    window.close();
} else {
    window.location.href = "../index.php";
}
</script>
</body>
</html>