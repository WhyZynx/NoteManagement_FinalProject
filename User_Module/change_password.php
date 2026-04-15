<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/preferences.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

$pref = getPreferences($conn, $_SESSION['user_id']);
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../Assets/css/theme.css">
</head>

<body class="<?= htmlspecialchars($pref['theme_mode']) ?>"
      style="font-size: <?= (int)$pref['font_size'] ?>px;
             font-family: <?= htmlspecialchars($pref['font_style']) ?>;">

<?php include "sidebar.php"; ?>

<div class="content">

    <h2>Change Password</h2>

    <?php if ($error): ?>
        <p style="color:red">
            <?php
            if ($error == 'wrong_old') echo "Wrong old password";
            if ($error == 'confirm') echo "Confirm password does not match";
            if ($error == 'same') echo "New password must be different from old password";
            if ($error == 'weak') echo "Password must be at least 8 characters and include uppercase, lowercase, and number";
            ?>
        </p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:green">Password changed successfully</p>
    <?php endif; ?>

    <form action="update_password.php" method="POST">
        <input type="password" name="old_password" placeholder="Old Password" required><br><br>
        <input type="password" name="new_password" placeholder="New Password" required><br><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br><br>

        <button type="submit">Change Password</button>
    </form>

    <br>

    <a href="profile.php">Back</a>

</div>

<script src="../Assets/js/sidebar.js"></script>
</body>
</html>