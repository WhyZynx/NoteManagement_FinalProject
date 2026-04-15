<?php
require_once __DIR__ . '/../db.php';

function setNoteLabels($conn, $noteId, $labelIds)
{
    $conn->prepare("DELETE FROM note_labels WHERE note_id = ?")
        ->bind_param("i", $noteId)
        ->execute();

    if (empty($labelIds)) return true;

    $stmt = $conn->prepare("
        INSERT INTO note_labels (note_id, label_id)
        VALUES (?, ?)
    ");

    foreach ($labelIds as $id) {
        $id = intval($id);
        $stmt->bind_param("ii", $noteId, $id);
        $stmt->execute();
    }

    return true;
}
function getLabelsByNote($conn, $noteId)
{
    $stmt = $conn->prepare("
        SELECT l.id, l.label_name
        FROM labels l
        JOIN note_labels nl ON l.id = nl.label_id
        WHERE nl.note_id = ?
    ");
    $stmt->bind_param("i", $noteId);
    $stmt->execute();

    return $stmt->get_result();
}