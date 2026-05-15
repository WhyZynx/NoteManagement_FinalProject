<?php
$host = "db-mindflow-note-lytran041206-4f56.h.aivencloud.com";
$user = "avnadmin";
$pass = "AVNS_vYv4Dem09CeTiY1KT2-"; 
$dbname = "defaultdb";
$port = 24529;

$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    die("Database connect failed " . mysqli_connect_error());
}

// Thiết lập font tiếng Việt chuẩn
mysqli_set_charset($conn, "utf8mb4");
?>