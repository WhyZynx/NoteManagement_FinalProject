<?php
include("db.php");

$mockNotes = [
    ["id" => 1, "title" => "Ghi chú 1", "content" => "Nội dung 1"],
    ["id" => 2, "title" => "Ghi chú 2", "content" => "Nội dung 2"],
    ["id" => 3, "title" => "Ghi chú 3", "content" => "Nội dung 3"],
    ["id" => 4, "title" => "Ghi chú 4", "content" => "Nội dung 4"],
];

$statusMap = [];
$result = $conn->query("SELECT id, is_pinned, pinned_at FROM notes");

while ($row = $result->fetch_assoc()) {
    $statusMap[$row['id']] = $row;
}

foreach ($mockNotes as &$note) {
    if (isset($statusMap[$note['id']])) {
        $note['is_pinned'] = $statusMap[$note['id']]['is_pinned'];
        $note['pinned_at'] = $statusMap[$note['id']]['pinned_at'];
    } else {
        $note['is_pinned'] = 0;
        $note['pinned_at'] = null;
    }
}
unset($note);

usort($mockNotes, function ($a, $b) {
    if ($a['is_pinned'] != $b['is_pinned']) {
        return $b['is_pinned'] - $a['is_pinned'];
    }
    return strtotime($b['pinned_at'] ?? 0) - strtotime($a['pinned_at'] ?? 0);
});
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Pin (Mock)</title>
</head>
<body>

<h2>Test Pin Note (Mock 4 ghi chú)</h2>

<?php foreach ($mockNotes as $note) { ?>
    <div style="border:1px solid #000; margin:10px; padding:10px;">
        
        <b><?php echo $note['title']; ?></b><br>
        <?php echo $note['content']; ?><br><br>

        <b>
            <?php echo ($note['is_pinned']) ? "📌 Đã ghim" : "Chưa ghim"; ?>
        </b><br>

        <?php if ($note['pinned_at']) { ?>
            <small>Ghim lúc: <?php echo $note['pinned_at']; ?></small><br>
        <?php } ?>

        <br>

        <a href="Note_Module/pin_note.php?id=<?php echo $note['id']; ?>">
            <?php echo ($note['is_pinned']) ? "❌ Bỏ ghim" : "📌 Ghim"; ?>
        </a>

    </div>
<?php } ?>

</body>
</html>