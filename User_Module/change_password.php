<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/preferences.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

$pref = getPreferences($conn, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../Assets/css/theme.css">
</head>

<body class="<?= $pref['theme_mode'] ?>"
      style="font-size: <?= $pref['font_size'] ?>px;
             font-family: <?= $pref['font_style'] ?>;">

<?php include "sidebar.php"; ?>

<div class="content">

<h2>Change Password</h2>

<?php if (isset($_GET['error'])): ?>
    <p style="color:red">
        <?php
        if ($_GET['error'] == 'wrong_old') echo "Wrong old password";
        if ($_GET['error'] == 'confirm') echo "Confirm not match";
        if ($_GET['error'] == 'same') echo "New password must be different";
        if ($_GET['error'] == 'weak') echo "Weak password";
        ?>
    </p>
<?php endif; ?>

<form action="update_password.php" method="POST">

    <input type="password" name="old_password" placeholder="Old Password"><br><br>
    <input type="password" name="new_password" placeholder="New Password"><br><br>
    <input type="password" name="confirm_password" placeholder="Confirm Password"><br><br>

    <button type="submit">Change Password</button>
</form>

<br>

<a href="profile.php">Back</a>

</div>
<script src="../Assets/js/sidebar.js"></script>
</body>
</html>