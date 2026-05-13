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
    <link rel="stylesheet" href="Assets/css/label.css">
    <link rel="stylesheet" href="Assets/css/note.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="manifest" href="/NoteManagement_FinalProject/manifest.json">
    <meta name="theme-color" content="#6366f1">

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
    <style>
        :root {
            --user-color: <?= htmlspecialchars($tc1) ?>;
            --user-color-2: <?= htmlspecialchars($tc2) ?>;
        }
</style>
</head>

<body class="<?= htmlspecialchars($pref['theme_mode'] ?? 'light') ?>">

<div class="app-container">

    <aside class="sidebar">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="sidebar-top">

            <div class="sidebar-header">
                <h2 class="logo">
                    <span class="logo-bold">Mind</span>
                    <span class="logo-light">Flow</span>
                </h2>
            </div>

            <nav class="main-menu">
                <a href="?page=notes" class="menu-item <?= (!isset($_GET['page']) || $_GET['page'] === 'notes') ? 'active' : '' ?>">
                    <i class="bi bi-lightbulb-fill"></i>
                    <span>Notes</span>
                </a>

                <?php include __DIR__ . '/Note_Module/share_note.php'; ?>
                <?php include __DIR__ . '/Note_Module/shared_notes_view.php'; ?>
            </nav>

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
                <?php include __DIR__ . '/Label_Module/labels.php'; ?>
            </div>

        </div>

        <div class="sidebar-bottom">
            <nav class="sidebar-nav">
                <a href="#">
                    <i class="bi bi-trash"></i>
                    <span>Trash</span>
                </a>
                <a href="User_Module/setting.php">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
                <a href="Auth_Module/logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>
    </aside>

    <main class="main-content">
        <div class="content-area">

            <?php include __DIR__ . '/Note_Module/notes.php'; ?>

        </div>

    </main>

</div>

<script>
    const NOTE_BASE = "Note_Module/";
    const USER_BASE = "User_Module/";
    const API_BASE = "API/";
</script>

<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
<script src="Assets/js/app.js"></script>
<script src="Assets/js/notes.js"></script>
<script src="Assets/js/labels.js"></script>
<script src="Assets/js/offline.js"></script>

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

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (window.innerWidth > 768) {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    } else {
        sidebar.classList.toggle('active');
    }
}

document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.menu-toggle');
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    }
});
</script>

</body>
</html>
