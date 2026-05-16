<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

$host = getenv('DB_HOST') ?: 'localhost'; 
$user = getenv('DB_USER') ?: 'root';      
$pass = getenv('DB_PASS') ?: ''; 
$dbname = getenv('DB_NAME') ?: 'mindflow_db';
$port = getenv('DB_PORT') ?: '3306';

$max_tries = 5;
$attempts = 0;
$conn = false;

while ($attempts < $max_tries) {
    $conn = @mysqli_connect($host, $user, $pass, $dbname, $port);
    if ($conn) {
        break;
    }
    $attempts++;
    sleep(2);
}

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

$conn->query("SET time_zone = '+07:00'");
mysqli_set_charset($conn, "utf8mb4");
?>