<?php
include __DIR__ . '/../db.php';

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
            $_SESSION["is_verified"] = 0;

            header("Location: ../index.php");
            exit();
        } else {
            $error = "Email already exists";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../Assets/css/style.css">
</head>
<body>

<nav class="custom-navbar">
    <div class="logo">NotesFlow</div>
</nav>

    <main>
        <div class="content-wrapper">
            <div class="illustration-box">
                <img src="../Assets/images/web_img/potted plants-pana.png" alt="Illustration"> 
            </div>

            <div class="login-card">
                <h2 class="card-title">Sign Up</h2>
                
                <form method="POST" class="login-form" id="registerForm">
                    <div class="input-wrapper">
                        <input type="email" name="email" id="email" class="input-field" placeholder="Email address" required>
                        <span class="error-message" id="emailError"></span>
                    </div>

                    <div class="input-wrapper">
                        <input type="text" name="display_name" id="name" class="input-field" placeholder="Display Name" required>
                        <span class="error-message" id="nameError"></span>
                    </div>

                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" class="input-field" placeholder="Password" required>
                        <i class="fa-regular fa-eye-slash toggle-password"></i>
                    </div>
                    <span class="error-message" id="passwordError"></span>

                    <div class="input-wrapper">
                       <input type="password" name="confirm_password" id="confirm-password" class="input-field" placeholder="Confirm Password" required>
                        <i class="fa-regular fa-eye-slash toggle-password"></i>
                    </div>
                    <span class="error-message" id="repasswordError"></span>

                    <button type="submit" name="btnRegister" class="btn-submit-login">Sign Up</button>
                </form>

                <div class="signup-prompt">
                    Already have an account? <a href="login.php">Login</a>
                </div>

                <p><?= $error ?></p>
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
    <script src="../Assets/js/app.js"></script>
</body>
</html>