<?php
/**
 * Email Verification Handler
 * Schedulr — pages/verify-registration.php
 *
 * This is the page the student lands on when they click
 * the verification link in their email.
 *
 * URL format: /pages/verify-registration.php?token=abc123&id=10
 */

require_once __DIR__ . '/../config/database.php';

$token          = isset($_GET['token']) ? trim($_GET['token']) : '';
$registrationId = isset($_GET['id'])    ? intval($_GET['id']) : 0;

// If token or ID is missing, reject immediately
if (!$token || !$registrationId) {
    redirectToSuccess(null, 'invalid');
}

try {
    $database = new Database();
    $db       = $database->getConnection();

    // Look up the registration by ID + token together
    $stmt = $db->prepare("
        SELECT registration_id, student_id, email_verified,
               verification_token, verification_token_expires,
               registration_status
        FROM course_registrations
        WHERE registration_id = ?
          AND verification_token = ?
    ");
    $stmt->execute([$registrationId, $token]);
    $reg = $stmt->fetch(PDO::FETCH_ASSOC);

    // Token + ID combo not found
    if (!$reg) {
        redirectToSuccess(null, 'invalid');
    }

    // Already verified — just send them to the success page
    if ($reg['email_verified']) {
        redirectToSuccess($registrationId, 'already');
    }

    // Check token hasn't expired
    if ($reg['verification_token_expires'] && new DateTime() > new DateTime($reg['verification_token_expires'])) {
        redirectToSuccess($registrationId, 'expired');
    }

    // ── All checks passed — mark as verified ─────────────────────────────────
    $now = date('Y-m-d H:i:s');

    $update = $db->prepare("
        UPDATE course_registrations
        SET email_verified       = 1,
            email_verified_at    = ?,
            registration_status  = 'verified',
            updated_at           = ?
        WHERE registration_id = ?
    ");
    $update->execute([$now, $now, $registrationId]);

    // Send them to the success page with ?verified in the URL
    header('Location: ../student/registration-success.php?id=' . $registrationId . '&verified');
    exit;

} catch (Exception $e) {
    error_log('verify-registration.php error: ' . $e->getMessage());
    redirectToSuccess(null, 'error');
}

/**
 * Redirect to success page or show an inline error.
 * @param int|null $id
 * @param string   $reason  invalid | expired | already | error
 */
function redirectToSuccess(?int $id, string $reason): void {
    if ($reason === 'already' && $id) {
        // Already verified — just go straight to success page
        header('Location: ../student/registration-success.php?id=' . $id . '&verified');
        exit;
    }

    // Show a simple branded error page for the other cases
    $messages = [
        'invalid' => [
            'title'   => 'Invalid Verification Link',
            'detail'  => 'This verification link is not valid. It may have been copied incorrectly.',
            'action'  => 'Go back to the registration pending page and use the Resend button to get a fresh link.',
        ],
        'expired' => [
            'title'   => 'Link Expired',
            'detail'  => 'This verification link expired after 24 hours.',
            'action'  => 'Go back to the registration pending page and click "Resend verification email" to get a new link.',
        ],
        'error'   => [
            'title'   => 'Something Went Wrong',
            'detail'  => 'An unexpected error occurred while verifying your email.',
            'action'  => 'Please try clicking the link again. If the problem continues, contact support.',
        ],
    ];

    $msg = $messages[$reason] ?? $messages['error'];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($msg['title']); ?> - Schedulr</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Outfit', sans-serif;
                background: linear-gradient(135deg, #fef2f2 0%, #fff 100%);
                min-height: 100vh;
                display: flex; align-items: center; justify-content: center;
                padding: 20px;
            }
            .container {
                background: white; border-radius: 20px; padding: 50px 40px;
                box-shadow: 0 10px 40px rgba(220,38,38,0.1);
                max-width: 560px; width: 100%; text-align: center;
            }
            .logo {
                width: 80px; height: 80px;
                background: linear-gradient(135deg, #dc2626, #991b1b);
                border-radius: 20px;
                display: flex; align-items: center; justify-content: center;
                font-weight: 800; font-size: 36px; color: white;
                margin: 0 auto 30px;
            }
            .icon { font-size: 64px; margin-bottom: 20px; }
            h1 { font-size: 28px; font-weight: 800; color: #1a1a1a; margin-bottom: 16px; }
            .detail { color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 12px; }
            .action {
                background: #fef3c7; border-left: 4px solid #f59e0b;
                padding: 16px 20px; border-radius: 10px;
                color: #92400e; font-size: 15px; line-height: 1.6;
                text-align: left; margin: 24px 0;
            }
            .btn {
                display: inline-block; padding: 14px 28px;
                background: linear-gradient(135deg, #dc2626, #b91c1c);
                color: white; border-radius: 10px; font-family: 'Outfit', sans-serif;
                font-size: 16px; font-weight: 600; text-decoration: none;
                transition: all 0.3s ease; margin-top: 8px;
            }
            .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(220,38,38,0.4); }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo">S</div>
            <div class="icon">⚠️</div>
            <h1><?php echo htmlspecialchars($msg['title']); ?></h1>
            <p class="detail"><?php echo htmlspecialchars($msg['detail']); ?></p>
            <div class="action"><?php echo htmlspecialchars($msg['action']); ?></div>
            <a href="../student/dashboard.php" class="btn">Back to Dashboard</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}