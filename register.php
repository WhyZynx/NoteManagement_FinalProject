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
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
<body>
    <nav class="custom-navbar navbar navbar-expand-lg">
    
        <button class="btn p-1" onclick="history.back()">
            <i class="fa-solid fa-arrow-left"></i>
        </button>

        <div class="ms-auto d-flex align-items-center nav-right">
            
            <div class="d-flex align-items-center gap-1 lang">
                <i class="fa-solid fa-globe"></i>
                <span>EN</span>
            </div>

            <a href="login.php" class="btn signUp-btn">Login</a>
            <button class="btn login-btn">Sign Up</button>

        </div>
    </nav>

    <main>
        <div class="content-wrapper">
            <div class="illustration-box">
                <img src="web_img/potted plants-pana.png" alt="Illustration"> 
            </div>

            <div class="login-card">
                <h2 class="card-title">Sign Up</h2>
                
                <form method="POST" class="login-form">
                    <?php if(isset($error)) echo "<p style='color:red; font-size:12px; margin-bottom:10px;'>$error</p>"; ?>
                    
                    <div class="input-wrapper">
                        <input type="email" name="email" class="input-field" placeholder="Email address or Username" required>
                    </div>

                    <div class="input-wrapper">
                        <input type="text" name="display_name" class="input-field" placeholder="Display Name" required>
                    </div>

                    <div class="input-wrapper">
                        <input type="password" name="password" class="input-field password-field" name="re_password" class="input-field" placeholder="Password" required>
                        <i class="fa-regular fa-eye-slash toggle-password"></i>
                    </div>

                    <div class="input-wrapper">
                        <input type="password" name="re_password" class="input-field password-field" name="re_password" class="input-field" placeholder="Confirm Password" required>
                        <i class="fa-regular fa-eye-slash toggle-password"></i>
                    </div>

                    <button type="submit" name="btnRegister" class="btn-submit-login">Sign Up</button>
                </form>

                <div class="signup-prompt">
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </div>
        </div>

    </main>

    <footer class="footer-bg">
        <div class="container">
            <h5>Support</h5>
            <ul class="list-unstyled">
                <li><a href="#">Help</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
        </div>
    </footer>

    <script>
        const toggles = document.querySelectorAll(".toggle-password");
        toggles.forEach(toggle => {
            toggle.addEventListener("click", function () {
                const input = this.previousElementSibling;

                const type = input.type === "password" ? "text" : "password";
                input.type = type;

                this.classList.toggle("fa-eye");
                this.classList.toggle("fa-eye-slash");
            });
        });
        </script>

</body>
</html>