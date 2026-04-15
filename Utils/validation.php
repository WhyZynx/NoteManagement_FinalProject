<?php
function validateRequired($fields)
{
    foreach ($fields as $field) {
        if (!isset($field) || trim($field) === '') {
            return "Please fill in all fields";
        }
    }
    return null;
}

function validatePasswordStrength($password)
{
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters";
    }

    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain uppercase letter";
    }

    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain lowercase letter";
    }

    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain number";
    }

    return null;
}

function validateConfirmPassword($password, $confirm)
{
    if ($password !== $confirm) {
        return "Passwords do not match";
    }
    return null;
}

function validateEmail($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }
    return null;
}

function validateDifferentFromOldPassword($newPassword, $oldHash)
{
    if (password_verify($newPassword, $oldHash)) {
        return "New password must be different from old password";
    }
    return null;
}
?>