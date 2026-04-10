<?php
session_start();
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
        $error = "Incorrect email or password!";
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
    <link rel="stylesheet" href="style.css">
</head>
<body class="min-vh-100 d-flex flex-column">

    <nav class="custom-navbar navbar navbar-expand-lg">
        <button class="btn p-1" onclick="history.back()">
            <i class="fa-solid fa-arrow-left"></i>
        </button>

        <div class="ms-auto d-flex align-items-center nav-right">
            <div class="d-flex align-items-center gap-1 lang">
                <i class="fa-solid fa-globe"></i>
                <span>EN</span>
            </div>
            <button class="btn login-btn">Login</button>
            <a href="register.php" class="btn signUp-btn">Sign Up</a>

        </div>
    </nav>

    <main class="main-content">
        <div class="content-wrapper">
            <div class="login-card">
                <h1 class="card-title">Login</h1>

                <form action="login.php" method="POST" class="login-form" id="loginForm">
                    <div class="input-wrapper">
                        <input type="text" name="email" id="email" placeholder="Email address or Username" class="input-field">
                        <span class="error-message" id="emailError"></span>
                    </div>

                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" placeholder="Password" class="input-field">
                        <i class="fa-regular fa-eye-slash toggle-password" id="togglePassword"></i>
                    </div>
                    
                    <span class="error-message <?php if (isset($error)) echo 'show'; ?>" id="passwordError">
                        <?php 
                            if (isset($error)) echo $error;
                        ?>
                    </span>

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
    document.addEventListener("DOMContentLoaded", function () {
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const toggle = document.getElementById("togglePassword");

        loginForm.addEventListener('submit', function (e) {
            let isValid = true;

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(emailInput.value)) {
                showError(emailInput, 'emailError', 'Please enter your email address in the correct format');
                isValid = false;
            } else {
                hideError(emailInput, 'emailError');
            }

            if (passwordInput.value.trim() === "") {
                showError(passwordInput, 'passwordError', 'The password cannot be left blank');
                isValid = false;
            } else {
                hideError(passwordInput, 'passwordError');
            }

            if (!isValid) e.preventDefault();
        });

        function showError(input, spanId, message) {
            input.classList.add('is-invalid');
            const span = document.getElementById(spanId);
            span.style.display = 'block';
            span.innerText = message;
        }

        function hideError(input, spanId) {
            input.classList.remove('is-invalid');
            const span = document.getElementById(spanId);
            span.style.display = 'none';
        }

        toggle.addEventListener("click", function () {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    });
    </script>
</body>
</html>