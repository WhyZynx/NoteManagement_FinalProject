<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "note_management"; 

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn , "utf8");
?>