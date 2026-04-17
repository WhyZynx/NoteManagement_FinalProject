<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../Label_Module/label_controller.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$userId = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

switch ($action) {

    case 'list':
        $result = getAllLabels($conn, $userId);

        $labels = [];
        while ($row = $result->fetch_assoc()) {
            $labels[] = $row;
        }

        echo json_encode($labels);
        break;

    case 'create':
        $labelName = trim($_POST['label_name'] ?? '');

        if ($labelName === '') {
            echo json_encode([
                'success' => false,
                'message' => 'Label name required'
            ]);
            exit();
        }

        $success = createLabel($conn, $userId, $labelName);

        echo json_encode([
            'success' => $success
        ]);
        break;

    case 'update':
        $labelId = intval($_POST['label_id'] ?? 0);
        $newName = trim($_POST['new_name'] ?? '');

        $success = updateLabel($conn, $userId, $labelId, $newName);

        echo json_encode([
            'success' => $success
        ]);
        break;

    case 'delete':
    $labelId = intval($_POST['label_id'] ?? 0);

    $stmt = $conn->prepare("DELETE FROM note_labels WHERE label_id = ?");
    $stmt->bind_param("i", $labelId);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM labels WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $labelId, $userId);

    $success = $stmt->execute();

    echo json_encode([
        'success' => $success
    ]);
    exit();

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
}