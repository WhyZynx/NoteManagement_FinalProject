<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

function sendVerificationEmail($email, $token)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mindflow.notes2026@gmail.com';
        $mail->Password = 'gkkq ctma dcmm ukyq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';

        $mail->setFrom('mindflow.notes2026@gmail.com', 'Notes App');
        $mail->addAddress($email);

        $verifyLink = "http://localhost/Auth%20Module/verify_email.php?token=" . urlencode($token);
        $mail->isHTML(true);
        $mail->Subject = "Verify your account";
        $mail->Body = "
            <h2>Welcome to NotesFlow</h2>
            <p>Thanks for registering.</p>
            <p>Please verify your account:</p>
            <p><a href='$verifyLink'>Click here to verify</a></p>
            <br>
            <p>If you didn't request this email, ignore it.</p>
        ";

        return $mail->send();

    } catch (Exception $e) {
        return false;
    }
}
?>