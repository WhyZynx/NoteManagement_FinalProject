<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/validation.php';
include __DIR__ . '/../Utils/security.php';

$email = $_GET['email'] ?? '';
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    $error = validatePasswordStrength($password)
        ?? validateConfirmPassword($password, $confirm);

    if (!$error) {

        $hash = hashPassword($password);

        $stmt = $conn->prepare("UPDATE users SET password_hash = ?, reset_otp = NULL, otp_expires = NULL WHERE email = ?");
        $stmt->bind_param("ss", $hash, $email);
        $stmt->execute();

        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>

<h2>Reset Password</h2>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
    <input type="password" name="password" placeholder="New Password" required><br><br>
    <input type="password" name="confirm" placeholder="Confirm Password" required><br><br>
    <button type="submit">Reset</button>
</form>

</body>
</html>