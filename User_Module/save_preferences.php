<?php
session_start();
require_once("../db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Auth_Module/login.php");
    exit();
}

$theme = $_POST["theme_mode"] ?? "light";
$font_size = intval($_POST["font_size"] ?? 16);
$font_style = $_POST["font_style"] ?? "Sans-serif";

$stmt = $conn->prepare("
    UPDATE users
    SET theme_mode = ?, font_size = ?, font_style = ?
    WHERE id = ?
");

$stmt->bind_param("sisi", $theme, $font_size, $font_style, $_SESSION["user_id"]);
$stmt->execute();

$_SESSION["theme_mode"] = $theme;
$_SESSION["font_size"] = $font_size;
$_SESSION["font_style"] = $font_style;

header("Location: setting.php");
exit();