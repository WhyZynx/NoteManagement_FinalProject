<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/preferences.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$sql = "SELECT display_name, avatar, theme_mode, font_size, font_style FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <link rel="stylesheet" href="../Assets/css/theme.css">

</head>
<body class="<?= htmlspecialchars($user['theme_mode'] ?? 'light') ?>"
      style="font-size: <?= (int)($user['font_size'] ?? 16) ?>px;
             font-family: '<?= htmlspecialchars($user['font_style'] ?? 'Sans-serif') ?>', sans-serif;">
             
    <h2>Edit Profile</h2>

    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
        <label>Display Name</label><br>
        <input type="text" name="display_name" value="<?php echo htmlspecialchars($user['display_name']); ?>" required>

        <br><br>

        <label>Avatar</label><br>
        <input type="file" name="avatar">

        <br><br>

        <button type="submit">Update Profile</button>
    </form>

    <hr>

    <h2>User Preferences</h2>
    <form action="save_preferences.php" method="POST">
        <label>Theme</label><br>
        <select name="theme_mode">
            <option value="light" <?php echo $user['theme_mode'] == 'light' ? 'selected' : ''; ?>>Light</option>
            <option value="dark" <?php echo $user['theme_mode'] == 'dark' ? 'selected' : ''; ?>>Dark</option>
        </select>

        <br><br>

        <label>Font Size</label><br>
        <input type="number" name="font_size" min="12" max="30"
            value="<?php echo $user['font_size'] ?? 16; ?>">

        <br><br>

        <label>Font Style</label><br>
        <select name="font_style">
            <option value="Sans-serif" <?php echo ($user['font_style'] ?? '') == 'Sans-serif' ? 'selected' : ''; ?>>Sans-serif</option>
            <option value="Serif" <?php echo ($user['font_style'] ?? '') == 'Serif' ? 'selected' : ''; ?>>Serif</option>
            <option value="Monospace" <?php echo ($user['font_style'] ?? '') == 'Monospace' ? 'selected' : ''; ?>>Monospace</option>
        </select>
        <br><br>
        <button type="submit">Save Preferences</button>
    </form>
    <br>
    <a href="../index.php">Back</a>
</body>
</html>