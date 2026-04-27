<?php
include("../db.php");

$note_id = $_POST['note_id'] ?? '';
$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if ($note_id == '' || $old_password == '' || $new_password == '') {
    echo "Missing data";
    exit();
}

$result = $conn->query("
    SELECT password 
    FROM note_passwords 
    WHERE note_id = $note_id
");

if ($result->num_rows == 0) {
    echo "Note not locked";
    exit();
}

$row = $result->fetch_assoc();

if (!password_verify($old_password, $row['password'])) {
    echo "Wrong password";
    exit();
}

$new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

$conn->query("
    UPDATE note_passwords 
    SET password = '$new_hashed' 
    WHERE note_id = $note_id
");

echo "Password changed successfully";
?>