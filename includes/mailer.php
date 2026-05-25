<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

// Choisis ton service SMTP :
// 1. Pour Gmail, décommente la section Gmail et renseigne tes infos
// 2. Pour Mailtrap, décommente la section Mailtrap et renseigne tes infos
// 3. Pour Outlook, décommente la section Outlook et renseigne tes infos

function sendMailSMTP($to, $subject, $body, $toName = "") {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->CharSet = 'UTF-8';

        // --- GMAIL ---
        // $mail->Host = 'smtp.gmail.com';
        // $mail->Username = 'ton.email@gmail.com';
        // $mail->Password = 'mot_de_passe_application'; // Utilise un mot de passe d'application Gmail
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        // $mail->Port = 587;
        // $mail->setFrom('ton.email@gmail.com', 'GC Manager');

        // --- MAILTRAP ---
        // $mail->Host = 'sandbox.smtp.mailtrap.io';
        // $mail->Username = 'MAILTRAP_USERNAME';
        // $mail->Password = 'MAILTRAP_PASSWORD';
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        // $mail->Port = 587;
        // $mail->setFrom('no-reply@gcmanager.local', 'GC Manager');

        // --- OUTLOOK ---
        // $mail->Host = 'smtp.office365.com';
        // $mail->Username = 'ton.email@outlook.com';
        // $mail->Password = 'ton_mot_de_passe';
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        // $mail->Port = 587;
        // $mail->setFrom('ton.email@outlook.com', 'GC Manager');

        // --- FIN CONFIG ---

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
