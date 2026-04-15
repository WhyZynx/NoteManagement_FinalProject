<?php
require_once __DIR__ . '/../db.php';

function getAllLabels($conn, $userId)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM labels
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    return $stmt->get_result();
}

function createLabel($conn, $userId, $labelName)
{
    $stmt = $conn->prepare("
        INSERT INTO labels (user_id, label_name)
        VALUES (?, ?)
    ");
    $stmt->bind_param("is", $userId, $labelName);

    return $stmt->execute();
}

function updateLabel($conn, $userId, $labelId, $newName)
{
    $stmt = $conn->prepare("
        UPDATE labels
        SET label_name = ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("sii", $newName, $labelId, $userId);

    return $stmt->execute();
}

function deleteLabel($conn, $userId, $labelId)
{
    $stmt = $conn->prepare("
        DELETE FROM labels
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $labelId, $userId);

    return $stmt->execute();
}