<?php
include __DIR__ . '/../db.php';

$message = "";
$error = "";

$email = trim(strtolower($_GET['email'] ?? ""));

if (empty($email)) {
    die("Invalid request.");
}

$stmt = $conn->prepare("SELECT password_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$user = $result->fetch_assoc()) {
    die("User not found.");
}

$current_hash = $user["password_hash"];

if (isset($_POST['password'])) {
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (password_verify($password, $current_hash)) {
        $error = "New password must be different from your old password.";
    } else {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("
            UPDATE users
            SET password_hash = ?, reset_otp = NULL, otp_expires = NULL
            WHERE email = ?
        ");
        $stmt->bind_param("ss", $password_hash, $email);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit;
        } else {
            $error = "Failed to reset password.";
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
</head>
<body>
    <div class="reset-password-container">
        <h2>Reset Password</h2>
        <p>Enter your new password below.</p>

        <form method="POST">
            <div class="form-group">
                <input type="password" name="password" placeholder="New password" required>
            </div>

            <div class="form-group">
                <input type="password" name="confirm" placeholder="Confirm new password" required>
            </div>

            <button type="submit">Reset Password</button>
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