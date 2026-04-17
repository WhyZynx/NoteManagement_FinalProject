<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
$currentDir = __DIR__;
$levelsUp = substr_count($currentDir, '/') - substr_count($docRoot, '/');
$basePath = str_repeat('../', $levelsUp);

require_once $basePath . 'db.php';                                    
require_once $basePath . 'Note_Module/note_label_controller.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'set') {

    $noteId = intval($_POST['note_id'] ?? 0);
    $labelIds = $_POST['labels'] ?? [];

    if (!is_array($labelIds)) {
        $labelIds = json_decode($labelIds, true);
    }

    if (!is_array($labelIds)) {
        $labelIds = [];
    }

    setNoteLabels($conn, $noteId, $labelIds);

    echo json_encode([
        "success" => true,
        "noteId" => $noteId,
        "labels" => $labelIds
    ]);
    exit();
}

echo json_encode(['success' => false]);
exit();