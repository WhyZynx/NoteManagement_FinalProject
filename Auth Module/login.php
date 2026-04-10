<?php
include __DIR__ . '/../db.php';

$error = "";

if (isset($_POST['btnLogin'])) {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($pass, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['display_name'];
        $_SESSION['is_verified'] = $user['is_verified'];

        header("Location: ../index.php");
        exit();
    } else {
        $error = "Email or password is incorrect!";
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
    <link rel="stylesheet" href="../Assets/css/style.css">
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

                <form action="login.php" method="POST" class="login-form">
                    <div class="input-wrapper">
                        <input type="text" name="email" placeholder="Email address" class="input-field" required>
                    </div>

                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" placeholder="Password" class="input-field" required>
                        <i class="fa-regular fa-eye-slash toggle-password" id="togglePassword"></i>
                    </div>

                    <div class="forget-pwd-container">
                        <a href="forgot_password.php" class="forget-link">Forgot Password?</a>
                    </div>

                    <button type="submit" name="btnLogin" class="btn-submit-login">
                        Login
                    </button>
                    <?php if (!empty($error)) : ?>
                        <p style="color:red; margin-top:10px;">
                            <?= $error ?>
                        </p>
                    <?php endif; ?>

                    <p class="signup-prompt">
                        Don't have an account? <a href="register.php">Sign up</a>
                    </p>
                </form>
            </div>

            <div class="illustration-box">
                <img src="../Assets/images/web_img/rose.png" alt="Rose Illustration">
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