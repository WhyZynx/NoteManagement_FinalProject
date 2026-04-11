<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
$servername = "localhost";
$db_username = "root";   
$db_password = "";        
$dbname = "note_management";   

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
 $conn->set_charset("utf8");
 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>