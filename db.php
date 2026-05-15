<?php
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$dbname = getenv('DB_NAME');
$port = getenv('DB_PORT');

$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    die("Database connect failed " . mysqli_connect_error());
}

// Thiết lập font tiếng Việt chuẩn
mysqli_set_charset($conn, "utf8mb4");
?>