<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Trang chủ</title></head>
<body>
    <?php if ($_SESSION['is_verified'] == 0): ?>
        <div style="background: #fff3cd; padding: 10px; border: 1px solid #ffeeba;">
            <strong>Lưu ý:</strong> Tài khoản chưa kích hoạt. Vui lòng check mail!
        </div>
    <?php endif; ?>

    <h1>Chào mừng, <?php echo $_SESSION['user_name']; ?>!</h1>
    <nav>
        <a href="profile.php">Hồ sơ cá nhân</a> | 
        <a href="settings.php">Cài đặt giao diện</a> | 
        <a href="logout.php">Đăng xuất</a>
    </nav>

    <hr>
    <h3>Danh sách ghi chú của bạn (Tuần 3 Quỳnh làm)</h3>
</body>
</html>