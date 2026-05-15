<?php
session_start();
include __DIR__ . '/../db.php';
include __DIR__ . '/../Utils/email.php';
include __DIR__ . '/../Utils/validation.php';
include __DIR__ . '/../Utils/security.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = "";

$email = trim(strtolower($_POST["email"] ?? ""));
$display_name = trim($_POST["display_name"] ?? "");
$password = trim($_POST["password"] ?? "");
$confirm_password = trim($_POST["confirm_password"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $error = validateRequired([$email, $display_name, $password, $confirm_password])
        ?? validateEmail($email)
        ?? validatePasswordStrength($password)
        ?? validateConfirmPassword($password, $confirm_password);

    if (!$error) {

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "Email already exists";
        } else {

            $password_hash = hashPassword($password);
            $verify_token = generateToken();

            $sql = "INSERT INTO users (email, display_name, password_hash, verify_token, is_verified)
                    VALUES (?, ?, ?, ?, 0)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $email, $display_name, $password_hash, $verify_token);

            if ($stmt->execute()) {

                $_SESSION["user_id"] = $stmt->insert_id;
                $_SESSION["email"] = $email;
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

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
    <script src="../Assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>