<?php
session_start();
include __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth Module/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$sql = "SELECT email, display_name, avatar, is_verified, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: ../Auth Module/login.php");
    exit();
}

$avatarPath = (!empty($user['avatar']))
    ? "../" . $user['avatar']
    : "../Assets/images/avatar/sbcf-default-avatar.png";
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
</head>
<body>

<h2>User Profile</h2>

<div>
    <img src="<?php echo $avatarPath; ?>" width="120" height="120" alt="Avatar">
</div>

<div>
    <p>Display Name: <?php echo$user['display_name']; ?></p>
    <p>Email: <?php echo $user['email']; ?></p>
    <p>Status: <?php echo $user['is_verified'] ? 'Verified' : 'Unverified'; ?></p>
    <p>Member Since: <?php echo $user['created_at']; ?></p>
</div>

<br>

<a href="../index.php">Back to Home</a>
<a href="setting.php">Edit Profile</a>
<a href="change_password.php">Password</a>

</body>
</html>