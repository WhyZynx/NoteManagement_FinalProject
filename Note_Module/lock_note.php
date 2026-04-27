<?php
include("../db.php");

$note_id = $_POST['note_id'] ?? '';
$password = $_POST['password'] ?? '';

if ($note_id == '' || $password == '') {
    echo "Missing data";
    exit();
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$result = $conn->query("SELECT id FROM note_passwords WHERE note_id = $note_id");

if ($result->num_rows > 0) {
    $conn->query("
        UPDATE note_passwords 
        SET password = '$hashed' 
        WHERE note_id = $note_id
    ");
} else {
    $conn->query("
        INSERT INTO note_passwords (note_id, password) 
        VALUES ($note_id, '$hashed')
    ");
}

echo "Locked successfully";
?>