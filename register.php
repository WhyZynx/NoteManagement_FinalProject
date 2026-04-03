<?php
include 'db.php';

if (isset($_POST['btnRegister'])) {
    $email = $_POST['email'];
    $name = $_POST['display_name'];
    $pass = $_POST['password'];
    $repass = $_POST['re_password'];

    if ($pass !== $repass) {
        $error = "Mật khẩu nhập lại không khớp!";
    } else {
        $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (email, display_name, password_hash, is_verified) 
                VALUES ('$email', '$name', '$hashed_pass', 0)";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['user_name'] = $name;
            $_SESSION['is_verified'] = 0; 

            header("Location: index.php");
            exit();
        } else {
            $error = "Email đã tồn tại hoặc lỗi hệ thống!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Đăng ký</title></head>
<body>
    <form method="POST">
        <h2>Đăng ký tài khoản</h2>
        <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="text" name="display_name" placeholder="Tên hiển thị" required><br>
        <input type="password" name="password" placeholder="Mật khẩu" required><br>
        <input type="password" name="re_password" placeholder="Nhập lại mật khẩu" required><br>
        <button type="submit" name="btnRegister">Đăng ký ngay</button>
    </form>
    <a href="login.php">Đã có tài khoản? Đăng nhập</a>
</body>
</html>