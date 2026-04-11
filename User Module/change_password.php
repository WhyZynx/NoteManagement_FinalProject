<?php
require_once("../db.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Auth Module/login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $old = $_POST["old_password"] ?? "";
    $new = $_POST["new_password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if ($new !== $confirm) {
        $message = "New password and confirm password do not match";
    } else {

        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION["user_id"]);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !password_verify($old, $user["password_hash"])) {
            $message = "Old password is incorrect";
        } else {

            $new_hash = password_hash($new, PASSWORD_BCRYPT);

            $update = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $update->bind_param("si", $new_hash, $_SESSION["user_id"]);
            $update->execute();

            $message = "Password changed successfully";
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

<p><?php echo $message; ?></p>

<form method="POST">

    <label>Old Password</label><br>
    <input type="password" name="old_password" required>

    <br><br>

    <label>New Password</label><br>
    <input type="password" name="new_password" required>

    <br><br>

    <label>Confirm Password</label><br>
    <input type="password" name="confirm_password" required>

    <br><br>

    <button type="submit">Change Password</button>

</form>

<br>

<a href="profile.php">Back</a>

</body>
</html>