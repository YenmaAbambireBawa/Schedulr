<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'schedulr.au@gmail.com'; // YOUR gmail
    $mail->Password   = 'pwab wlou hpgz zeum'; // 16-char app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Email details
    $mail->setFrom('schedulr.au@gmail.com', 'Schedulr');
    $mail->addAddress('abambirebawayenma@gmail.com'); // where email goes

    $mail->isHTML(true);
    $mail->Subject = 'Test Email from PHP';
    $mail->Body    = '<h2>It worked </h2><p>The PHP SMTP email is live.</p>';
    $mail->AltBody = 'It worked! Your PHP SMTP email is live.';

    $mail->send();
    echo 'Email sent successfully ';
} catch (Exception $e) {
    echo "Email failed  Error: {$mail->ErrorInfo}";
}
