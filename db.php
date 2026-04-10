<?php
session_start();

$host = "localhost";
$dbname = "note_management";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed");
}
?>