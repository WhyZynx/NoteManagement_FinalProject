<?php
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/security.php';

$email = $_GET['email'] ?? '';
$message = "";

if (isset($_POST['otp'])) {
    $otp = trim($_POST['otp']);
    $now = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("
        SELECT id
        FROM users
        WHERE email = ?
        AND reset_otp = ?
        AND otp_expires >= ?
    ");
    $stmt->bind_param("sss", $email, $otp, $now);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        header("Location: reset_password.php?email=" . urlencode($email));
        exit;
    } else {
        $message = "Invalid or expired OTP.";
    }
}
?>

<form method="POST">
    <input type="text" name="otp" maxlength="6" required>
    <button type="submit">Verify OTP</button>
</form>

<p><?php echo $message; ?></p>