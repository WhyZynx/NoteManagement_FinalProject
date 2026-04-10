<?php
include '../db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $display_name = trim($_POST["display_name"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $verify_token = bin2hex(random_bytes(32));

        $sql = "INSERT INTO users(email, display_name, password_hash, verify_token)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $email, $display_name, $password_hash, $verify_token);

        if ($stmt->execute()) {
            $_SESSION["user_id"] = $conn->insert_id;
            $_SESSION["display_name"] = $display_name;

            header("Location: index.php");
            exit();
        } else {
            $error = "The email address already exists, or there's a system error!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="custom-navbar">
    <div class="logo">NotesFlow</div>
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

                <button type="submit" class="btn-submit-login">Sign Up</button>

                <p><?= $error ?></p>
            </form>
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