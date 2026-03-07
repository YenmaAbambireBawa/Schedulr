<?php
/**
 * Verify Registration Page
 * Handles email verification for course registrations
 */

require_once __DIR__ . '/../middleware/Auth.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$registrationId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$verificationStatus = 'invalid'; // invalid, expired, success, error
$message = '';

if (!$token || !$registrationId) {
    $verificationStatus = 'invalid';
    $message = 'Invalid verification link.';
} else {
    try {
        // Database connection
        $db = new PDO(
            "mysql:host=localhost;dbname=schedulr_db;charset=utf8mb4",
            "root",
            "",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        // Get registration by token and ID
        $stmt = $db->prepare("
            SELECT * FROM course_registrations 
            WHERE registration_id = ? 
            AND verification_token = ?
        ");
        $stmt->execute([$registrationId, $token]);
        $registration = $stmt->fetch();
        
        if (!$registration) {
            $verificationStatus = 'invalid';
            $message = 'Invalid verification link. The registration may not exist.';
        } elseif ($registration['email_verified']) {
            $verificationStatus = 'success';
            $message = 'Your email has already been verified!';
        } elseif (strtotime($registration['verification_token_expires']) < time()) {
            $verificationStatus = 'expired';
            $message = 'This verification link has expired. Please submit a new registration.';
        } else {
            // Verify the email
            $updateStmt = $db->prepare("
                UPDATE course_registrations 
                SET email_verified = 1,
                    email_verified_at = NOW(),
                    registration_status = 'verified'
                WHERE registration_id = ?
            ");
            $updateStmt->execute([$registrationId]);
            
            $verificationStatus = 'success';
            $message = 'Email verified successfully! Your registration is now being processed.';
            
            // TODO: Trigger the auto-registration process here
            // This could be done via a queue system or background job
        }
        
    } catch (PDOException $e) {
        $verificationStatus = 'error';
        $message = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Registration - Schedulr</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #fef2f2 0%, #fff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(220, 38, 38, 0.1);
            text-align: center;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 36px;
            color: white;
            margin: 0 auto 30px;
        }

        .status-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
        }

        h1 {
            font-size: 32px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 15px;
        }

        .message {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .success-box {
            background: #d1fae5;
            border-left: 4px solid #065f46;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: left;
        }

        .success-box h3 {
            color: #065f46;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .success-box p {
            color: #065f46;
            line-height: 1.6;
        }

        .error-box {
            background: #fee2e2;
            border-left: 4px solid #991b1b;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: left;
        }

        .error-box h3 {
            color: #991b1b;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .error-box p {
            color: #991b1b;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: none;
            border-radius: 10px;
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #dc2626;
            border: 2px solid #dc2626;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background: #dc2626;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">S</div>
        
        <?php if ($verificationStatus === 'success'): ?>
            <!-- Success -->
            <svg class="status-icon" viewBox="0 0 100 100" fill="none">
                <circle cx="50" cy="50" r="45" fill="#d1fae5"/>
                <path d="M30 50 L42 62 L70 34" stroke="#065f46" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            
            <h1>Verification Successful!</h1>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
            
            <div class="success-box">
                <h3>What happens next?</h3>
                <p>Your course registration is now in the processing queue. Our system will automatically attempt to register your courses in myCAMU based on your ranked timetable preferences.</p>
                <p style="margin-top: 10px;">You'll receive an email notification once the registration process is complete, typically within a few minutes.</p>
            </div>
            
            <?php if (isset($registration)): ?>
                <a href="registration-pending.php?id=<?php echo $registration['registration_id']; ?>" class="btn">View Registration Status</a>
            <?php endif; ?>
            <a href="/student/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            
        <?php elseif ($verificationStatus === 'expired'): ?>
            <!-- Expired -->
            <svg class="status-icon" viewBox="0 0 100 100" fill="none">
                <circle cx="50" cy="50" r="45" fill="#fef3c7"/>
                <text x="50" y="65" text-anchor="middle" font-size="50" fill="#f59e0b">!</text>
            </svg>
            
            <h1>Link Expired</h1>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
            
            <div class="error-box">
                <h3>Verification Link Expired</h3>
                <p>This verification link is no longer valid. Verification links expire after 24 hours for security reasons.</p>
                <p style="margin-top: 10px;">Please return to the course registration page and submit a new registration request.</p>
            </div>
            
            <a href="/student/dashboard.php" class="btn">Back to Dashboard</a>
            
        <?php else: ?>
            <!-- Invalid or Error -->
            <svg class="status-icon" viewBox="0 0 100 100" fill="none">
                <circle cx="50" cy="50" r="45" fill="#fee2e2"/>
                <line x1="35" y1="35" x2="65" y2="65" stroke="#991b1b" stroke-width="6" stroke-linecap="round"/>
                <line x1="65" y1="35" x2="35" y2="65" stroke="#991b1b" stroke-width="6" stroke-linecap="round"/>
            </svg>
            
            <h1>Verification Failed</h1>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
            
            <div class="error-box">
                <h3>Unable to Verify</h3>
                <p>We couldn't verify your registration with the provided link. This could be because:</p>
                <ul style="margin: 10px 0 0 20px; text-align: left;">
                    <li>The link is invalid or corrupted</li>
                    <li>The registration was already verified</li>
                    <li>The link has expired</li>
                </ul>
            </div>
            
            <a href="/student/dashboard.php" class="btn">Back to Dashboard</a>
        <?php endif; ?>
    </div>
</body>
</html>