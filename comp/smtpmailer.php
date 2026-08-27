<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';

function smtpmailer($to, $subject, $body) {
    global $error;
    $from = "no-reply@samp-net.com";
    $from_name = "Samp-net role play";
    $mail = new PHPMailer();  // create a new object
    $mail->IsSMTP(); // enable SMTP
    $mail->CharSet = "utf-8";
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->SMTPAuth = true;  // authentication enabled
    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
    $mail->Host = 'smtp.yandex.ru';
    $mail->Port = 465;
    $mail->Username = "no-reply@samp-net.com";
    $mail->Password = "d7342121DD";
    $mail->SetFrom($from, $from_name);
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AddAddress($to);
    if (!$mail->Send()) {
        $error = 'Mail error: ' . $mail->ErrorInfo;
        return false;
    } else {
        $error = 'Message sent!';
        return true;
    }
}

?>
