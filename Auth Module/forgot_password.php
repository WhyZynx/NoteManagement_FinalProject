<?php
include __DIR__ . '/../db.php';
require __DIR__ . '/../Utils/email.php';

$message = "";
$error = "";

if (isset($_POST['email'])) {
    $email = trim(strtolower($_POST['email']));

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $otp = str_pad(rand(0, 999999), 6, "0", STR_PAD_LEFT);
        $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        $stmt = $conn->prepare("
            UPDATE users
            SET reset_otp = ?, otp_expires = ?
            WHERE email = ?
        ");
        $stmt->bind_param("sss", $otp, $expires, $email);
        $stmt->execute();

        if (sendOtpEmail($email, $otp)) {
            header("Location: verify_otp.php?email=" . urlencode($email));
            exit;
        } else {
            $error = "Failed to send OTP email.";
        }
    } else {
        $error = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>
    <div class="forgot-password-container">
        <h2>Forgot Password</h2>
        <p>Enter your email to receive an OTP code.</p>

        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <button type="submit">Send OTP</button>
        </form>

        <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if ($message): ?>
            <p class="success-message"><?php echo $message; ?></p>
        <?php endif; ?>

        <p>
            <a href="login.php">Back to Login</a>
        </p>
    </div>
</body>
</html>