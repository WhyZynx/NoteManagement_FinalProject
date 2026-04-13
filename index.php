<?php
session_start();
include __DIR__ . '/db.php';
include __DIR__ . '/Utils/preferences.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Auth_Module/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT display_name, is_verified, theme_mode, font_size, font_style
                          FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
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
    </style>
</head>

<body class="<?= $pref['theme_mode'] ?? 'light' ?>">
<div class="content">

    <?php if (isset($_SESSION["success_message"])): ?>
        <div class="alert-success">
            <?= htmlspecialchars($_SESSION["success_message"]); ?>
        </div>
        <?php unset($_SESSION["success_message"]); ?>
    <?php endif; ?>

    <?php
    $isVerified = (int)($user['is_verified'] ?? 0);
    ?>

    <?php if ($isVerified === 0): ?>
        <div class="alert-warning">
            Your account is not activated yet. Please check your email to verify.
        </div>
    <?php endif; ?>

    <h1>Welcome, <?= htmlspecialchars($user['display_name'] ?? 'User'); ?> 👋</h1>

    <nav>
        <a href="User_Module/profile.php">Profile</a> |
        <a href="User_Module/setting.php">Settings</a> |
        <a href="Auth_Module/logout.php">Logout</a>
    </nav>

    <hr>

    <h3>Your Notes List</h3>

    <hr>

    <button onclick="createNoteCard()">Add Note</button>
    <button onclick="setViewMode('grid')">Grid View</button>
    <button onclick="setViewMode('list')">List View</button>

    <hr>

    <div id="notes-list" class="grid"></div>

    <script src="Assets/js/notes.js"></script>
</body>
</html>