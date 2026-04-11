<?php
session_start();
include __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth Module/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$sql = "SELECT display_name, avatar FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
</head>
<body>

<h2>Edit Profile</h2>

<form action="update_profile.php" method="POST" enctype="multipart/form-data">

    <label>Display Name</label><br>
    <input type="text" name="display_name" value="<?php echo htmlspecialchars($user['display_name']); ?>" required>

    <br><br>

    <label>Avatar</label><br>
    <input type="file" name="avatar">

    <br><br>

    <button type="submit">Update</button>

</form>

<br>

<a href="profile.php">Back</a>

</body>
</html>