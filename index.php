<?php
session_start();

include __DIR__ . '/db.php';
include __DIR__ . '/Utils/preferences.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Auth_Module/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT display_name, is_verified, theme_mode, font_size, font_style
    FROM users
    WHERE id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$_SESSION["is_verified"] = $user["is_verified"] ?? 0;

$pref = [
    "theme_mode" => $user["theme_mode"] ?? "light",
    "font_size" => $user["font_size"] ?? 16,
    "font_style" => $user["font_style"] ?? "Arial"
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notes Test</title>

    <link rel="stylesheet" href="Assets/css/theme.css">
    <link rel="stylesheet" href="Assets/css/home.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <?php
        $myThemeColor = '#5385c7';
        if(isset($conn)) {
            $t_stmt = $conn->prepare("SELECT theme_color FROM users WHERE id = ?");
            $t_stmt->bind_param("i", $userId);
            $t_stmt->execute();
            $t_res = $t_stmt->get_result()->fetch_assoc();
            if($t_res) {
                $myThemeColor = $t_res['theme_color'] ?? '#5385c7';
            }
        }
        $t_colors = explode('|', $myThemeColor);
        $tc1 = $t_colors[0];
        $tc2 = $t_colors[1] ?? $tc1;
    ?>
</head>

<body class="<?= htmlspecialchars($pref['theme_mode'] ?? 'light') ?>">

<div class="app-container">

    <aside class="sidebar">

        <!-- TOP -->
        <div class="sidebar-top">
            <div class="sidebar-header">
                <h2 class="logo">
                    MindFlow
                </h2>
            </div>

            <div class="profile-box">
                <a href="User_Module/profile.php" class="profile-link">
                    <div class="profile-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <span class="profile-name">
                        <?= htmlspecialchars($user['display_name'] ?? 'User'); ?>
                    </span>
                </a>
            </div>

            <div class="verify-box">
                <?php if (isset($_SESSION["success_message"])): ?>
                    <div id="verify-message" class="success-banner">
                        <?php
                            echo htmlspecialchars($_SESSION["success_message"]);
                            unset($_SESSION["success_message"]);
                        ?>
                    </div>
                <?php elseif (!($_SESSION["is_verified"] ?? 0)): ?>
                    <div id="verify-message" class="warning-banner">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Your account is not activated.
                    </div>
                <?php endif; ?>
            </div>

            <div class="label-section">
                <h3>
                    <i class="bi bi-tags-fill"></i> Labels
                </h3>
                <?php include __DIR__ . '/Label_Module/labels.php'; ?>
            </div>
        </div>

        <!-- BOTTOM -->
        <div class="sidebar-bottom">
            <nav class="sidebar-nav">

                <a href="User_Module/setting.php">
                    <i class="bi bi-gear-fill"></i> Settings
                </a>

                <a href="Auth_Module/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>

            </nav>
        </div>

    </aside>

    <main class="main-content">

        <h1>
            Welcome, <?= htmlspecialchars($user['display_name'] ?? 'User'); ?> 👋
        </h1>

        <hr>

        <?php include __DIR__ . '/Note_Module/notes.php'; ?>
        <?php include __DIR__ . '/Note_Module/share_note.php'; ?>
        <?php include __DIR__ . '/Note_Module/shared_notes_view.php'; ?>

    </main>

</div>

<script>
    const NOTE_BASE = "Note_Module/";
    const USER_BASE = "User_Module/";
    const API_BASE = "API/";
</script>

<script src="Assets/js/app.js"></script>
<script src="Assets/js/notes.js"></script>
<script src="Assets/js/labels.js"></script>

<script>
window.addEventListener("storage", function (event) {
    if (event.key === "verify_success" && event.newValue === "1") {
        const banner = document.getElementById("verify-message");

        if (banner) {
            banner.className = "success-banner";
            banner.innerText = "Account verified successfully";
        } else {
            const newBanner = document.createElement("div");
            newBanner.id = "verify-message";
            newBanner.className = "success-banner";
            newBanner.innerText = "Account verified successfully";
            document.querySelector(".content").prepend(newBanner);
        }

        setTimeout(() => {
            const msg = document.getElementById("verify-message");
            if (msg) {
                msg.style.opacity = "0";
                setTimeout(() => msg.remove(), 500);
            }
        }, 5000);

        localStorage.removeItem("verify_success");
    }
});

window.addEventListener("load", function () {
    if (localStorage.getItem("verify_success") === "1") {
        window.dispatchEvent(new StorageEvent("storage", {
            key: "verify_success",
            newValue: "1"
        }));
    }
});
</script>

</body>
</html>
