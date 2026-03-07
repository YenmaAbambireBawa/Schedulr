<?php
/**
 * Submit Course Registration API
 * Handles student course registration with 3 timetable options.
 * Encrypts myCAMU password and sends verification email via SMTP.
 *
 * DUMMY MODE: Set DUMMY_CAMU=true below to bypass real myCAMU and DB,
 * and redirect to the visual myCAMU simulator instead.
 */
ob_start();

header('Content-Type: application/json');
require_once __DIR__ . '/../middleware/Auth.php';

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ─────────────────────────────────────────────────────────────
// DUMMY MODE SWITCH
// Set to true to use the visual myCAMU simulator.
// Set to false to use real DB + SMTP.
// ─────────────────────────────────────────────────────────────
define('DUMMY_CAMU', true);

if (!getenv('ENCRYPTION_KEY')) {
    putenv('ENCRYPTION_KEY=' . bin2hex(random_bytes(32)));
}

try {
    Auth::requireStudent();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $input = file_get_contents('php://input');
    $data  = json_decode($input, true);

    // Validate required fields
    $requiredFields = ['student_id', 'student_email', 'mycamu_email', 'mycamu_password', 'timetable_options'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            http_response_code(400);
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }

    if (!isset($data['timetable_options']['option1'],
                $data['timetable_options']['option2'],
                $data['timetable_options']['option3'])) {
        http_response_code(400);
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid timetable options structure']);
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // DUMMY MODE: skip DB + SMTP, save to JSON, return sim URL
    // ─────────────────────────────────────────────────────────
    if (DUMMY_CAMU) {

        // Simulate a quick credential check
        $mycamuPassword = $data['mycamu_password'];
        if (strlen($mycamuPassword) < 6) {
            http_response_code(401);
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'myCAMU authentication failed: password too short.']);
            exit;
        }
        if ($mycamuPassword === 'wrong123') {
            http_response_code(401);
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'myCAMU authentication failed: invalid credentials.', 'camu_error_code' => 'AUTH_INVALID']);
            exit;
        }

        $registrationId = 'REG-' . strtoupper(substr(md5(uniqid($data['student_id'], true)), 0, 10));
        $camuJobId      = 'JOB-' . rand(100000, 999999);

        $record = [
            'registration_id'   => $registrationId,
            'student_id'        => $data['student_id'],
            'student_email'     => $data['student_email'],
            'mycamu_email'      => $data['mycamu_email'],
            'timetable_options' => $data['timetable_options'],
            'status'            => 'pending',
            'submitted_at'      => date('c'),
            'camu_job_id'       => $camuJobId,
        ];

        // Persist to JSON "database"
        $savePath = __DIR__ . '/../user_data/registrations.json';
        $existing = [];
        if (file_exists($savePath)) {
            $existing = json_decode(file_get_contents($savePath), true) ?? [];
        }
        $existing[$registrationId] = $record;
        @file_put_contents($savePath, json_encode($existing, JSON_PRETTY_PRINT));

        ob_end_clean();
        echo json_encode([
            'success'         => true,
            'message'         => 'Registration submitted. Redirecting to myCAMU simulator...',
            'registration_id' => $registrationId,
            'email_sent'      => false,
            'dummy_mode'      => true,
            // Front-end will redirect here after showing the success message
            'simulator_url'   => '../student/camu-simulator.php?id=' . $registrationId,
        ]);
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // PRODUCTION MODE: real DB + SMTP below
    // ─────────────────────────────────────────────────────────

    try {
        $db = new PDO(
            "mysql:host=localhost;dbname=schedulr_db;charset=utf8mb4",
            "root", "",
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }

    function encryptPassword($password) {
        $key = getenv('ENCRYPTION_KEY');
        if (!$key) throw new Exception('Encryption key not configured');
        $iv        = random_bytes(16);
        $encrypted = openssl_encrypt($password, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    function generateVerificationToken() {
        return bin2hex(random_bytes(32));
    }

    function sendVerificationEmailSMTP($email, $token, $registrationId) {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return sendVerificationEmailBasic($email, $token, $registrationId);
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'schedulr.au@gmail.com';
            $mail->Password   = 'uwvi toph auiz wfvs';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $protocol        = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $baseUrl         = $protocol . '://' . $_SERVER['HTTP_HOST'];
            $verificationLink = $baseUrl . '/schedulr/pages/verify-registration.php?token=' . $token . '&id=' . $registrationId;

            $mail->setFrom('noreply@schedulr.edu', 'Schedulr');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Course Registration - Schedulr';
            $mail->Body = "
            <html><head><style>
                body{font-family:Arial,sans-serif;line-height:1.6;color:#333}
                .container{max-width:600px;margin:0 auto;padding:20px}
                .header{background:linear-gradient(135deg,#dc2626,#991b1b);color:white;padding:30px;text-align:center;border-radius:10px 10px 0 0}
                .content{background:#f9fafb;padding:30px;border-radius:0 0 10px 10px}
                .button{display:inline-block;background:#dc2626;color:white;padding:15px 30px;text-decoration:none;border-radius:8px;font-weight:bold;margin:20px 0}
                .footer{text-align:center;margin-top:30px;color:#666;font-size:12px}
            </style></head>
            <body><div class='container'>
                <div class='header'><h1>Verify Your Course Registration</h1></div>
                <div class='content'>
                    <p>Hello,</p>
                    <p>Thank you for submitting your course registration with Schedulr! Click the button below to verify:</p>
                    <div style='text-align:center'><a href='{$verificationLink}' class='button'>Verify Email Address</a></div>
                    <p>Or copy this link: <span style='word-break:break-all;background:white;padding:10px;border-radius:5px;display:block'>{$verificationLink}</span></p>
                    <p><strong>This link expires in 24 hours.</strong></p>
                    <p>Once verified, your courses will be automatically registered in myCAMU according to your ranked preferences.</p>
                    <div class='footer'><p>Automated email from Schedulr. Do not reply.</p></div>
                </div>
            </div></body></html>";
            $mail->AltBody = "Verify your registration: {$verificationLink} (expires in 24 hours)";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: {$mail->ErrorInfo}");
            return false;
        }
    }

    function sendVerificationEmailBasic($email, $token, $registrationId) {
        $protocol        = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $baseUrl         = $protocol . '://' . $_SERVER['HTTP_HOST'];
        $verificationLink = $baseUrl . '/pages/verify-registration.php?token=' . $token . '&id=' . $registrationId;

        $subject  = 'Verify Your Course Registration - Schedulr';
        $message  = "<html><body><p>Please verify your registration: <a href='{$verificationLink}'>{$verificationLink}</a></p></body></html>";
        $headers  = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: Schedulr <noreply@schedulr.edu>\r\n";
        return mail($email, $subject, $message, $headers);
    }

    $db->beginTransaction();

    $encryptedPassword  = encryptPassword($data['mycamu_password']);
    $verificationToken  = generateVerificationToken();
    $tokenExpiry        = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $timetableOptionsJson = json_encode($data['timetable_options']);

    $query = "INSERT INTO course_registrations (
        student_id, student_email, mycamu_email, mycamu_password_encrypted,
        registration_status, verification_token, verification_token_expires,
        timetable_options, submitted_at
    ) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, NOW())";

    $stmt = $db->prepare($query);
    $stmt->execute([
        $data['student_id'],
        $data['student_email'],
        $data['mycamu_email'],
        $encryptedPassword,
        $verificationToken,
        $tokenExpiry,
        $timetableOptionsJson,
    ]);

    $registrationId = $db->lastInsertId();
    $emailSent      = sendVerificationEmailSMTP($data['student_email'], $verificationToken, $registrationId);

    if (!$emailSent) {
        error_log("Warning: Verification email failed for registration ID: $registrationId");
    }

    $db->commit();

    ob_end_clean();
    echo json_encode([
        'success'         => true,
        'message'         => 'Registration submitted successfully. Please check your email to verify.',
        'registration_id' => $registrationId,
        'email_sent'      => $emailSent,
        'warning'         => !$emailSent ? 'Registration saved but email notification failed. Please contact support.' : null,
    ]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>
