<?php
require_once("db.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$display_name = trim($_POST["display_name"] ?? "");

if ($display_name === "") {
    echo "Display name cannot be empty";
    exit();
}

$stmt = $conn->prepare("UPDATE users SET display_name = ? WHERE id = ?");
$stmt->bind_param("si", $display_name, $_SESSION["user_id"]);
$stmt->execute();

header("Location: profile.php");
exit();