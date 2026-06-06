<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

function sendMailSMTP($to, $subject, $body, $toName = "") {
    $mail = new PHPMailer(true);
    try {
        if (!defined('SMTP_HOST') || SMTP_HOST === '' || SMTP_USERNAME === '' || SMTP_PASSWORD === '') {
            error_log('Mailer Error: configuration SMTP incomplete.');
            return false;
        }

        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->CharSet = 'UTF-8';
        $mail->Host = SMTP_HOST;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

        $mail->addAddress($to, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
