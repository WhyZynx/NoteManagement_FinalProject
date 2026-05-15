<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$dbname = getenv('DB_NAME');
$port = getenv('DB_PORT');

$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    die("Database connect failed " . mysqli_connect_error());
}
$conn->query("SET time_zone = '+07:00'");
mysqli_set_charset($conn, "utf8mb4");
?>