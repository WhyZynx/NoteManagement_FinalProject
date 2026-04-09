<?php
require_once("db.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$theme = $_POST["theme_mode"] ?? "light";
$font_size = intval($_POST["font_size"] ?? 16);

$stmt = $conn->prepare("
    UPDATE users
    SET theme_mode = ?, font_size = ?
    WHERE id = ?
");

$stmt->bind_param("sii", $theme, $font_size, $_SESSION["user_id"]);
$stmt->execute();

header("Location: setting.php");
exit();