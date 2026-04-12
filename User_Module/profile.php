<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/preferences.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT email, display_name, avatar, is_verified, created_at
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$pref = getPreferences($conn, $userId);

$avatar = !empty($user['avatar']) ? $user['avatar'] : 'Assets/images/default.png';
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <link rel="stylesheet" href="../Assets/css/theme.css">
</head>

<body class="<?= $pref['theme_mode'] ?>"
      style="font-size: <?= $pref['font_size'] ?>px;
             font-family: <?= $pref['font_style'] ?>;">

<?php include "sidebar.php"; ?>

<div class="content">

    <h2>User Profile</h2>

    <div>
        <img src="../<?= htmlspecialchars($avatar) ?>" width="120" height="120">
    </div>

    <div>
        <p>Display Name: <?= htmlspecialchars($user['display_name']) ?></p>
        <p>Email: <?= htmlspecialchars($user['email']) ?></p>
        <p>Status: <?= $user['is_verified'] ? 'Verified' : 'Unverified' ?></p>
        <p>Member Since: <?= $user['created_at'] ?></p>
    </div>

    <br>

    <a href="../index.php">Back to home</a>

</div>

</body>
</html>