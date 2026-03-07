<?php
/**
 * Schedulr — Email Test Script v2
 * Place at: C:/xampp/htdocs/schedulr/test-email.php
 * Visit:    http://localhost/schedulr/test-email.php
 * DELETE this file when done.
 */

$TEST_RECIPIENT = 'abambirebawayenma@gmail.com';

echo '<pre style="font-family:monospace;font-size:14px;padding:20px;">';
echo "=== Schedulr Email Diagnostic ===\n\n";

// 1. PHP version
echo "PHP version:    " . PHP_VERSION . "\n";
echo PHP_VERSION_ID >= 70400 ? "✅ OK\n\n" : "❌ FAIL — upgrade PHP in XAMPP\n\n";

// 2. Composer / vendor
$autoload = __DIR__ . '/vendor/autoload.php';
echo "Checking vendor/autoload.php... ";
if (file_exists($autoload)) { echo "✅ Found\n"; require_once $autoload; }
else { echo "❌ NOT FOUND — run: composer require phpmailer/phpmailer\n</pre>"; exit; }

// 3. PHPMailer
echo "Checking PHPMailer class...    ";
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) { echo "✅ Loaded\n"; }
else { echo "❌ NOT found\n</pre>"; exit; }

// 4. config/email.php
$configFile = __DIR__ . '/config/email.php';
echo "Checking config/email.php...   ";
if (file_exists($configFile)) { echo "✅ Found\n"; require_once $configFile; }
else { echo "❌ NOT FOUND\n</pre>"; exit; }

// 5. Show config values (no blocking checks)
echo "\n--- Config values ---\n";
echo "MAIL_HOST:      " . MAIL_HOST . "\n";
echo "MAIL_PORT:      " . MAIL_PORT . "\n";
echo "MAIL_USERNAME:  " . MAIL_USERNAME . "\n";
echo "MAIL_PASSWORD:  " . str_repeat('*', max(0, strlen(MAIL_PASSWORD) - 4)) . substr(MAIL_PASSWORD, -4) . "\n";
echo "MAIL_FROM:      " . MAIL_FROM . "\n";
echo "APP_BASE_URL:   " . APP_BASE_URL . "\n";

// 6. OpenSSL
echo "\nChecking OpenSSL extension...  ";
if (extension_loaded('openssl')) { echo "✅ Enabled\n"; }
else {
    echo "❌ DISABLED\n   Fix: Open C:/xampp/php/php.ini, find ';extension=openssl', remove the semicolon, restart Apache.\n</pre>";
    exit;
}

// 7. Send test email
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "\n--- Attempting to send to: $TEST_RECIPIENT ---\n";

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) { echo htmlspecialchars($str) . "\n"; };
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl')
                        ? PHPMailer::ENCRYPTION_SMTPS
                        : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->Timeout    = 15;
    $mail->setFrom(MAIL_FROM, 'Schedulr Test');
    $mail->addAddress($TEST_RECIPIENT);
    $mail->isHTML(true);
    $mail->Subject = 'Schedulr test email — ' . date('H:i:s');
    $mail->Body    = '<p style="font-family:Arial;font-size:16px;">Test from <strong>Schedulr</strong>. If you got this, email is working! ✅</p>';
    $mail->AltBody = 'Test from Schedulr. Email is working!';
    $mail->send();
    echo "\n✅ SUCCESS — check your inbox (and spam folder)\n";
} catch (Exception $e) {
    echo "\n❌ FAILED — " . $mail->ErrorInfo . "\n\n";
    echo "Common fixes:\n";
    echo "• 'Username and Password not accepted' → wrong App Password, or 2FA not enabled on Google account\n";
    echo "• 'Could not connect to SMTP host'     → try MAIL_PORT=465 and MAIL_ENCRYPTION='ssl' in config/email.php\n";
    echo "• 'Connection timed out'               → your network blocks port 587, try a mobile hotspot\n";
}

echo "\n</pre>";
echo '<p style="font-family:Arial;color:red;font-weight:bold;">⚠️ Delete this file when done!</p>';