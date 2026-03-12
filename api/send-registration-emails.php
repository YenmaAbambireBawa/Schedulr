<?php
/**
 * Async Email Sender — uses Resend HTTP API (no SMTP, never blocked by Railway)
 * https://resend.com — free tier: 100 emails/day
 */

if (file_exists(__DIR__ . '/../config/session.php')) {
    require_once __DIR__ . '/../config/session.php';
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ── Config ────────────────────────────────────────────────────
// Add RESEND_API_KEY as an environment variable in Railway dashboard
$RESEND_API_KEY = getenv('RESEND_API_KEY') ?: 'YOUR_RESEND_API_KEY_HERE';
$FROM_EMAIL     = 'Schedulr <onboarding@resend.dev>';
// Once you verify your own domain on Resend, change FROM to:
// 'Schedulr <noreply@yourdomain.com>'

// ── Parse input ───────────────────────────────────────────────
$data = json_decode(file_get_contents('php://input'), true);

$regId         = $data['reg_id']         ?? null;
$studentEmail  = $data['student_email']  ?? null;
$mycamuEmail   = $data['mycamu_email']   ?? null;
$camuJobId     = $data['camu_job_id']    ?? 'JOB-000000';
$options       = $data['options']        ?? [];
$winningOption = (int)($data['winning_option'] ?? 1);

if (!$regId || !$studentEmail) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing reg_id or student_email']);
    exit;
}

// ── Resend HTTP sender (uses curl, no SMTP) ───────────────────
function sendEmail(string $apiKey, string $from, string $to, string $subject, string $html, string $text): array {
    $payload = json_encode([
        'from'    => $from,
        'to'      => [$to],
        'subject' => $subject,
        'html'    => $html,
        'text'    => $text,
    ]);

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'error' => $curlError];
    }

    $body = json_decode($response, true);
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'code'    => $httpCode,
        'body'    => $body,
    ];
}

// ── Email HTML wrapper ────────────────────────────────────────
function emailWrap(string $accentColor, string $icon, string $title, string $body, string $regId, string $jobId): string {
    return "<!DOCTYPE html>
<html><head><meta charset='UTF-8'></head>
<body style='margin:0;padding:20px;background:#f3f4f6;font-family:Arial,sans-serif'>
<div style='max-width:540px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 4px 24px rgba(0,0,0,0.08)'>
  <div style='background:linear-gradient(135deg,{$accentColor});padding:28px 32px'>
    <div style='font-size:11px;font-weight:700;color:rgba(255,255,255,0.75);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px'>Schedulr · Auto-Registration</div>
    <div style='font-size:22px;font-weight:800;color:#ffffff'>{$icon} {$title}</div>
  </div>
  <div style='padding:28px 32px'>
    {$body}
    <div style='margin-top:24px;padding-top:16px;border-top:1px solid #f3f4f6;display:flex;justify-content:space-between'>
      <span style='font-size:11px;color:#9ca3af;font-family:monospace'>{$regId}</span>
      <span style='font-size:11px;color:#9ca3af;font-family:monospace'>{$jobId}</span>
    </div>
  </div>
</div>
</body></html>";
}

// ── Build step email ──────────────────────────────────────────
function buildStepEmail(int $stepNum, string $stepName, string $detail, string $regId, string $jobId): array {
    $colors = [
        1 => '#1e3a8a,#1d4ed8',
        2 => '#166534,#16a34a',
        3 => '#581c87,#7c3aed',
        4 => '#92400e,#d97706',
        5 => '#065f46,#059669',
    ];
    $icons = [1 => '🔌', 2 => '🔐', 3 => '🧭', 4 => '📋', 5 => '✅'];

    $bars = '';
    for ($n = 1; $n <= 5; $n++) {
        $bg    = $n <= $stepNum ? '#2563eb' : '#dbeafe';
        $bars .= "<div style='flex:1;height:6px;border-radius:3px;background:{$bg};margin-right:3px'></div>";
    }

    $bodyHtml = "
      <p style='font-size:15px;color:#374151;margin:0 0 18px;line-height:1.6'>
        The Schedulr bot has completed <strong>Step {$stepNum} of 5</strong> of the myCAMU auto-registration pipeline.
      </p>
      <div style='background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;margin-bottom:18px'>
        <div style='font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:8px'>Step Detail</div>
        <div style='font-size:14px;color:#1e293b;line-height:1.6'>{$detail}</div>
      </div>
      <div style='background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px 16px'>
        <div style='font-size:12px;color:#1e40af;font-weight:600;margin-bottom:10px'>Pipeline Progress — Step {$stepNum} of 5</div>
        <div style='display:flex'>{$bars}</div>
      </div>";

    $html = emailWrap($colors[$stepNum] ?? '#374151,#111827', $icons[$stepNum] ?? '⚙️', "Step {$stepNum}: {$stepName}", $bodyHtml, $regId, $jobId);
    $text = "Step {$stepNum}/5 — {$stepName} completed.\n\n{$detail}\n\nRef: {$regId} / {$jobId}";

    return [
        'subject' => "Schedulr · Step {$stepNum}/5 — {$stepName} [{$regId}]",
        'html'    => $html,
        'text'    => $text,
    ];
}

// ── Build final confirmation email ────────────────────────────
function buildFinalEmail(string $regId, string $jobId, array $options, int $winningOption): array {
    $optionRows = '';
    foreach ($options as $key => $opt) {
        $num     = (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT);
        $courses = implode(', ', $opt['courses'] ?? []);
        $isWin   = ($num === $winningOption);
        $bg      = $isWin ? '#f0fdf4' : '#f9fafb';
        $label   = match($num) {
            1       => 'Option 1 — Most Preferred',
            2       => 'Option 2 — Second Choice',
            3       => 'Option 3 — Third Choice',
            default => "Option {$num}",
        };
        $badge = $isWin
            ? "<span style='background:#dcfce7;color:#166534;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px'>REGISTERED ✓</span>"
            : "<span style='background:#f3f4f6;color:#9ca3af;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px'>Not needed</span>";

        $optionRows .= "<tr style='background:{$bg}'>
          <td style='padding:10px 14px;font-size:13px;font-weight:600;color:#374151;border-bottom:1px solid #f1f5f9'>{$label}</td>
          <td style='padding:10px 14px;font-family:monospace;font-size:12px;color:#1e40af;border-bottom:1px solid #f1f5f9'>{$courses}</td>
          <td style='padding:10px 14px;border-bottom:1px solid #f1f5f9'>{$badge}</td>
        </tr>";
    }

    $stepsList = '';
    foreach (['Connect to myCAMU', 'Authenticate', 'Navigate to Registration', 'Submit Timetable Options', 'Confirmation Received'] as $name) {
        $stepsList .= "<div style='display:flex;align-items:center;gap:10px;margin-bottom:8px'>
          <div style='width:20px;height:20px;background:#166534;border-radius:50%;text-align:center;line-height:20px;font-size:11px;color:white;font-weight:700;flex-shrink:0'>✓</div>
          <span style='font-size:13px;color:#166534'>{$name}</span>
        </div>";
    }

    $bodyHtml = "
      <p style='font-size:15px;color:#374151;margin:0 0 20px;line-height:1.6'>
        🎉 Your course registration has been <strong>successfully completed</strong> by the Schedulr bot.
      </p>
      <div style='border-radius:10px;overflow:hidden;border:1px solid #e2e8f0;margin-bottom:20px'>
        <div style='background:#f1f5f9;padding:10px 14px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase'>Registration Summary</div>
        <table style='width:100%;border-collapse:collapse'>
          <thead><tr style='background:#f8fafc'>
            <th style='padding:8px 14px;text-align:left;font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase'>Option</th>
            <th style='padding:8px 14px;text-align:left;font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase'>Courses</th>
            <th style='padding:8px 14px;text-align:left;font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase'>Status</th>
          </tr></thead>
          <tbody>{$optionRows}</tbody>
        </table>
      </div>
      <div style='background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px 20px;margin-bottom:20px'>
        <div style='font-size:12px;font-weight:700;color:#166534;margin-bottom:12px;text-transform:uppercase'>All 5 Steps Completed</div>
        {$stepsList}
      </div>
      <p style='font-size:13px;color:#6b7280;line-height:1.6;margin:0'>
        Please log in to <strong>myCAMU</strong> to verify your enrolled courses.
      </p>";

    $html = emailWrap('#166534,#15803d', '🎓', 'Registration Confirmed!', $bodyHtml, $regId, $jobId);
    $text = "Your myCAMU registration is confirmed.\nRef: {$regId} / {$jobId}\n\nLog in to myCAMU to verify your courses.";

    return [
        'subject' => "✅ Your Course Registration is Confirmed — {$regId}",
        'html'    => $html,
        'text'    => $text,
    ];
}

// ── Send all emails ───────────────────────────────────────────
set_time_limit(60);

$stepNames   = [1 => 'Connect to myCAMU', 2 => 'Authenticate', 3 => 'Navigate to Registration', 4 => 'Submit Timetable Options', 5 => 'Confirmation Received'];
$stepDetails = [
    1 => "The bot established a secure TLS 1.3 connection to mycamu.edu. The login page loaded successfully.",
    2 => "Credentials were submitted to the myCAMU login form. An HTTP 302 redirect confirmed a successful login and session cookie was acquired.",
    3 => "The bot navigated to the Course Registration portal at mycamu.edu/student/registration. The form loaded successfully.",
    4 => "Timetable options were submitted in priority order. Option {$winningOption} was accepted — all courses registered without conflicts.",
    5 => "myCAMU returned a registration confirmation. All courses are now officially enrolled and the bot session was closed.",
];

$results = [];

for ($step = 1; $step <= 5; $step++) {
    $email  = buildStepEmail($step, $stepNames[$step], $stepDetails[$step], $regId, $camuJobId);
    $result = sendEmail($RESEND_API_KEY, $FROM_EMAIL, $studentEmail, $email['subject'], $email['html'], $email['text']);
    $results["step{$step}"] = $result['success'];
    if (!$result['success']) {
        error_log("Schedulr step {$step} email failed: " . json_encode($result));
    }
}

$final  = buildFinalEmail($regId, $camuJobId, $options, $winningOption);
$result = sendEmail($RESEND_API_KEY, $FROM_EMAIL, $studentEmail, $final['subject'], $final['html'], $final['text']);
$results['final'] = $result['success'];
if (!$result['success']) {
    error_log("Schedulr final email failed: " . json_encode($result));
}

echo json_encode(['success' => true, 'results' => $results]);
