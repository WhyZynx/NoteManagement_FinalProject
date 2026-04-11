<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/email.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim(strtolower($_POST["email"]));
    $display_name = trim($_POST["display_name"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {

        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $verify_token = bin2hex(random_bytes(32));

        $check_sql = "SELECT id, display_name, is_verified FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ($user["is_verified"] == 0) {
                $update_sql = "UPDATE users SET verify_token = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $verify_token, $user["id"]);
                $update_stmt->execute();

                sendVerificationEmail($email, $verify_token);
            }

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["display_name"] = $user["display_name"];
            $_SESSION["is_verified"] = $user["is_verified"];

            header("Location: ../index.php");
            exit();
        }

        $sql = "INSERT INTO users (email, display_name, password_hash, verify_token, is_verified)
                VALUES (?, ?, ?, ?, 0)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $email, $display_name, $password_hash, $verify_token);

        if ($stmt->execute()) {

            $_SESSION["user_id"] = $stmt->insert_id;
            $_SESSION["display_name"] = $display_name;
            $_SESSION["is_verified"] = 0;

            sendVerificationEmail($email, $verify_token);

            header("Location: ../index.php");
            exit();

        } else {
            $error = "Registration failed";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../Assets/css/style.css">
</head>
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