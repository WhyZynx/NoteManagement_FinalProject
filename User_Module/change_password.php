<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/validation.php';
include __DIR__ . '/../Utils/security.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($old === '' || $new === '' || $confirm === '') {
        $error = "Please fill in all fields";
    } else {

        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !verifyPassword($old, $user['password_hash'])) {
            $error = "Old password is incorrect";
        } else {

            $error = validatePasswordStrength($new)
                ?? validateConfirmPassword($new, $confirm);

            if (!$error && verifyPassword($new, $user['password_hash'])) {
                $error = "New password must be different";
            }

            if (!$error) {
                $newHash = hashPassword($new);

                $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->bind_param("si", $newHash, $userId);
                $stmt->execute();

                header("Location: profile.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
</head>
<body>

<h2>Change Password</h2>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
    <input type="password" name="old_password" placeholder="Old Password" required><br><br>
    <input type="password" name="new_password" placeholder="New Password" required><br><br>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required><br><br>
    <button type="submit">Change</button>
</form>

</body>
</html>