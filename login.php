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
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="login.css">
</head>
<body class="min-vh-100 d-flex flex-column">

    <header class="header-container">
        <button class="back-btn"><i class="fa-solid fa-arrow-left"></i></button>
        <div class="header-right">
            <span class="lang-selector"><i class="fa-solid fa-globe me-2"></i> EN</span>
            <button class="nav-login-btn">Login</button>
            <a href="register.php" class="nav-signup-link">Sign Up</a>
        </div>
    </header>

    <main class="main-content">
        <div class="content-wrapper">
            <div class="login-card">
                <h1 class="card-title">Login</h1>

                <form action="login.php" method="POST" class="login-form">
                    <div class="input-wrapper">
                        <input type="text" name="email" placeholder="Email address or Username" class="input-field" required>
                    </div>

                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" placeholder="Password" class="input-field" required>
                        <i class="fa-regular fa-eye toggle-password" id="togglePassword"></i>
                    </div>

                    <div class="forget-pwd-container">
                        <a href="#" class="forget-link">Forget Password?</a>
                    </div>

                    <button type="submit" name="btnLogin" class="btn-submit-login">
                        Login
                    </button>

                    <p class="signup-prompt">
                        Don't have an account? <a href="register.php">Sign up</a>
                    </p>
                </form>
            </div>

            <div class="illustration-box">
                <img src="web_img/rose.png" alt="Rose Illustration">
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
        const toggle = document.getElementById("togglePassword");
        const passwordField = document.getElementById("password");

        toggle.addEventListener("click", function () {
            const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
            passwordField.setAttribute("type", type);
            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    </script>
</body>
</html>