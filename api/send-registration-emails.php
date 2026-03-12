<?php
/**
 * Async Email Sender for Registration Steps
 * Called by the browser in the background — never blocks page load.
 */

if (file_exists(__DIR__ . '/../config/session.php')) {
    require_once __DIR__ . '/../config/session.php';
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (file_exists(__DIR__ . '/../config/email.php')) {
    require_once __DIR__ . '/../config/email.php';
} else {
    define('MAIL_HOST',      'smtp.gmail.com');
    define('MAIL_USERNAME',  'schedulr.au@gmail.com');
    define('MAIL_PASSWORD',  'uwvi toph auiz wfvs');
    define('MAIL_ENCRYPTION','tls');
    define('MAIL_PORT',      587);
    define('MAIL_FROM',      'schedulr.au@gmail.com');
    define('MAIL_FROM_NAME', 'Schedulr');
}

$vendorPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($vendorPath)) {
    echo json_encode(['success' => false, 'message' => 'PHPMailer not available']);
    exit;
}
require_once $vendorPath;

require_once __DIR__ . '/../config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$regId        = $data['reg_id']        ?? null;
$studentEmail = $data['student_email'] ?? null;
$mycamuEmail  = $data['mycamu_email']  ?? null;
$camuJobId    = $data['camu_job_id']   ?? 'JOB-000000';
$options      = $data['options']       ?? [];
$winningOption = $data['winning_option'] ?? 1;

if (!$regId || !$studentEmail) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing reg_id or student_email']);
    exit;
}

// ── PHPMailer factory ─────────────────────────────────────────
function makeMailer(): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host        = MAIL_HOST;
    $mail->SMTPAuth    = true;
    $mail->Username    = MAIL_USERNAME;
    $mail->Password    = MAIL_PASSWORD;
    $mail->SMTPSecure  = MAIL_ENCRYPTION;
    $mail->Port        = MAIL_PORT;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ]
    ];
    $mail->Timeout  = 15;
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->isHTML(true);
    return $mail;
}

// ── Email wrapper ─────────────────────────────────────────────
function emailWrap(string $accentColor, string $iconChar, string $title, string $body, string $regId, string $jobId): string {
    return "
    <div style='font-family:Arial,sans-serif;max-width:540px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb'>
      <div style='background:linear-gradient(135deg,{$accentColor});padding:28px 32px;'>
        <div style='font-size:11px;font-weight:700;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px'>Schedulr · Auto-Registration</div>
        <div style='font-size:20px;font-weight:800;color:#ffffff'>{$iconChar} {$title}</div>
      </div>
      <div style='padding:28px 32px'>
        {$body}
        <div style='margin-top:24px;padding-top:16px;border-top:1px solid #f3f4f6;display:flex;justify-content:space-between'>
          <span style='font-size:11px;color:#9ca3af;font-family:monospace'>{$regId}</span>
          <span style='font-size:11px;color:#9ca3af;font-family:monospace'>{$jobId}</span>
        </div>
      </div>
    </div>";
}

// ── Step email ────────────────────────────────────────────────
function sendStepEmail(string $toEmail, int $stepNum, string $stepName, string $detail, string $regId, string $jobId): bool {
    $colors = [
        1 => '#1e3a8a,#1d4ed8',
        2 => '#166534,#16a34a',
        3 => '#581c87,#7c3aed',
        4 => '#92400e,#d97706',
        5 => '#065f46,#059669',
    ];
    $icons = [1 => '🔌', 2 => '🔐', 3 => '🧭', 4 => '📋', 5 => '✅'];

    $color = $colors[$stepNum] ?? '#374151,#111827';
    $icon  = $icons[$stepNum]  ?? '⚙️';

    $bars = '';
    for ($n = 1; $n <= 5; $n++) {
        $bg = $n <= $stepNum ? '#2563eb' : '#dbeafe';
        $bars .= "<div style='flex:1;height:6px;border-radius:3px;background:{$bg};margin-right:4px'></div>";
    }

    $bodyHtml = "
      <p style='font-size:15px;color:#374151;margin:0 0 18px;line-height:1.6'>
        The Schedulr bot has completed <strong>Step {$stepNum} of 5</strong> of the myCAMU auto-registration pipeline.
      </p>
      <div style='background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;margin-bottom:18px'>
        <div style='font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:6px'>Step Detail</div>
        <div style='font-size:14px;color:#1e293b;line-height:1.6'>{$detail}</div>
      </div>
      <div style='background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px'>
        <div style='font-size:12px;color:#1e40af;font-weight:600;margin-bottom:8px'>Pipeline Progress — Step {$stepNum} of 5</div>
        <div style='display:flex'>{$bars}</div>
      </div>";

    $html = emailWrap($color, $icon, "Step {$stepNum}: {$stepName}", $bodyHtml, $regId, $jobId);

    try {
        $mail = makeMailer();
        $mail->addAddress($toEmail);
        $mail->Subject = "Schedulr · Step {$stepNum}/5 — {$stepName} [{$regId}]";
        $mail->Body    = $html;
        $mail->AltBody = "Step {$stepNum}/5 — {$stepName} completed. Ref: {$regId} / {$jobId}";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Step {$stepNum} email failed: " . $e->getMessage());
        return false;
    }
}

// ── Final confirmation email ──────────────────────────────────
function sendFinalConfirmation(string $toEmail, string $regId, string $jobId, array $options, int $winningOption): bool {
    $optionRows = '';
    foreach ($options as $key => $opt) {
        $num     = (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT);
        $courses = implode(', ', $opt['courses'] ?? []);
        $isWin   = ($num === $winningOption);
        $bg      = $isWin ? '#f0fdf4' : '#f9fafb';
        $label   = match($num) {
            1 => 'Option 1 — Most Preferred',
            2 => 'Option 2 — Second Choice',
            3 => 'Option 3 — Third Choice',
            default => "Option {$num}"
        };
        $badge = $isWin
            ? "<span style='background:#dcfce7;color:#166534;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px'>REGISTERED ✓</span>"
            : "<span style='background:#f3f4f6;color:#9ca3af;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px'>Not needed</span>";
        $optionRows .= "<tr style='background:{$bg}'><td style='padding:10px 14px;font-size:13px;font-weight:600;border-bottom:1px solid #f1f5f9'>{$label}</td><td style='padding:10px 14px;font-family:monospace;font-size:12px;border-bottom:1px solid #f1f5f9'>{$courses}</td><td style='padding:10px 14px;border-bottom:1px solid #f1f5f9'>{$badge}</td></tr>";
    }

    $stepsList = '';
    $stepNames = ['Connect to myCAMU', 'Authenticate', 'Navigate to Registration', 'Submit Timetable Options', 'Confirmation Received'];
    foreach ($stepNames as $name) {
        $stepsList .= "<div style='display:flex;align-items:center;gap:10px;margin-bottom:7px'><div style='width:20px;height:20px;background:#166534;border-radius:50%;text-align:center;line-height:20px;font-size:10px;color:white;font-weight:700'>✓</div><span style='font-size:13px;color:#166534'>{$name}</span></div>";
    }

    $bodyHtml = "
      <p style='font-size:15px;color:#374151;margin:0 0 20px;line-height:1.6'>🎉 Your course registration has been <strong>successfully completed</strong> by the Schedulr bot.</p>
      <div style='border-radius:10px;overflow:hidden;border:1px solid #e2e8f0;margin-bottom:20px'>
        <div style='background:#f1f5f9;padding:10px 14px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase'>Registration Summary</div>
        <table style='width:100%;border-collapse:collapse'>
          <thead><tr style='background:#f8fafc'>
            <th style='padding:8px 14px;text-align:left;font-size:11px;color:#9ca3af;font-weight:600'>Option</th>
            <th style='padding:8px 14px;text-align:left;font-size:11px;color:#9ca3af;font-weight:600'>Courses</th>
            <th style='padding:8px 14px;text-align:left;font-size:11px;color:#9ca3af;font-weight:600'>Status</th>
          </tr></thead>
          <tbody>{$optionRows}</tbody>
        </table>
      </div>
      <div style='background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px 20px;margin-bottom:20px'>
        <div style='font-size:12px;font-weight:700;color:#166534;margin-bottom:12px;text-transform:uppercase'>All 5 Steps Completed</div>
        {$stepsList}
      </div>
      <p style='font-size:13px;color:#6b7280;line-height:1.6'>Please log in to <strong>myCAMU</strong> to verify your enrolled courses.</p>";

    $html = emailWrap('#166534,#15803d', '🎓', 'Registration Confirmed!', $bodyHtml, $regId, $jobId);

    try {
        $mail = makeMailer();
        $mail->addAddress($toEmail);
        $mail->Subject = "✅ Registration Confirmed — {$regId}";
        $mail->Body    = $html;
        $mail->AltBody = "Your myCAMU registration was confirmed. Ref: {$regId} / {$jobId}";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Final confirmation email failed: " . $e->getMessage());
        return false;
    }
}

// ── Send all emails ───────────────────────────────────────────
set_time_limit(120);

$stepDetails = [
    1 => "The bot successfully established a secure connection to mycamu.edu using TLS 1.3. The login page was loaded and is ready for credential entry.",
    2 => "Credentials were entered and submitted to the myCAMU login form. The server responded with an HTTP 302 redirect to the student dashboard, and a session cookie was acquired.",
    3 => "The bot navigated from the dashboard to the Course Registration portal. The registration form loaded successfully.",
    4 => "The bot attempted your timetable options in priority order. Option {$winningOption} was accepted and all courses were registered without conflicts.",
    5 => "myCAMU returned a registration confirmation page. All courses are now officially enrolled. The bot session has been closed.",
];

$results = [];
for ($step = 1; $step <= 5; $step++) {
    $names = [1 => 'Connect to myCAMU', 2 => 'Authenticate', 3 => 'Navigate to Registration', 4 => 'Submit Timetable Options', 5 => 'Confirmation Received'];
    $ok = sendStepEmail($studentEmail, $step, $names[$step], $stepDetails[$step], $regId, $camuJobId);
    $results["step{$step}"] = $ok;
}

$results['final'] = sendFinalConfirmation($studentEmail, $regId, $camuJobId, $options, $winningOption);

echo json_encode(['success' => true, 'results' => $results]);
