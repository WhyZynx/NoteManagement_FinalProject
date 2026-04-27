<?php
include("../db.php");

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {

    $user_id = $_GET['user_id'] ?? '';
    $last_sync = $_GET['last_sync'] ?? '';

    if ($user_id == '') {
        echo json_encode(["error" => "Missing user_id"]);
        exit();
    }

    if ($last_sync == '') {
        $sql = "SELECT * FROM notes WHERE user_id = $user_id";
    } else {
        $sql = "
            SELECT * FROM notes 
            WHERE user_id = $user_id 
              AND updated_at > '$last_sync'
        ";
    }

    $sql .= " ORDER BY is_pinned DESC, pinned_at DESC, updated_at DESC";

    $result = $conn->query($sql);

    $notes = [];
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }

    echo json_encode([
        "type" => "pull",
        "data" => $notes
    ]);
}

if ($method == "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["error" => "No data"]);
        exit();
    }

    foreach ($data as $note) {

        $id = $note['id'];
        $title = $note['title'];
        $content = $note['content'];
        $updated_at = $note['updated_at'];

        $check = $conn->query("SELECT updated_at FROM notes WHERE id = $id");

        if ($check->num_rows > 0) {
            $row = $check->fetch_assoc();

            if ($updated_at > $row['updated_at']) {
                $conn->query("
                    UPDATE notes 
                    SET title='$title', content='$content', updated_at='$updated_at'
                    WHERE id=$id
                ");
            }
        } else {
            $conn->query("
                INSERT INTO notes (id, title, content, updated_at)
                VALUES ($id, '$title', '$content', '$updated_at')
            ");
        }
    }

    echo json_encode([
        "type" => "push",
        "status" => "success"
    ]);
}
?>