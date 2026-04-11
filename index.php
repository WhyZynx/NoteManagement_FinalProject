<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: Auth Module/login.php");
    exit();
}

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
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="Assets/css/theme.css">
</head>
<body
    class="<?= $themeMode ?>"
    style="font-size: <?= $fontSize ?>px; font-family: <?= $fontStyle ?>;"
>

    <?php if (isset($_SESSION["success_message"])): ?>
        <div class="alert-success">
            <?= htmlspecialchars($_SESSION["success_message"]); ?>
        </div>
        <?php unset($_SESSION["success_message"]); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION["is_verified"]) && $_SESSION["is_verified"] == 0): ?>
        <div class="alert-warning">
            Your account is not activated yet. Please check your email to verify.
        </div>
    <?php endif; ?>

    <h1>Welcome, <?= htmlspecialchars($_SESSION['display_name']); ?>!</h1>

    <nav>
        <a href="User Module/profile.php">View Profile</a> |
        <a href="User Module/setting.php">Settings</a> |
        <a href="Auth Module/logout.php">Logout</a>
    </nav>

    <hr>
    <h3>Your Notes List</h3>

</body>
</html>