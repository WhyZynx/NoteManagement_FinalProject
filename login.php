<?php
include 'db.php';

if (isset($_POST['btnLogin'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($pass, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['display_name'];
        $_SESSION['is_verified'] = $user['is_verified'];
        
        header("Location: index.php");
        exit();
    } else {
        $error = "Email hoặc mật khẩu không chính xác!";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Đăng nhập</title></head>
<body>
    <form method="POST">
        <h2>Đăng nhập</h2>
        <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Mật khẩu" required><br>
        <button type="submit" name="btnLogin">Đăng nhập</button>
    </form>
    <a href="register.php">Chưa có tài khoản? Đăng ký</a> | 
    <a href="forgot_password.php">Quên mật khẩu?</a>
</body>
</html>