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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../Assets/css/theme.css">
</head>

<body class="<?= htmlspecialchars($pref['theme_mode']) ?>"
      style="font-size: <?= (int)$pref['font_size'] ?>px;
             font-family: <?= htmlspecialchars($pref['font_style']) ?>;">

<div class="main-wrapper">
    <button class="menu-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <?php include "sidebar.php"; ?>

    <div class="content">
        <h1 class="page-title">Security</h1>

        <div class="password-card">
            <div class="password-form-side">
                <h2 class="password-title">Update Password</h2>

                <form action="update_password.php" method="POST">
                    <div class="password-input-group">
                        <label class="password-input-label">Old Password</label>
                        <input type="password" name="old_password" class="edit-input-text" placeholder="••••••••" required style="width: 100%;">
                        <i class="fa-regular fa-eye-slash toggle-password" style="cursor: pointer;"></i>
                    </div>

                    <div class="password-input-group">
                        <label class="password-input-label">New Password</label>
                        <input type="password" name="new_password" class="edit-input-text" placeholder="Enter new password" required style="width: 100%;">
                        <i class="fa-regular fa-eye-slash toggle-password" style="cursor: pointer;"></i>
                    </div>

                    <div class="password-input-group">
                        <label class="password-input-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="edit-input-text" placeholder="Repeat new password" required style="width: 100%;">
                        <i class="fa-regular fa-eye-slash toggle-password" style="cursor: pointer;"></i>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="password-alert password-alert-error">
                            <i class="fas fa-exclamation-circle"></i> 
                            <?php
                                if ($error == 'wrong_old') echo "Old password is incorrect";
                                elseif ($error == 'confirm') echo "Confirm password doesn't match";
                                elseif ($error == 'same') echo "Must be a new password";
                                elseif ($error == 'weak') echo "Password is too simple";
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="password-alert password-alert-success">
                            <i class="fas fa-check-circle"></i> Password changed successfully!
                        </div>
                    <?php endif; ?>

                    <div class="password-footer">
                        <button type="submit" class="password-btn-submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        <img src="../Assets/images/web_img/ocean.png" alt="ocean" class="ocean-bg">
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-password').forEach(function(icon) {
        icon.addEventListener('click', function() {
            let input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            }
        });
    });
</script>
<script src="../Assets/js/sidebar.js"></script>

</body>
</html>