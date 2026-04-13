<?php
session_start();
include __DIR__ . '/../db.php';
require __DIR__ . '/../Utils/email.php';

$error = "";
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));

    if ($email === '') {
        $error = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            $otp = str_pad(rand(0, 999999), 6, "0", STR_PAD_LEFT);
            $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

            $stmt = $conn->prepare("UPDATE users SET reset_otp = ?, otp_expires = ? WHERE email = ?");
            $stmt->bind_param("sss", $otp, $expires, $email);
            $stmt->execute();

            sendOtpEmail($email, $otp);
            header("Location: reset_password.php?email=" . urlencode($email));
            exit();
        } else {
            $error = "Email does not exist in our system";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
                <h1 class="card-title">Forgot Password</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" style="font-size: 12px; border-radius: 50px; padding: 10px 20px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <div class="input-wrapper">
                        <input type="email" name="email" placeholder="Enter your email here" class="input-field" required>
                    </div>

                    <button type="submit" class="btn-submit-login w-100">
                        Get Verification Code
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