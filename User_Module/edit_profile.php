<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/preferences.php';

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT display_name, avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$pref = getPreferences($conn, $userId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
</head>

<body class="<?= $pref['theme_mode'] ?>"
      style="font-size: <?= $pref['font_size'] ?>px;
             font-family: <?= $pref['font_style'] ?>;">

<?php include "sidebar.php"; ?>

<div class="content">

<h2>Edit Profile</h2>

<form action="update_profile.php" method="POST" enctype="multipart/form-data">

<input type="text" name="display_name"
       value="<?= $user['display_name'] ?>">

<br><br>

<input type="file" name="avatar">

<br><br>

<button type="submit">Save</button>

</form>

<br>
<a href="profile.php">Back</a>

</div>

</body>
</html>