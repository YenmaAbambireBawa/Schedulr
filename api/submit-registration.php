<?php
/**
 * Submit Course Registration API
 * DUMMY MODE: saves to MySQL, redirects to visual myCAMU simulator.
 */

// DON'T manually start session here — let Auth.php / config/session.php handle it
ob_start();
define('DUMMY_CAMU', true);
header('Content-Type: application/json');

require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../config/database.php';

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── Helper: send JSON and exit ────────────────────────────────
function respond($success, $message, $extra = [], $code = 200) {
    http_response_code($code);
    ob_end_clean();
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

// ── Auth check ────────────────────────────────────────────────
if (!Auth::isStudent()) {
    respond(false, 'Student access required', [], 403);
}

// ── Method check ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', [], 405);
}

// ── Parse input ───────────────────────────────────────────────
$data = json_decode(file_get_contents('php://input'), true);

// ── Validate required fields ──────────────────────────────────
foreach (['student_id', 'student_email', 'mycamu_email', 'mycamu_password', 'timetable_options'] as $field) {
    if (empty($data[$field])) {
        respond(false, "Missing required field: $field", [], 400);
    }
}

if (!isset($data['timetable_options']['option1'],
           $data['timetable_options']['option2'],
           $data['timetable_options']['option3'])) {
    respond(false, 'Invalid timetable options structure', [], 400);
}

// ── DUMMY MODE ────────────────────────────────────────────────
if (DUMMY_CAMU) {

    $mycamuPassword = $data['mycamu_password'];

    if (strlen($mycamuPassword) < 6) {
        respond(false, 'myCAMU authentication failed: password too short.', [], 401);
    }
    if ($mycamuPassword === 'wrong123') {
        respond(false, 'myCAMU authentication failed: invalid credentials.', ['camu_error_code' => 'AUTH_INVALID'], 401);
    }

    $registrationId = 'REG-' . strtoupper(substr(md5(uniqid($data['student_id'], true)), 0, 10));
    $camuJobId      = 'JOB-' . rand(100000, 999999);

    // Save to MySQL
try {
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        $stmt = $db->prepare("
            INSERT INTO course_registrations
                (student_id, student_email, mycamu_email, mycamu_password_encrypted,
                 timetable_options, registration_status, submitted_at)
            VALUES (?, ?, ?, '', ?, 'pending', NOW())
        ");
        $stmt->execute([
            $data['student_id'],
            $data['student_email'],
            $data['mycamu_email'],
            json_encode($data['timetable_options']),
        ]);
        
        // Get the auto-incremented ID
        $dbInsertId = $db->lastInsertId();
    }
} catch (\Exception $e) {
    error_log("Registration DB insert failed: " . $e->getMessage());
}

    respond(true, 'Registration submitted. Redirecting to myCAMU simulator...', [
    'registration_id' => $dbInsertId ?? $registrationId,
    'email_sent'      => false,
    'dummy_mode'      => true,
    'simulator_url'   => '/student/camu-simulator.php?id=' . ($dbInsertId ?? $registrationId),
]);
}

// ── PRODUCTION MODE ───────────────────────────────────────────
try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        respond(false, 'Database connection failed', [], 500);
    }

    // Encrypt myCAMU password
    if (!getenv('ENCRYPTION_KEY')) {
        putenv('ENCRYPTION_KEY=' . bin2hex(random_bytes(32)));
    }
    $key       = getenv('ENCRYPTION_KEY');
    $iv        = random_bytes(16);
    $encrypted = base64_encode($iv . openssl_encrypt($data['mycamu_password'], 'aes-256-cbc', $key, 0, $iv));

    $verificationToken = bin2hex(random_bytes(32));
    $tokenExpiry       = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO course_registrations
            (student_id, student_email, mycamu_email, mycamu_password_encrypted,
             registration_status, verification_token, verification_token_expires,
             timetable_options, submitted_at)
        VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $data['student_id'],
        $data['student_email'],
        $data['mycamu_email'],
        $encrypted,
        $verificationToken,
        $tokenExpiry,
        json_encode($data['timetable_options']),
    ]);

    $registrationId = $db->lastInsertId();
    $db->commit();

    // Send verification email
    $emailSent = false;
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $baseUrl = 'https://' . $_SERVER['HTTP_HOST'];
            $link    = $baseUrl . '/pages/verify-registration.php?token=' . $verificationToken . '&id=' . $registrationId;

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'schedulr.au@gmail.com';
            $mail->Password   = 'uwvi toph auiz wfvs';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->Timeout    = 10;
            $mail->setFrom('schedulr.au@gmail.com', 'Schedulr');
            $mail->addAddress($data['student_email']);
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Course Registration - Schedulr';
            $mail->Body    = "<p>Please verify your registration: <a href='{$link}'>{$link}</a></p>";
            $mail->AltBody = "Verify your registration: {$link}";
            $mail->send();
            $emailSent = true;
        } catch (Exception $e) {
            error_log("Verification email failed: " . $e->getMessage());
        }
    }

    respond(true, 'Registration submitted successfully. Please check your email to verify.', [
        'registration_id' => $registrationId,
        'email_sent'      => $emailSent,
    ]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    respond(false, $e->getMessage(), [], 500);
}
?>
