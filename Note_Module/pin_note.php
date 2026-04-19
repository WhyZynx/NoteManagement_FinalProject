<?php
include("../db.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $result = $conn->query("SELECT is_pinned FROM notes WHERE id = $id");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($row['is_pinned'] == 1) {
            $conn->query("UPDATE notes 
                          SET is_pinned = 0, pinned_at = NULL 
                          WHERE id = $id");
        } else {
            $conn->query("UPDATE notes 
                          SET is_pinned = 1, pinned_at = NOW() 
                          WHERE id = $id");
        }
    }
}

header("Location: ../index.php");
exit();
?>