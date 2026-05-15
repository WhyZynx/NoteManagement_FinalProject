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
$avatar = !empty($user['avatar']) ? $user['avatar'] : '../Assets/images/avatar/default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | MindFlow</title>
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
                    <h2>Hello, <?= htmlspecialchars($user['display_name']) ?>!</h2>
                    <p>Update your profile information to keep your account personalized.</p>
                </div>
                <img src="../<?= $avatar ?>" alt="Avatar" class="avatar-wrapper">
            </div>

            <div class="details-section">
                <div class="section-header">
                    <h3>Edit Personal Details</h3>
                </div>
                
                <div class="edit-container-card">
                    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="edit-row">
                            <label class="edit-label">Name:</label>
                            <input type="text" name="display_name" class="edit-input-text"
                                value="<?= htmlspecialchars($user['display_name']) ?>" 
                                placeholder="Enter your name">
                        </div>

                        <div class="edit-row">
                            <label class="edit-label">Avatar:</label>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <label for="avatar-upload" class="custom-file-upload">
                                    select file
                                </label>
                                <input type="file" name="avatar" id="avatar-upload" style="display: none;">
                                
                                <span class="file-name-display">
                                    <?= $user['avatar'] ? basename($user['avatar']) : 'No files are available.' ?>
                                </span>
                            </div>
                        </div>

                        <div class="edit-actions">
                            <a href="profile.php" class="btn-cancel">Cancel</a>
                            <button type="submit" class="btn-confirm-save">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <img src="../Assets/images/web_img/ocean.png" alt="ocean" class="ocean-bg">
        </div>
    </div>

    <script src="../Assets/js/sidebar.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const el = document.querySelector(".file-name-display");
        if (!el) return;

        const full = el.textContent.trim();

        if (full.length > 12) {
            const first = full.slice(0, 5);
            const last = full.slice(-5);
            el.textContent = first + "..." + last;
        }

        el.title = full;
    });
</script>
</body>
</html>