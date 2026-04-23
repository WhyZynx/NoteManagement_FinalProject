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
    <link rel="stylesheet" href="Assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

    <style>
        #notes-list.grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        #notes-list.list {
            display: block;
        }

        .note-card {
            padding: 16px;
            border: 1px solid #ddd;
            border-radius: 12px;
        }

        .note-toolbar {
            margin-bottom: 8px;
        }

        textarea {
            width: 100%;
            min-height: 120px;
        }

        .warning-banner,
        .success-banner {
            margin: 16px 0;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 600;
            transition: opacity 0.5s ease;
        }

        .warning-banner {
            background: #fff3cd;
            color: #856404;
        }

        .success-banner {
            background: #d4edda;
            color: #155724;
        }

        .toolbar {
            margin: 12px 0;
        }

        #searchInput {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
    </style>

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


        body.hologram, body.hologram .content {
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%) !important;
            background-attachment: fixed !important;
        }


        body.custom, body.custom .content {
            background-color: color-mix(in srgb, var(--user-color) 12%, #ffffff) !important;
            background-image: none !important;
            min-height: 100vh;
        }
        body.custom h1, body.custom h2, body.custom h3 {
            color: color-mix(in srgb, var(--user-color) 60%, #333333) !important;
        }
        body.custom a {
            color: var(--user-color) !important;
        }

 
        body.gradient, body.gradient .content {
            background: linear-gradient(135deg, var(--user-color) 0%, var(--user-color-2) 100%) !important;
            background-attachment: fixed !important;
        }


        body.dark, body.dark .content {
            background: #1e2233 !important;
            color: #ffffff !important;
        }
        body.dark h1, body.dark h3, body.dark span {
            color: #ffffff !important;
        }
        body.dark .note-card {
            background-color: #272d40 !important;
            border-color: #3f4760 !important;
        }
        body.dark textarea, body.dark input {
            background-color: #1e2233 !important;
            color: #ffffff !important;
            border-color: #3f4760 !important;
        }
        body.dark a {
            color: #3b82f6 !important;
        }
        body.dark .success-banner {
            background: #1f3b2b !important;
            color: #75b798 !important;
        }
        body.dark .warning-banner {
            background: #403417 !important;
            color: #ffda6a !important;
        }
    </style>
</head>

<body class="<?= htmlspecialchars($pref['theme_mode'] ?? 'light') ?>">

<div class="content">

    <?php if (isset($_SESSION["success_message"])): ?>
        <div id="verify-message" class="success-banner">
            <?php
                echo htmlspecialchars($_SESSION["success_message"]);
                unset($_SESSION["success_message"]);
            ?>
        </div>
    <?php elseif (!($_SESSION["is_verified"] ?? 0)): ?>
        <div id="verify-message" class="warning-banner">
            Your account is not activated. Please verify your email.
        </div>
    <?php endif; ?>

    <h1>
        Welcome, <?= htmlspecialchars($user['display_name'] ?? 'User'); ?> 👋
    </h1>

    <nav>
        <a href="User_Module/profile.php">Profile</a> |
        <a href="User_Module/setting.php">Settings</a> |
        <a href="Auth_Module/logout.php">Logout</a>
    </nav>

    <hr>

    <?php include __DIR__ . '/Note_Module/notes.php'; ?>
    <?php include __DIR__ . '/Label_Module/labels.php'; ?>
    <?php include __DIR__ . '/Note_Module/share_note.php'; ?>
    <?php include __DIR__ . '/Note_Module/shared_notes_view.php'; ?>
</div>

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
