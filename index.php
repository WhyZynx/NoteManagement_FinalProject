<?php
session_start();
include __DIR__ . '/db.php';
include __DIR__ . '/Utils/preferences.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Auth_Module/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

/* Load user data (single source of truth = DB) */
$stmt = $conn->prepare("
    SELECT display_name, is_verified
    FROM users
    WHERE id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* Load preferences */
$pref = getPreferences($conn, $userId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="Assets/css/theme.css">
</head>

<body class="<?= $pref['theme_mode'] ?>"
      style="font-size: <?= $pref['font_size'] ?>px;
             font-family: <?= $pref['font_style'] ?>;">

<div class="content">
    <?php if (isset($_SESSION["success_message"])): ?>
        <div class="alert-success">
            <?= htmlspecialchars($_SESSION["success_message"]); ?>
        </div>
        <?php unset($_SESSION["success_message"]); ?>
    <?php endif; ?>

    <?php if (!empty($user['is_verified']) && $user['is_verified'] == 0): ?>
        <div class="alert-warning">
            Your account is not activated yet. Please check your email to verify.
        </div>
    <?php endif; ?>
    
    <h1>Welcome, <?= htmlspecialchars($user['display_name']); ?> 👋</h1>

    <nav>
        <a href="User_Module/profile.php">Profile</a> |
        <a href="User_Module/setting.php">Settings</a> |
        <a href="Auth_Module/logout.php">Logout</a>
    </nav>

    <hr>

    <h3>Your Notes List</h3>

</div>

</body>
</html>