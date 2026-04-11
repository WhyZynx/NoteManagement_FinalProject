<?php
session_start();
include __DIR__ . '/../db.php';

$error = "";

if (isset($_POST['btnLogin'])) {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $pass = trim($_POST['password'] ?? '');

    if ($email === "" || $pass === "") {
        $error = "Please fill in all fields";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($pass, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['display_name'] = $user['display_name'];

                header("Location: ../index.php");
                exit();
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }
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
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="login-form" id="loginForm">
                    <div class="input-wrapper">
                        <input type="text" name="email" id="email" placeholder="Email address or Username" class="input-field">
                        <span class="error-message" id="emailError"></span>
                    </div>

                    <div class="input-wrapper">
                        <div class="input-group-custom">
                            <input type="password" name="password" id="password" class="input-field" placeholder="Password">
                            <i class="fa-regular fa-eye-slash toggle-password" id="togglePassword"></i>
                        </div>
                        <span class="error-message" id="passwordError"></span>
                    </div>

                    <div class="forget-pwd-container">
                        <a href="forgot_password.php" class="forget-link">Forgot Password?</a>
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
    <script src="/Assets/js/login.js?v=<?php echo time(); ?>"></script>
</body>
</html>