<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Auth Module/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>

    <?php if (isset($_SESSION["success_message"])): ?>
        <div class="alert alert-success text-center">
            <?= $_SESSION["success_message"]; ?>
        </div>
        <?php unset($_SESSION["success_message"]); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION["is_verified"]) && $_SESSION["is_verified"] == 0): ?>
        <div class="alert alert-warning text-center">
            Your account is not activated yet. Please check your email to verify.
        </div>
    <?php endif; ?>

    <h1>Welcome, <?= $_SESSION['user_name']; ?>!</h1>

    <nav>
        <a href="profile.php">Profile</a> |
        <a href="settings.php">Settings</a> |
        <a href="Auth Module/logout.php">Logout</a>
    </nav>

    <hr>
    <h3>Your Notes List</h3>

</body>
</html>