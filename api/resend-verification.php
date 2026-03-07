<?php
/**
 * Resend Verification Email
 * Schedulr — api/resend-verification.php
 *
 * Called via POST from the "Resend verification email" button
 * on student/registration-pending.php
 */

require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Mailer.php';

header('Content-Type: application/json');

Auth::requireStudent();
$user = Auth::user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input          = json_decode(file_get_contents('php://input'), true);
$registrationId = isset($input['registration_id']) ? intval($input['registration_id']) : 0;

if (!$registrationId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing registration ID']);
    exit;
}

try {
    $database = new Database();
    $db       = $database->getConnection();

    // Fetch the registration — must belong to this student
    $stmt = $db->prepare("
        SELECT r.registration_id, r.student_email, r.verification_token,
               r.verification_token_expires, r.email_verified,
               u.full_name
        FROM course_registrations r
        JOIN users u ON u.user_id = r.student_id
        WHERE r.registration_id = ?
          AND r.student_id = ?
    ");
    $stmt->execute([$registrationId, $user['id']]);
    $reg = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reg) {
        echo json_encode(['success' => false, 'message' => 'Registration not found']);
        exit;
    }

    if ($reg['email_verified']) {
        echo json_encode(['success' => false, 'message' => 'Email is already verified']);
        exit;
    }

    // ── Rate limit: block resend if token was created less than 2 minutes ago ──
    if ($reg['verification_token_expires']) {
        // Token expiry is 24h after it was created, so subtract 24h to get creation time
        $createdAt     = (new DateTime($reg['verification_token_expires']))->modify('-24 hours');
        $twoMinutesAgo = (new DateTime())->modify('-2 minutes');

        // If the token was created AFTER two minutes ago, it's too soon to resend
        if ($createdAt > $twoMinutesAgo) {
            echo json_encode(['success' => false, 'message' => 'Please wait a moment before requesting another email']);
            exit;
        }
    }

    // Generate a fresh token with a new 24-hour window
    $newToken   = bin2hex(random_bytes(32));
    $newExpires = (new DateTime())->modify('+24 hours')->format('Y-m-d H:i:s');

    $update = $db->prepare("
        UPDATE course_registrations
        SET verification_token         = ?,
            verification_token_expires = ?
        WHERE registration_id = ?
    ");
    $update->execute([$newToken, $newExpires, $registrationId]);

    // Send the email
    $sent = Mailer::resendVerification(
        $reg['student_email'],
        $reg['full_name'],
        $newToken,
        $registrationId
    );

    if ($sent) {
        echo json_encode(['success' => true, 'message' => 'Verification email resent — please check your inbox']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email — please try again shortly']);
    }

} catch (Exception $e) {
    error_log('resend-verification.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error — please try again']);
}