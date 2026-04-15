<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

function createMailer()
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'mindflow.notes2026@gmail.com';
    $mail->Password = 'gkkq ctma dcmm ukyq';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('mindflow.notes2026@gmail.com', 'MindFlow');
    $mail->isHTML(true);

    return $mail;
}

function sendVerificationEmail($email, $token)
{
    try {
        $mail = createMailer();
        $mail->addAddress($email);

        //$verifyLink = "http://localhost/Auth_Module/verify_email.php?token=" . urlencode($token);
        //Subfolder, xóa sau khi hoàn thành dự án,đổi thành cái trên
        $verifyLink =
    (isset($_SERVER['HTTPS']) ? 'https' : 'http') .
    '://' .
    $_SERVER['HTTP_HOST'] .
    dirname($_SERVER['PHP_SELF']) .
    '/../Auth_Module/verify_email.php?token=' . urlencode($token);

        $mail->Subject = "Verify your account";
        $mail->Body = "
            <h2>Welcome to MindFlow</h2>
            <p>Thanks for registering.</p>
            <p>Please verify your account:</p>
            <p><a href='$verifyLink'>Click here to verify</a></p>
            <p>If you didn't request this email, ignore it.</p>
        ";

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}

function sendOtpEmail($email, $otp)
{
    try {
        $mail = createMailer();
        $mail->addAddress($email);

        $mail->Subject = "Password Reset OTP";
        $mail->Body = "
            <h2>Your OTP Code</h2>
            <p>Your OTP is: <b>$otp</b></p>
            <p>This code expires in 5 minutes.</p>
        ";

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
?>