<?php
/**
 * Registration Pending Page
 * Shows status while waiting for email verification
 *
 * FIXES APPLIED:
 *  1. "Resend verification email" now calls the real API endpoint
 *  2. Shows success/error feedback after resend
 */

require_once __DIR__ . '/../middleware/Auth.php';
Auth::requireStudent();

$user = Auth::user();
// Accept both numeric IDs (production) and string IDs like REG-XXXXX (dummy mode)
$registrationIdRaw = isset($_GET['id']) ? trim($_GET['id']) : '';

if ($registrationIdRaw === '') {
    header('Location: dashboard.php');
    exit;
}

// Sanitize: allow alphanumeric and hyphens only
$registrationId = preg_replace('/[^a-zA-Z0-9\-]/', '', $registrationIdRaw);

if ($registrationId === '') {
    header('Location: dashboard.php');
    exit;
}

try {
    // ── DUMMY MODE: load from JSON file instead of DB ────────────────────────
    $savePath = __DIR__ . '/../user_data/registrations.json';
    $registration = null;

    if (file_exists($savePath)) {
        $allRegistrations = json_decode(file_get_contents($savePath), true) ?? [];
        if (isset($allRegistrations[$registrationId])) {
            $raw = $allRegistrations[$registrationId];
            // Normalise field names to match the production DB column names
            $registration = [
                'registration_id'     => $raw['registration_id'],
                'student_id'          => $raw['student_id'],
                'student_email'       => $raw['student_email'],
                'mycamu_email'        => $raw['mycamu_email'],
                'registration_status' => $raw['status'] ?? 'pending',
                'email_verified'      => 0, // dummy mode skips email verification
                'submitted_at'        => $raw['submitted_at'] ?? date('Y-m-d H:i:s'),
            ];
        }
    }

    // ── PRODUCTION MODE: fall back to real DB if not found in JSON ───────────
    if (!$registration && is_numeric($registrationId)) {
        $db = new PDO(
            "mysql:host=localhost;dbname=schedulr_db;charset=utf8mb4",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );

        $stmt = $db->prepare("SELECT * FROM course_registrations WHERE registration_id = ? AND student_id = ?");
        $stmt->execute([$registrationId, $user['id']]);
        $registration = $stmt->fetch();
    }

    if (!$registration) {
        header('Location: dashboard.php');
        exit;
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Pending - Schedulr</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            width: 80px; height: 80px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 36px; color: white;
            margin: 0 auto 30px;
        }
        .status-icon { width: 100px; height: 100px; margin: 0 auto 30px; }
        .pending-icon { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        h1 { font-size: 32px; font-weight: 800; color: #1a1a1a; margin-bottom: 15px; }
        .subtitle { font-size: 18px; color: #6b7280; margin-bottom: 30px; }
        .info-box {
            background: #fef3c7; border-left: 4px solid #f59e0b;
            padding: 20px; border-radius: 10px; margin-bottom: 30px; text-align: left;
        }
        .info-box h3 { color: #92400e; font-size: 16px; font-weight: 700; margin-bottom: 10px; }
        .info-box p { color: #92400e; line-height: 1.6; }
        .status-badge {
            display: inline-block; padding: 10px 20px;
            background: #fef3c7; color: #92400e;
            border-radius: 50px; font-weight: 700; font-size: 14px; margin-bottom: 20px;
        }
        .status-badge.verified, .status-badge.completed { background: #d1fae5; color: #065f46; }
        .status-badge.processing { background: #dbeafe; color: #1e40af; }
        .btn {
            display: inline-block; padding: 15px 30px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white; border: none; border-radius: 10px;
            font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 600;
            text-decoration: none; cursor: pointer; transition: all 0.3s ease; margin-top: 20px;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(220,38,38,0.4); }
        .btn-secondary {
            background: white; color: #dc2626; border: 2px solid #dc2626;
        }
        .btn-secondary:hover { background: #dc2626; color: white; }
        .resend-area { margin-top: 20px; }
        .resend-link {
            background: none; border: none; padding: 0;
            color: #dc2626; font-weight: 600; font-size: 14px;
            cursor: pointer; font-family: 'Outfit', sans-serif;
            text-decoration: underline;
        }
        .resend-link:disabled { color: #9ca3af; cursor: not-allowed; text-decoration: none; }
        .resend-feedback {
            display: none; margin-top: 12px;
            padding: 12px 16px; border-radius: 8px; font-size: 14px; font-weight: 600;
        }
        .resend-feedback.success { background: #d1fae5; color: #065f46; }
        .resend-feedback.error   { background: #fee2e2; color: #991b1b; }
        .details {
            background: #f9fafb; padding: 20px; border-radius: 10px; margin-top: 30px; text-align: left;
        }
        .details h3 { font-size: 16px; font-weight: 700; margin-bottom: 15px; color: #1a1a1a; }
        .detail-item { padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .detail-item:last-child { border-bottom: none; }
        .detail-label { font-size: 14px; color: #6b7280; margin-bottom: 5px; }
        .detail-value { font-size: 16px; font-weight: 600; color: #1a1a1a; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">S</div>

        <?php if ($registration['email_verified']): ?>
            <svg class="status-icon" viewBox="0 0 100 100" fill="none">
                <circle cx="50" cy="50" r="45" fill="#d1fae5"/>
                <path d="M30 50 L42 62 L70 34" stroke="#065f46" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="status-badge <?php echo strtolower($registration['registration_status']); ?>">
                Status: <?php echo ucfirst($registration['registration_status']); ?>
            </span>
            <h1>Email Verified!</h1>
            <p class="subtitle">Your registration is being processed</p>
            <div class="info-box">
                <h3>What happens next?</h3>
                <p>Your course registration request is now in the queue. Our system will automatically attempt to register your courses in myCAMU based on your ranked preferences. You'll receive an email notification once the process is complete.</p>
            </div>

        <?php else: ?>
            <svg class="status-icon pending-icon" viewBox="0 0 100 100" fill="none">
                <circle cx="50" cy="50" r="45" fill="#fef3c7"/>
                <circle cx="50" cy="50" r="20" fill="#f59e0b"/>
            </svg>
            <span class="status-badge">Status: Pending Verification</span>
            <h1>Check Your Email</h1>
            <p class="subtitle">We've sent a verification link to
                <strong><?php echo htmlspecialchars($registration['student_email']); ?></strong>
            </p>
            <div class="info-box">
                <h3>Next Steps:</h3>
                <p>1. Check your inbox (and spam folder) for an email from Schedulr<br>
                   2. Click the verification link in the email<br>
                   3. Your registration will be automatically processed</p>
            </div>

            <!-- ✅ FIX: Working resend button -->
            <div class="resend-area">
                <p style="color:#6b7280;font-size:14px;">
                    Didn't receive the email?
                    <button class="resend-link" id="resend-btn"
                            onclick="resendVerification(<?php echo $registration['registration_id']; ?>)">
                        Resend verification email
                    </button>
                </p>
                <div class="resend-feedback" id="resend-feedback"></div>
            </div>
        <?php endif; ?>

        <div class="details">
            <h3>Registration Details</h3>
            <div class="detail-item">
                <div class="detail-label">Registration ID</div>
                <div class="detail-value"><?php echo htmlspecialchars(is_numeric($registration['registration_id']) ? '#' . str_pad($registration['registration_id'], 6, '0', STR_PAD_LEFT) : $registration['registration_id']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Submitted</div>
                <div class="detail-value"><?php echo date('F j, Y g:i A', strtotime($registration['submitted_at'])); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">myCAMU Email</div>
                <div class="detail-value"><?php echo htmlspecialchars($registration['mycamu_email']); ?></div>
            </div>
        </div>

        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <script>
        // Auto-refresh every 10 seconds until verified
        <?php if (!$registration['email_verified']): ?>
        setTimeout(() => { location.reload(); }, 10000);
        <?php endif; ?>

        async function resendVerification(registrationId) {
            const btn      = document.getElementById('resend-btn');
            const feedback = document.getElementById('resend-feedback');

            btn.disabled    = true;
            btn.textContent = 'Sending...';
            feedback.style.display = 'none';

            try {
                const res = await fetch('../api/resend-verification.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ registration_id: registrationId })
                });

                const data = await res.json();

                feedback.textContent    = data.message;
                feedback.className      = 'resend-feedback ' + (data.success ? 'success' : 'error');
                feedback.style.display  = 'block';

                if (data.success) {
                    btn.textContent = 'Email sent!';
                    // Re-enable after 60 seconds
                    setTimeout(() => {
                        btn.disabled    = false;
                        btn.textContent = 'Resend verification email';
                    }, 60000);
                } else {
                    btn.disabled    = false;
                    btn.textContent = 'Resend verification email';
                }

            } catch (err) {
                feedback.textContent   = 'Something went wrong. Please try again.';
                feedback.className     = 'resend-feedback error';
                feedback.style.display = 'block';
                btn.disabled           = false;
                btn.textContent        = 'Resend verification email';
            }
        }
    </script>
</body>
</html>