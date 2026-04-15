<?php
require_once __DIR__ . '/../db.php';

function setNoteLabels($conn, $noteId, $labelIds)
{
    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("DELETE FROM note_labels WHERE note_id = ?");
        $stmt->bind_param("i", $noteId);
        $stmt->execute();

        if (!empty($labelIds)) {
            $stmt = $conn->prepare("
                INSERT INTO note_labels (note_id, label_id)
                VALUES (?, ?)
            ");

            foreach ($labelIds as $id) {
                $id = intval($id);
                $stmt->bind_param("ii", $noteId, $id);
                $stmt->execute();
            }
        }

        $conn->commit();
        return true;

    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function getLabelsByNote($conn, $noteId)
{
    $stmt = $conn->prepare("
        SELECT l.id, l.label_name
        FROM labels l
        JOIN note_labels nl ON l.id = nl.label_id
        JOIN notes n ON n.id = nl.note_id
        WHERE nl.note_id = ? AND n.user_id = ?
    ");
    $stmt->bind_param("i", $noteId);
    $stmt->execute();

    return $stmt->get_result();
}