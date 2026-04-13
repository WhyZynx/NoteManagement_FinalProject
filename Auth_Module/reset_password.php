<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/validation.php';
include __DIR__ . '/../Utils/security.php';
require __DIR__ . '/../Utils/email.php';

$email_param = $_GET['email'] ?? ($_POST['email'] ?? '');
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');

    if (isset($_POST['resend'])) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->fetch_assoc()) {
            $otp = str_pad(rand(0, 999999), 6, "0", STR_PAD_LEFT);
            $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

            $update = $conn->prepare("UPDATE users SET reset_otp = ?, otp_expires = ? WHERE email = ?");
            $update->bind_param("sss", $otp, $expires, $email);
            $update->execute();

            sendOtpEmail($email, $otp);
            $success = "A new verification code has been sent to your email";
        } else {
            $error = "Email does not exist in our system";
        }
    } else {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        $otp = trim($_POST['otp'] ?? '');
        $now = date("Y-m-d H:i:s");

        $error = validatePasswordStrength($password)
            ?? validateConfirmPassword($password, $confirm);

        if (!$error) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND reset_otp = ? AND otp_expires >= ?");
            $stmt->bind_param("sss", $email, $otp, $now);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->fetch_assoc()) {
                $hash = hashPassword($password);
                $update = $conn->prepare("UPDATE users SET password_hash = ?, reset_otp = NULL, otp_expires = NULL WHERE email = ?");
                $update->bind_param("ss", $hash, $email);
                $update->execute();

                header("Location: login.php?message=reset_success");
                exit();
            } else {
                $error = "Invalid or expired verification code";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../Assets/css/style.css">
</head>
<body class="min-vh-100 d-flex flex-column">
    <nav class="custom-navbar navbar navbar-expand-lg">
        <button class="btn p-1" onclick="history.back()">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
    </nav>

    <main class="main-content">
        <div class="content-wrapper">
            <div class="login-card">
                <h1 class="card-title">Reset Password</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" style="font-size: 12px; border-radius: 50px; padding: 10px 20px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" style="font-size: 12px; border-radius: 50px; padding: 10px 20px;">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email_param) ?>">

                    <div class="input-wrapper">
                        <input type="password" name="password" placeholder="New password" class="input-field" required>
                    </div>

                    <div class="input-wrapper">
                        <input type="password" name="confirm" placeholder="Confirm password" class="input-field" required>
                    </div>

                    <div class="input-wrapper">
                        <div class="input-group-custom">
                            <input type="text" name="otp" placeholder="Verification code" class="input-field" required>
                            <button type="submit" name="resend" value="1" class="forget-link" formnovalidate style="background: none; border: none; position: absolute; right: 20px; top: 50%; transform: translateY(-50%); padding: 0; cursor: pointer; outline: none;">Resend Code</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-login w-100" style="margin-top: 10px;">
                        Reset Password
                    </button>

                    <p class="signup-prompt text-center w-100">
                        <a href="login.php">Back to Login</a>
                    </p>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer-bg">
        <div class="container">
            <h5>Support</h5>
            <ul class="list-unstyled">
                <li><a href="#">Help</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
        </div>
    </footer>
</body>
</html>