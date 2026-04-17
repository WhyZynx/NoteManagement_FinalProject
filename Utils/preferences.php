<?php
function getPreferences($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT theme_mode, theme_color, font_size, font_style
        FROM users
        WHERE id = ?
    ");

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $res = $stmt->get_result()->fetch_assoc();

    return [
        'theme_mode' => $res['theme_mode'] ?? 'light',
        'theme_color' => $res['theme_color'] ?? '#5385c7', 
        'font_size' => (int)($res['font_size'] ?? 16),
        'font_style' => $res['font_style'] ?? "'Inter', sans-serif"
    ];
}
?>