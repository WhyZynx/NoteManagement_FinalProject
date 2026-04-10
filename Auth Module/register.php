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
            $error = "Email already exists";
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
        <div class="login-card">
            <h2 class="card-title">Register</h2>

            <form method="POST" class="login-form">
                <div class="input-wrapper">
                    <input type="email" name="email" class="input-field" placeholder="Email" required>
                </div>

                <div class="input-wrapper">
                    <input type="text" name="display_name" class="input-field" placeholder="Display Name" required>
                </div>

                <div class="input-wrapper">
                    <input type="password" name="password" class="input-field" placeholder="Password" required>
                </div>

                <div class="input-wrapper">
                    <input type="password" name="confirm_password" class="input-field" placeholder="Confirm Password" required>
                </div>

                <button type="submit" class="btn-submit-login">Sign Up</button>

                <p><?= $error ?></p>
            </form>
        </div>
    </div>
</main>

</body>
</html>