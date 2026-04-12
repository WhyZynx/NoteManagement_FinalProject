<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/email.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim(strtolower($_POST["email"] ?? ""));
    $display_name = trim($_POST["display_name"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");

    $passwordPattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/";

    if ($email === "" || $display_name === "" || $password === "" || $confirm_password === "") {
        $error = "Please fill in all fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (!preg_match($passwordPattern, $password)) {
        $error = "Password must be at least 8 characters and include uppercase, lowercase, and a number";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {

        $check_sql = "SELECT id, display_name, is_verified FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        $verify_token = bin2hex(random_bytes(32));

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ($user["is_verified"] == 0) {
                $update_sql = "UPDATE users SET verify_token = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $verify_token, $user["id"]);
                $update_stmt->execute();

                sendVerificationEmail($email, $verify_token);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["display_name"] = $user["display_name"];
                $_SESSION["is_verified"] = 0;

                header("Location: ../index.php");
                exit();
            } else {
                $error = "Email already exists";
            }
        } else {

            $password_hash = password_hash($password, PASSWORD_BCRYPT);

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
    
        <button class="btn p-1" onclick="window.location.href='../about.html'">
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

            <div class="register-card">
                <h2 class="card-title">Sign Up</h2>
                
                <form method="POST" class="login-form" id="registerForm">
                    <div class="input-wrapper">
                        <input type="email" name="email" id="email" class="input-field" placeholder="Email address"  value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        <span class="error-message" id="emailError"></span>
                    </div>

                    <div class="input-wrapper">
                        <input type="text" name="display_name" id="name" class="input-field" placeholder="Display Name"  value="<?= htmlspecialchars($_POST['display_name'] ?? '') ?>">
                        <span class="error-message" id="nameError"></span>
                    </div>

                    <div class="input-wrapper">
                        <div class="input-group-custom">
                            <input type="password" name="password" id="password" class="input-field" placeholder="Password">
                            <i class="fa-regular fa-eye-slash toggle-password"></i>
                        </div>
                        <span class="error-message" id="passwordError"></span>
                    </div>

                    <div class="input-wrapper">
                        <div class="input-group-custom">
                            <input type="password" name="confirm_password" id="confirm-password" class="input-field" placeholder="Confirm Password">
                            <i class="fa-regular fa-eye-slash toggle-password"></i>
                        </div>
                        <span class="error-message" id="repasswordError"></span>
                    </div>

                    <button type="submit" name="btnRegister" class="btn-submit-signUp">Sign Up</button>
                </form>

                <div class="signup-prompt">
                    Already have an account? <a href="login.php">Login</a>
                </div>

               <?php if (!empty($error)): ?>
                    <div class="alert alert-danger mt-3">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
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
    <script src="/Assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>