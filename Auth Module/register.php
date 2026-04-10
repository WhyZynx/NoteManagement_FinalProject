<?php
session_start();
include 'db.php';

if (isset($_POST['btnRegister'])) {
    $email = $_POST['email'];
    $name = $_POST['display_name'];
    $pass = $_POST['password'];
    $repass = $_POST['re_password'];

    if ($pass !== $repass) {
        $error = "The password entered again doesn't match!";
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
            $error = "The email address already exists, or there's a system error!";
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
                
                <form method="POST" class="login-form" id="registerForm">
                    <div class="input-wrapper">
                        <input type="email" name="email" id="email" class="input-field" placeholder="Email address">
                        <span class="error-message" id="emailError"></span>
                    </div>

                    <div class="input-wrapper">
                        <input type="text" name="display_name" id="name" class="input-field" placeholder="Display Name">
                        <span class="error-message" id="nameError"></span>
                    </div>

                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" class="input-field" placeholder="Password">
                        <i class="fa-regular fa-eye-slash toggle-password"></i>
                    </div>
                    <span class="error-message" id="passwordError"></span>

                    <div class="input-wrapper">
                        <input type="password" name="re_password" id="repassword" class="input-field" placeholder="Confirm Password">
                        <i class="fa-regular fa-eye-slash toggle-password"></i>
                    </div>
                    <span class="error-message" id="repasswordError"></span>

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
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("registerForm");

            const email = document.getElementById("email");
            const name = document.getElementById("name");
            const password = document.getElementById("password");
            const repassword = document.getElementById("repassword");

            form.addEventListener("submit", function (e) {
                let isValid = true;

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!emailRegex.test(email.value)) {
                    showError(email, "emailError", "Invalid email format");
                    isValid = false;
                } else hideError(email, "emailError");

                if (name.value.trim() === "") {
                    showError(name, "nameError", "Name cannot be empty");
                    isValid = false;
                } else hideError(name, "nameError");

                if (password.value.trim() === "") {
                    showError(password, "passwordError", "Password cannot be empty");
                    isValid = false;
                } else hideError(password, "passwordError");

                if (repassword.value !== password.value || repassword.value === "") {
                    showError(repassword, "repasswordError", "Passwords do not match");
                    isValid = false;
                } else hideError(repassword, "repasswordError");

                if (!isValid) e.preventDefault();
            });

            function showError(input, id, message) {
                input.classList.add("is-invalid");
                const span = document.getElementById(id);
                span.innerText = message;
                span.classList.add("show");
            }

            function hideError(input, id) {
                input.classList.remove("is-invalid");
                const span = document.getElementById(id);
                span.classList.remove("show");
            }
        });

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