<?php
/**
 * Registration Success / Email Verified Page
 * Schedulr — student/registration-success.php
 *
 * FIXES APPLIED:
 *  1. Uses the shared Database class (no more hardcoded PDO credentials)
 *  2. Uses Schedulr red brand colours instead of generic green
 *  3. Uses correct column names (registration_id, not id; submitted_at, not created_at)
 */

session_start();
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$registrationId = intval($_GET['id']);
$verified       = isset($_GET['verified']);

// ✅ FIX 1: Use the shared Database config class, not raw PDO with hardcoded credentials
$database = new Database();
$db       = $database->getConnection();

$stmt = $db->prepare("SELECT * FROM course_registrations WHERE registration_id = ?");
$stmt->execute([$registrationId]);
$registration = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registration) {
    die('Registration not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration <?php echo $verified ? 'Verified' : 'Confirmed'; ?> - Schedulr</title>
    <!-- ✅ FIX 2: Matching Schedulr font and brand -->
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
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(220, 38, 38, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        .logo {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 36px; color: white;
            margin: 0 auto 30px;
        }
        .status-icon { width: 100px; height: 100px; margin: 0 auto 30px; }
        h1 { font-size: 32px; font-weight: 800; color: #1a1a1a; margin-bottom: 15px; }
        .subtitle { font-size: 18px; color: #6b7280; margin-bottom: 30px; }
        .status-badge {
            display: inline-block; padding: 10px 20px;
            border-radius: 50px; font-weight: 700; font-size: 14px; margin-bottom: 20px;
        }
        .status-badge.verified { background: #d1fae5; color: #065f46; }
        .info-box {
            background: #f9fafb; padding: 20px;
            border-radius: 12px; margin: 20px 0; text-align: left;
        }
        .info-box h3 { font-size: 16px; font-weight: 700; margin-bottom: 12px; color: #1a1a1a; }
        .info-box p { color: #374151; font-size: 15px; margin-bottom: 8px; }
        .info-box p:last-child { margin-bottom: 0; }
        .info-box ol { padding-left: 20px; color: #374151; font-size: 15px; line-height: 2; }
        .btn {
            display: inline-block; padding: 15px 30px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white; border: none; border-radius: 10px;
            font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 600;
            text-decoration: none; cursor: pointer; transition: all 0.3s ease; margin-top: 20px;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(220,38,38,0.4); }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">S</div>

        <!-- Success checkmark -->
        <svg class="status-icon" viewBox="0 0 100 100" fill="none">
            <circle cx="50" cy="50" r="45" fill="#d1fae5"/>
            <path d="M30 50 L42 62 L70 34" stroke="#065f46" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <h1><?php echo $verified ? 'Email Verified!' : 'Registration Received!'; ?></h1>

        <?php if ($verified): ?>
            <span class="status-badge verified">✓ Verified</span>
            <p class="subtitle">Your email has been successfully verified</p>
        <?php endif; ?>

        <!-- ✅ FIX 3: Use correct column names from actual DB schema -->
        <div class="info-box">
            <h3>Registration Details</h3>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($registration['student_email']); ?></p>
            <p><strong>myCAMU Account:</strong> <?php echo htmlspecialchars($registration['mycamu_email']); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($registration['registration_status']); ?></p>
            <p><strong>Submitted:</strong> <?php echo date('F j, Y, g:i a', strtotime($registration['submitted_at'])); ?></p>
            <?php if ($registration['email_verified_at']): ?>
                <p><strong>Verified:</strong> <?php echo date('F j, Y, g:i a', strtotime($registration['email_verified_at'])); ?></p>
            <?php endif; ?>
        </div>

        <div class="info-box">
            <h3>What Happens Next?</h3>
            <ol>
                <li>Your registration is being processed</li>
                <li>We'll use your myCAMU credentials to register your courses</li>
                <li>You'll receive a confirmation email once complete</li>
            </ol>
        </div>

        <a href="/student/dashboard.php" class="btn">Return to Dashboard</a>
    </div>
</body>
</html>
