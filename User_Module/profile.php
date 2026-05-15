<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/preferences.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT email, display_name, avatar, is_verified, created_at
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$pref = getPreferences($conn, $userId);


// Nếu avatar rỗng HOẶC chứa chữ "default.png" thì ép nó về đường dẫn Assets
if (empty($user['avatar']) || strpos($user['avatar'], 'default.png') !== false) {
    $avatar = 'Assets/images/avatar/default.png';
} else {
    $avatar = $user['avatar'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>User Information | MindFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Assets/css/theme.css">
</head>

<body class="<?= $pref['theme_mode'] ?>" style="font-size: <?= $pref['font_size'] ?>px; font-family: <?= $pref['font_style'] ?>;">

    <div class="main-wrapper">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>

        <?php include "sidebar.php"; ?>

        <div class="content">
            <h1 class="page-title">User Information</h1>

            <div class="welcome-card">
                <div class="welcome-text">
                    <h2>Hi, <?= htmlspecialchars($user['display_name']) ?>!</h2>
                    <p>Capture the essence of your ideas. Seamlessly organized, naturally mindful.</p>
                </div>
                <img src="../<?= $avatar ?>" alt="Avatar" class="avatar-wrapper">
            </div>

            <div class="details-section">
                <div class="section-header">
                    <h3>Personal Details</h3>
                    <a href="edit_profile.php" class="edit-link">Edit Info</a>
                </div>

                <div class="info-card">
                    <div class="info-group">
                        <label>Name:</label>
                        <span><?= htmlspecialchars($user['display_name']) ?></span>
                    </div>
                    <div class="info-group">
                        <label>Email:</label>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="info-group">
                        <label>Status:</label>
                        <span class="status-tag"><?= $user['is_verified'] ? 'Verified' : 'Unverified' ?></span>
                    </div>
                    <div class="info-group">
                        <label>Member Since:</label>
                        <span><?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
            </div>
            <img src="../Assets/images/web_img/ocean.png" alt="ocean" class="ocean-bg">
        </div>
    </div>

    <script src="../Assets/js/sidebar.js"></script>
</body>

</html>