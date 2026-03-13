<?php
/**
 * myCAMU Simulator Page
 * Visual simulation of the auto-registration bot operating inside myCAMU.
 * Fixed: removed camu_job_id DB dependency, safe email config, safe vendor load.
 */

// Start session safely
if (file_exists(__DIR__ . '/../config/session.php')) {
    require_once __DIR__ . '/../config/session.php';
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Emails sent async via /api/send-registration-emails.php (called by JS)
// No SMTP code here — avoids Railway timeout issues.

// Require database
require_once __DIR__ . '/../config/database.php';

// ── Load registration from DB ─────────────────────────────────
$regId = $_GET['id'] ?? null;
$registration = null;

if ($regId) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        if ($db) {
            $stmt = $db->prepare("SELECT * FROM course_registrations WHERE registration_id = ?");
            $stmt->execute([$regId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $registration = [
                    'student_email'     => $row['student_email'],
                    'mycamu_email'      => $row['mycamu_email'],
                    'camu_job_id'       => 'JOB-' . strtoupper(substr(md5($regId), 0, 6)),
                    'timetable_options' => json_decode($row['timetable_options'], true),
                ];
            }
        }
    } catch (Exception $e) {
        error_log("camu-simulator DB error: " . $e->getMessage());
    }
}

$options      = $registration['timetable_options'] ?? [];
$studentEmail = $registration['student_email']     ?? 'student@example.com';
$mycamuEmail  = $registration['mycamu_email']      ?? 'student@mycamu.edu';
$camuJobId    = $registration['camu_job_id']       ?? 'JOB-000000';

$optionsJson = json_encode($options);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>myCAMU Simulator — Schedulr</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
  :root {
    --red:   #dc2626;
    --red2:  #991b1b;
    --green: #16a34a;
    --amber: #d97706;
    --blue:  #2563eb;
    --bg:    #0f1117;
    --panel: #181c27;
    --border:#272d3d;
    --text:  #e2e8f0;
    --muted: #6b7280;
    --mono:  'JetBrains Mono', monospace;
    --sans:  'Outfit', sans-serif;
  }
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:var(--sans); background:var(--bg); color:var(--text); min-height:100vh; overflow-x:hidden; }

  /* TOP BAR */
  .topbar { display:flex; justify-content:space-between; align-items:center; padding:16px 32px; background:var(--panel); border-bottom:1px solid var(--border); }
  .topbar-left { display:flex; align-items:center; gap:14px; }
  .logo-badge { background:linear-gradient(135deg,var(--red),var(--red2)); width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:18px; color:white; }
  .topbar h1 { font-size:20px; font-weight:700; }
  .topbar h1 span { color:var(--red); }
  .job-badge { font-family:var(--mono); font-size:11px; background:rgba(220,38,38,0.12); border:1px solid rgba(220,38,38,0.3); color:#fca5a5; padding:4px 10px; border-radius:20px; letter-spacing:0.5px; }

  /* LAYOUT */
  .layout { display:grid; grid-template-columns:1fr 380px; gap:0; min-height:calc(100vh - 65px); }

  /* BROWSER PANEL */
  .browser-panel { border-right:1px solid var(--border); display:flex; flex-direction:column; }
  .browser-chrome { background:#1e2330; border-bottom:1px solid var(--border); padding:10px 16px; display:flex; align-items:center; gap:10px; }
  .browser-dots { display:flex; gap:6px; }
  .browser-dots span { width:12px; height:12px; border-radius:50%; }
  .browser-dots span:nth-child(1) { background:#ef4444; }
  .browser-dots span:nth-child(2) { background:#f59e0b; }
  .browser-dots span:nth-child(3) { background:#22c55e; }
  .browser-url { flex:1; background:#0f1117; border:1px solid var(--border); border-radius:6px; padding:6px 12px; font-family:var(--mono); font-size:12px; color:#94a3b8; display:flex; align-items:center; gap:8px; overflow:hidden; white-space:nowrap; }
  .url-lock { color:var(--green); font-size:12px; }
  #urlText { transition:opacity 0.3s; }
  .browser-content { flex:1; position:relative; overflow:hidden; }

  /* SCREENS */
  .camu-screen { position:absolute; inset:0; display:flex; flex-direction:column; opacity:0; pointer-events:none; transition:opacity 0.5s ease; }
  .camu-screen.active { opacity:1; pointer-events:auto; }

  /* Screen 0: Loading */
  #screen-loading { background:#fff; align-items:center; justify-content:center; gap:16px; }
  .camu-spinner { width:48px; height:48px; border:4px solid #e5e7eb; border-top-color:#1a56db; border-radius:50%; animation:spin 0.8s linear infinite; }
  @keyframes spin { to { transform:rotate(360deg); } }

  /* Screen 1: Login */
  #screen-login { background:linear-gradient(160deg,#1e3a8a 0%,#1e40af 60%,#1d4ed8 100%); align-items:center; justify-content:center; padding:40px; }
  .camu-login-box { background:white; border-radius:12px; padding:36px 40px; width:100%; max-width:380px; box-shadow:0 25px 60px rgba(0,0,0,0.4); }
  .camu-logo-row { display:flex; align-items:center; gap:10px; margin-bottom:24px; }
  .camu-logo-icon { width:44px; height:44px; background:linear-gradient(135deg,#1e3a8a,#1d4ed8); border-radius:10px; display:flex; align-items:center; justify-content:center; color:white; font-weight:800; font-size:18px; }
  .camu-logo-text { font-size:22px; font-weight:800; color:#1e3a8a; }
  .camu-logo-text span { color:#1d4ed8; }
  .camu-login-label { font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; margin-top:14px; }
  .camu-login-input { width:100%; border:2px solid #e5e7eb; border-radius:8px; padding:10px 14px; font-family:var(--sans); font-size:14px; color:#111; transition:border-color 0.2s; outline:none; }
  .camu-login-input.typing { border-color:#1d4ed8; }
  .camu-login-input.filled { border-color:#16a34a; background:#f0fdf4; }
  .camu-login-btn { margin-top:20px; width:100%; background:linear-gradient(135deg,#1e3a8a,#1d4ed8); color:white; border:none; border-radius:8px; padding:12px; font-family:var(--sans); font-size:15px; font-weight:700; cursor:default; opacity:0.5; transition:opacity 0.4s; }
  .camu-login-btn.ready   { opacity:1; }
  .camu-login-btn.loading { opacity:1; background:linear-gradient(135deg,#166534,#16a34a); }

  /* Screen 2: Portal */
  #screen-portal { background:#f8fafc; overflow-y:auto; }
  .camu-portal-header { background:linear-gradient(135deg,#1e3a8a,#1d4ed8); padding:16px 24px; color:white; display:flex; align-items:center; justify-content:space-between; flex-shrink:0; }
  .camu-portal-title { font-size:16px; font-weight:700; }
  .camu-portal-user  { font-size:12px; opacity:0.8; font-family:var(--mono); }
  .camu-portal-nav   { background:#1e40af; padding:0 24px; display:flex; flex-shrink:0; }
  .camu-nav-item { padding:10px 16px; font-size:12px; font-weight:600; color:rgba(255,255,255,0.6); cursor:default; border-bottom:3px solid transparent; }
  .camu-nav-item.active { color:white; border-bottom-color:#60a5fa; }
  .camu-portal-body { padding:20px 24px; flex:1; }
  .camu-portal-section { background:white; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:16px; overflow:hidden; opacity:0; transform:translateY(10px); transition:opacity 0.4s ease,transform 0.4s ease; }
  .camu-portal-section.visible { opacity:1; transform:translateY(0); }
  .camu-section-header { background:#f1f5f9; padding:12px 18px; font-size:13px; font-weight:700; color:#334155; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; }
  .camu-section-badge { font-size:11px; padding:2px 8px; border-radius:20px; font-weight:600; }
  .badge-pending { background:#fef3c7; color:#92400e; }
  .badge-success { background:#dcfce7; color:#166534; }
  .camu-section-body { padding:14px 18px; }
  .camu-course-row { display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid #f1f5f9; font-size:12px; color:#374151; }
  .camu-course-row:last-child { border-bottom:none; }
  .camu-course-code { font-family:var(--mono); font-weight:600; color:#1e40af; min-width:80px; }
  .camu-course-name { flex:1; }
  .camu-course-status { width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0; transition:all 0.3s; }
  .cs-pending  { background:#f3f4f6; color:#9ca3af; }
  .cs-spinning { background:#dbeafe; border:2px solid #93c5fd; animation:spin 0.8s linear infinite; }
  .cs-done     { background:#dcfce7; color:#166534; }

  /* Screen 3: Confirmed */
  #screen-confirmed { background:#f0fdf4; align-items:center; justify-content:center; flex-direction:column; gap:20px; padding:40px; }
  .confirmed-icon { width:80px; height:80px; background:var(--green); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:36px; animation:pulse-green 2s infinite; }
  @keyframes pulse-green { 0%,100%{box-shadow:0 0 0 0 rgba(22,163,74,0.4)} 50%{box-shadow:0 0 0 20px rgba(22,163,74,0)} }
  .confirmed-title { font-size:22px; font-weight:800; color:#166534; text-align:center; }
  .confirmed-sub   { font-size:14px; color:#4b5563; text-align:center; max-width:320px; line-height:1.6; }
  .confirmed-id    { font-family:var(--mono); font-size:13px; background:white; border:1px solid #bbf7d0; padding:8px 16px; border-radius:8px; color:#166534; }

  /* Email toast */
  .email-toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%) translateY(80px); background:#181c27; border:1px solid #272d3d; border-radius:12px; padding:12px 20px; display:flex; align-items:center; gap:12px; font-size:13px; color:#e2e8f0; box-shadow:0 8px 32px rgba(0,0,0,0.4); transition:transform 0.4s cubic-bezier(0.34,1.56,0.64,1); z-index:999; white-space:nowrap; }
  .email-toast.show { transform:translateX(-50%) translateY(0); }
  .email-toast-icon { font-size:18px; }
  .email-toast-text strong { color:#86efac; }

  /* LOG PANEL */
  .log-panel { display:flex; flex-direction:column; background:var(--panel); }
  .log-header { padding:16px 20px 12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
  .log-title { font-size:13px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.8px; }
  .live-dot { width:8px; height:8px; border-radius:50%; background:var(--green); box-shadow:0 0 6px var(--green); animation:blink 1.2s ease-in-out infinite; }
  @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }
  .log-scroll { flex:1; overflow-y:auto; padding:14px 0; font-family:var(--mono); font-size:12px; }
  .log-scroll::-webkit-scrollbar { width:4px; }
  .log-scroll::-webkit-scrollbar-thumb { background:var(--border); border-radius:2px; }
  .log-entry { padding:4px 20px; display:flex; gap:10px; align-items:flex-start; opacity:0; animation:fadeInLog 0.3s ease forwards; }
  @keyframes fadeInLog { to { opacity:1; } }
  .log-time { color:var(--muted); flex-shrink:0; font-size:11px; margin-top:1px; }
  .log-msg { line-height:1.5; }
  .log-msg.info    { color:#93c5fd; }
  .log-msg.success { color:#86efac; }
  .log-msg.warn    { color:#fcd34d; }
  .log-msg.system  { color:#c4b5fd; }
  .log-msg.muted   { color:var(--muted); }
  .log-msg.email   { color:#f9a8d4; }

  /* STATUS TRACK */
  .status-track { border-top:1px solid var(--border); padding:16px 20px; }
  .track-label { font-size:11px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:0.6px; margin-bottom:12px; }
  .track-steps { display:flex; flex-direction:column; gap:8px; }
  .track-step { display:flex; align-items:center; gap:10px; font-size:12px; color:var(--muted); transition:color 0.3s; }
  .track-step.done   { color:#86efac; }
  .track-step.active { color:#93c5fd; }
  .step-icon { width:18px; height:18px; border-radius:50%; background:var(--border); display:flex; align-items:center; justify-content:center; font-size:9px; flex-shrink:0; transition:all 0.3s; }
  .track-step.done   .step-icon { background:#166534; color:white; }
  .track-step.active .step-icon { background:transparent; border:2px solid #93c5fd; animation:spin 1s linear infinite; }

  /* OPTION RESULT CARDS */
  .result-cards { border-top:1px solid var(--border); padding:16px 20px; }
  .result-card { background:#0f1117; border:1px solid var(--border); border-radius:8px; padding:12px 14px; margin-bottom:8px; opacity:0; transition:opacity 0.4s,border-color 0.3s; }
  .result-card.visible     { opacity:1; }
  .result-card.selected    { border-color:#16a34a; }
  .result-card.failed-card { border-color:#dc2626; }
  .rc-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:6px; }
  .rc-label  { font-size:12px; font-weight:700; color:var(--text); }
  .rc-badge  { font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; font-family:var(--mono); }
  .rc-badge.trying   { background:rgba(37,99,235,0.2);  color:#93c5fd; }
  .rc-badge.ok       { background:rgba(22,163,74,0.2);  color:#86efac; }
  .rc-badge.conflict { background:rgba(220,38,38,0.2);  color:#fca5a5; }
  .rc-courses { font-size:11px; color:var(--muted); font-family:var(--mono); }

  /* BACK BUTTON */
  .back-btn { margin:16px 20px; display:none; align-items:center; gap:8px; background:linear-gradient(135deg,var(--red),var(--red2)); color:white; border:none; border-radius:8px; padding:10px 16px; font-family:var(--sans); font-size:13px; font-weight:700; cursor:pointer; text-decoration:none; transition:transform 0.2s; }
  .back-btn:hover { transform:translateY(-2px); }
  .back-btn.show  { display:flex; }

  /* No registration state */
  .no-reg { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; gap:16px; padding:40px; text-align:center; color:var(--muted); }
  .no-reg h2 { color:var(--text); font-size:20px; }

  /* Mobile */
  @media (max-width: 768px) {
    .topbar { padding: 12px 16px; flex-wrap: wrap; gap: 8px; }
    .topbar h1 { font-size: 15px; }
    .job-badge { font-size: 10px; padding: 3px 8px; }

    .layout { grid-template-columns: 1fr; grid-template-rows: auto auto; }

    .browser-panel { border-right: none; border-bottom: 1px solid var(--border); min-height: 420px; }
    .browser-content { min-height: 360px; }

    .camu-login-box { padding: 24px 20px; max-width: 100%; }

    .camu-portal-nav { overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
    .camu-portal-nav::-webkit-scrollbar { display: none; }
    .camu-nav-item { font-size: 11px; padding: 10px 12px; }

    .log-panel { min-height: 300px; max-height: 380px; }
    .log-scroll { max-height: 150px; }

    .result-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .result-cards > .track-label { grid-column: 1 / -1; }
    .result-card { margin-bottom: 0; }

    .status-track { padding: 12px 16px; }
    .track-steps { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }

    .confirmed-icon { width: 60px; height: 60px; font-size: 28px; }
    .confirmed-title { font-size: 18px; }

    #screen-login { padding: 20px; }
  }

  @media (max-width: 480px) {
    .result-cards { grid-template-columns: 1fr; }
    .track-steps { grid-template-columns: 1fr; }
    .topbar-left gap { gap: 8px; }
  }
</style>
</head>
<body>

<div class="email-toast" id="emailToast">
  <span class="email-toast-icon">📧</span>
  <span class="email-toast-text" id="emailToastText"></span>
</div>

<div class="topbar">
  <div class="topbar-left">
    <div class="logo-badge">S</div>
    <h1>Schedulr <span>×</span> myCAMU Simulator</h1>
  </div>
  <div style="display:flex;align-items:center;gap:12px">
    <span class="job-badge"><?= htmlspecialchars($camuJobId) ?></span>
    <span class="job-badge" style="color:#86efac;border-color:rgba(22,163,74,0.3);background:rgba(22,163,74,0.1)">LIVE</span>
  </div>
</div>

<div class="layout">

  <!-- LEFT: myCAMU Browser -->
  <div class="browser-panel">
    <div class="browser-chrome">
      <div class="browser-dots"><span></span><span></span><span></span></div>
      <div class="browser-url">
        <span class="url-lock">🔒</span>
        <span id="urlText">mycamu.edu</span>
      </div>
    </div>

    <div class="browser-content">

      <?php if (!$registration): ?>
      <!-- No registration found -->
      <div class="camu-screen active" id="screen-loading" style="background:#0f1117">
        <div class="no-reg">
          <div style="font-size:48px">⚠️</div>
          <h2>Registration Not Found</h2>
          <p>No registration data found for ID: <code style="color:#fca5a5"><?= htmlspecialchars($regId ?? 'none') ?></code></p>
          <a href="dashboard.php" style="color:#93c5fd;text-decoration:underline;margin-top:10px">← Back to Dashboard</a>
        </div>
      </div>
      <?php else: ?>

      <!-- Screen 0: Loading -->
      <div class="camu-screen active" id="screen-loading">
        <div class="camu-spinner"></div>
        <div style="color:#6b7280;font-size:14px">Connecting to myCAMU...</div>
      </div>

      <!-- Screen 1: Login -->
      <div class="camu-screen" id="screen-login">
        <div class="camu-login-box">
          <div class="camu-logo-row">
            <div class="camu-logo-icon">C</div>
            <div class="camu-logo-text">my<span>CAMU</span></div>
          </div>
          <div style="font-size:20px;font-weight:800;color:#1e3a8a;margin-bottom:4px">Student Portal</div>
          <div style="font-size:13px;color:#6b7280;margin-bottom:20px">Sign in to continue</div>
          <div class="camu-login-label">Email Address</div>
          <input class="camu-login-input" id="camSimEmail" type="email" readonly placeholder="Loading...">
          <div class="camu-login-label">Password</div>
          <input class="camu-login-input" id="camSimPass" type="password" readonly placeholder="">
          <button class="camu-login-btn" id="camLoginBtn">Sign In</button>
        </div>
      </div>

      <!-- Screen 2: Portal -->
      <div class="camu-screen" id="screen-portal">
        <div class="camu-portal-header">
          <div>
            <div class="camu-portal-title">myCAMU Student Portal</div>
            <div class="camu-portal-user"><?= htmlspecialchars($mycamuEmail) ?></div>
          </div>
          <div style="font-size:12px;background:rgba(255,255,255,0.15);padding:4px 10px;border-radius:6px">Semester Registration</div>
        </div>
        <div class="camu-portal-nav">
          <div class="camu-nav-item">Dashboard</div>
          <div class="camu-nav-item active">Course Registration</div>
          <div class="camu-nav-item">My Schedule</div>
          <div class="camu-nav-item">Grades</div>
        </div>
        <div class="camu-portal-body" id="portalBody"></div>
      </div>

      <!-- Screen 3: Confirmed -->
      <div class="camu-screen" id="screen-confirmed">
        <div class="confirmed-icon">✓</div>
        <div class="confirmed-title">Registration Confirmed!</div>
        <div class="confirmed-sub">Your courses have been successfully registered in myCAMU. Check your email for a full summary.</div>
        <div class="confirmed-id"><?= htmlspecialchars($regId ?? 'REG-UNKNOWN') ?></div>
      </div>

      <?php endif; ?>
    </div>
  </div>

  <!-- RIGHT: Log + Status Panel -->
  <div class="log-panel">
    <div class="log-header">
      <span class="log-title">Bot Activity Log</span>
      <span class="live-dot"></span>
    </div>

    <div class="log-scroll" id="logScroll">
      <?php if (!$registration): ?>
      <div class="log-entry" style="opacity:1">
        <span class="log-time"><?= date('H:i:s') ?></span>
        <span class="log-msg warn">No registration data found for ID: <?= htmlspecialchars($regId ?? 'none') ?></span>
      </div>
      <?php endif; ?>
    </div>

    <div class="status-track">
      <div class="track-label">Pipeline</div>
      <div class="track-steps">
        <div class="track-step" id="step-connect"><div class="step-icon">1</div><span>Connect to myCAMU</span></div>
        <div class="track-step" id="step-login"><div class="step-icon">2</div><span>Authenticate</span></div>
        <div class="track-step" id="step-navigate"><div class="step-icon">3</div><span>Navigate to Registration</span></div>
        <div class="track-step" id="step-submit"><div class="step-icon">4</div><span>Submit Timetable Options</span></div>
        <div class="track-step" id="step-confirm"><div class="step-icon">5</div><span>Confirmation</span></div>
      </div>
    </div>

    <div class="result-cards">
      <div class="track-label" style="margin-bottom:10px">Timetable Options</div>
      <div class="result-card" id="rc-option1">
        <div class="rc-header"><span class="rc-label">Option 1 — Most Preferred</span><span class="rc-badge trying" id="rc1-badge">Queued</span></div>
        <div class="rc-courses" id="rc1-courses">—</div>
      </div>
      <div class="result-card" id="rc-option2">
        <div class="rc-header"><span class="rc-label">Option 2 — Second Choice</span><span class="rc-badge trying" id="rc2-badge">Queued</span></div>
        <div class="rc-courses" id="rc2-courses">—</div>
      </div>
      <div class="result-card" id="rc-option3">
        <div class="rc-header"><span class="rc-label">Option 3 — Third Choice</span><span class="rc-badge trying" id="rc3-badge">Queued</span></div>
        <div class="rc-courses" id="rc3-courses">—</div>
      </div>
    </div>

    <a href="dashboard.php" class="back-btn" id="backBtn">← Back to Schedulr</a>
  </div>
</div>

<?php if ($registration): ?>
<script>
const REG_ID        = <?= json_encode($regId) ?>;
const CAMU_EMAIL    = <?= json_encode($mycamuEmail) ?>;
const STUDENT_EMAIL = <?= json_encode($studentEmail) ?>;
const OPTIONS       = <?= $optionsJson ?>;

function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }
function ts() { return new Date().toTimeString().slice(0,8); }

function log(msg, type = 'info') {
  const el = document.getElementById('logScroll');
  const entry = document.createElement('div');
  entry.className = 'log-entry';
  entry.innerHTML = `<span class="log-time">${ts()}</span><span class="log-msg ${type}">${msg}</span>`;
  el.appendChild(entry);
  el.scrollTop = el.scrollHeight;
}

function setUrl(url) {
  const el = document.getElementById('urlText');
  el.style.opacity = '0';
  setTimeout(() => { el.textContent = url; el.style.opacity = '1'; }, 200);
}

function stepDone(id) {
  const el = document.getElementById('step-' + id);
  if (el) { el.classList.remove('active'); el.classList.add('done'); el.querySelector('.step-icon').textContent = '✓'; }
}
function stepActive(id) {
  const el = document.getElementById('step-' + id);
  if (el) { el.classList.add('active'); el.querySelector('.step-icon').textContent = ''; }
}

function typeInto(inputId, text, delayPer = 60) {
  return new Promise(async resolve => {
    const el = document.getElementById(inputId);
    el.classList.add('typing');
    for (let i = 0; i < text.length; i++) {
      el.value += text[i];
      await sleep(delayPer + Math.random() * 40);
    }
    el.classList.remove('typing');
    el.classList.add('filled');
    resolve();
  });
}

function buildOptionCourses(optionKey) {
  const opt = OPTIONS[optionKey];
  if (!opt || !opt.courses) return '—';
  return opt.courses.join(' · ');
}

function showSection(id, delay = 0) {
  return new Promise(resolve => {
    setTimeout(() => {
      const el = document.getElementById(id);
      if (el) el.classList.add('visible');
      resolve();
    }, delay);
  });
}

function showEmailToast(stepNum, stepName) {
  const toast = document.getElementById('emailToast');
  const text  = document.getElementById('emailToastText');
  text.innerHTML = `📬 Email sent — <strong>Step ${stepNum}: ${stepName}</strong> → ${STUDENT_EMAIL}`;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3200);
}

function showFinalEmailToast() {
  const toast = document.getElementById('emailToast');
  const text  = document.getElementById('emailToastText');
  text.innerHTML = `🎉 Final confirmation email sent to <strong>${STUDENT_EMAIL}</strong>`;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 4000);
}

function buildPortalSections(resultOptionIndex) {
  const body = document.getElementById('portalBody');
  body.innerHTML = '';
  for (let i = 1; i <= 3; i++) {
    const opt     = OPTIONS['option' + i] || {};
    const courses = opt.courses || [];
    const label   = i === 1 ? 'Most Preferred' : i === 2 ? 'Second Choice' : 'Third Choice';
    const isWin   = (i === resultOptionIndex);
    const sec     = document.createElement('div');
    sec.className = 'camu-portal-section';
    sec.id        = 'portal-sec-' + i;

    const courseRows = courses.map(code => `
      <div class="camu-course-row">
        <span class="camu-course-code">${code}</span>
        <span class="camu-course-name">${code}</span>
        <span class="camu-course-status cs-pending" id="cs-${i}-${code.replace(/\s/g,'_')}">·</span>
      </div>`).join('');

    sec.innerHTML = `
      <div class="camu-section-header">
        Option ${i} — ${label}
        <span class="camu-section-badge ${isWin ? 'badge-success' : 'badge-pending'}" id="sec${i}-badge">${isWin ? 'Registered' : 'Pending'}</span>
      </div>
      <div class="camu-section-body">${courseRows || '<div style="color:#9ca3af;font-size:12px;padding:8px 0">No courses</div>'}</div>`;
    body.appendChild(sec);
  }
}

async function animateRegisterCourses(optionIndex) {
  const opt     = OPTIONS['option' + optionIndex] || {};
  const courses = opt.courses || [];
  for (const code of courses) {
    const key = code.replace(/\s/g, '_');
    const el  = document.getElementById(`cs-${optionIndex}-${key}`);
    if (!el) continue;
    el.className   = 'camu-course-status cs-spinning';
    el.textContent = '';
    await sleep(600 + Math.random() * 500);
    el.className   = 'camu-course-status cs-done';
    el.textContent = '✓';
    log(`  Registered: ${code}`, 'success');
    await sleep(200);
  }
  const badge = document.getElementById(`sec${optionIndex}-badge`);
  if (badge) { badge.textContent = 'Registered'; badge.className = 'camu-section-badge badge-success'; }
}

async function runSimulation() {
  // Populate result cards
  for (let i = 1; i <= 3; i++) {
    document.getElementById('rc' + i + '-courses').textContent = buildOptionCourses('option' + i);
    document.getElementById('rc-option' + i).classList.add('visible');
  }

  const opt1courses   = (OPTIONS.option1 || {}).courses || [];
  const winningOption = opt1courses.length > 0 ? 1 : 2;

  // Phase 1: Connect
  log('Initialising Schedulr automation agent...', 'system');
  await sleep(600);
  log('Target: mycamu.edu/student/login', 'muted');
  stepActive('connect');
  setUrl('mycamu.edu');
  await sleep(900);
  setUrl('mycamu.edu/student/login');
  await sleep(700);
  log('Connected. TLS 1.3 handshake OK.', 'success');
  log('Page loaded: myCAMU Student Login', 'info');
  stepDone('connect');
  log('📧 Step 1 email dispatched → ' + STUDENT_EMAIL, 'email');
  showEmailToast(1, 'Connect to myCAMU');
  await sleep(500);

  document.querySelectorAll('.camu-screen').forEach(s => s.classList.remove('active'));
  document.getElementById('screen-login').classList.add('active');
  await sleep(600);

  // Phase 2: Login
  stepActive('login');
  log('Filling credentials...', 'info');
  await sleep(300);
  await typeInto('camSimEmail', CAMU_EMAIL, 55);
  log(`Email entered: ${CAMU_EMAIL}`, 'muted');
  await sleep(400);
  await typeInto('camSimPass', '••••••••', 80);
  log('Password entered (masked).', 'muted');
  await sleep(500);
  document.getElementById('camLoginBtn').classList.add('ready');
  await sleep(400);
  document.getElementById('camLoginBtn').textContent = 'Signing in...';
  document.getElementById('camLoginBtn').classList.add('loading');
  log('Submitting login form...', 'info');
  await sleep(1200);
  log('HTTP 302 → /student/dashboard', 'success');
  log('Session cookie acquired.', 'success');
  stepDone('login');
  log('📧 Step 2 email dispatched → ' + STUDENT_EMAIL, 'email');
  showEmailToast(2, 'Authenticate');
  await sleep(500);

  // Phase 3: Navigate
  stepActive('navigate');
  setUrl('mycamu.edu/student/dashboard');
  log('Navigating to Course Registration portal...', 'info');
  await sleep(700);
  setUrl('mycamu.edu/student/registration');
  await sleep(500);
  log('Registration portal loaded.', 'success');
  stepDone('navigate');
  log('📧 Step 3 email dispatched → ' + STUDENT_EMAIL, 'email');
  showEmailToast(3, 'Navigate to Registration');
  await sleep(400);

  document.querySelectorAll('.camu-screen').forEach(s => s.classList.remove('active'));
  document.getElementById('screen-portal').classList.add('active');
  buildPortalSections(winningOption);
  await sleep(400);

  // Phase 4: Submit
  stepActive('submit');
  log('Beginning timetable submission (Option 1 first)...', 'info');

  for (let i = 1; i <= 3; i++) {
    const rc    = document.getElementById('rc-option' + i);
    const badge = document.getElementById('rc' + i + '-badge');
    badge.textContent = 'Trying...';
    badge.className   = 'rc-badge trying';
    log(`Trying Option ${i}...`, 'info');
    await showSection('portal-sec-' + i, 100);
    await sleep(400);

    if (i === winningOption) {
      await animateRegisterCourses(i);
      badge.textContent = 'Registered ✓';
      badge.className   = 'rc-badge ok';
      rc.classList.add('selected');
      log(`Option ${i} fully registered! ✓`, 'success');
      for (let j = i + 1; j <= 3; j++) {
        document.getElementById('rc' + j + '-badge').textContent = 'Not needed';
        document.getElementById('rc' + j + '-badge').className   = 'rc-badge trying';
        log(`Option ${j} — not needed.`, 'muted');
        await showSection('portal-sec-' + j, 100);
      }
      break;
    } else {
      await sleep(800);
      badge.textContent = 'Conflict';
      badge.className   = 'rc-badge conflict';
      rc.classList.add('failed-card');
      log(`Option ${i} has a time conflict. Trying next...`, 'warn');
    }
  }

  stepDone('submit');
  log('📧 Step 4 email dispatched → ' + STUDENT_EMAIL, 'email');
  showEmailToast(4, 'Submit Timetable Options');
  await sleep(500);

  // Phase 5: Confirm
  stepActive('confirm');
  log('Waiting for myCAMU confirmation...', 'info');
  await sleep(900);
  setUrl('mycamu.edu/student/registration/confirmation');
  log('Registration confirmed by myCAMU!', 'success');
  log(`Confirmation ref: ${REG_ID}`, 'system');
  stepDone('confirm');
  log('📧 Step 5 email dispatched → ' + STUDENT_EMAIL, 'email');
  showEmailToast(5, 'Confirmation Received');
  await sleep(700);

  document.querySelectorAll('.camu-screen').forEach(s => s.classList.remove('active'));
  document.getElementById('screen-confirmed').classList.add('active');

  await sleep(1200);
  log('─────────────────────────────', 'muted');
  log('📧 Final confirmation email sent → ' + STUDENT_EMAIL, 'email');
  showFinalEmailToast();
  log('All done. Bot session closed.', 'system');
  document.getElementById('backBtn').classList.add('show');
}

// ── Fire emails asynchronously in background ──────────────────
async function sendEmailsAsync() {
  try {
    await fetch('/api/send-registration-emails.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({
        reg_id:         REG_ID,
        student_email:  STUDENT_EMAIL,
        mycamu_email:   CAMU_EMAIL,
        camu_job_id:    <?= json_encode($camuJobId) ?>,
        options:        OPTIONS,
        winning_option: (OPTIONS.option1?.courses?.length > 0) ? 1 : 2
      })
    });
  } catch (e) {
    console.warn('Email dispatch failed (non-critical):', e);
  }
}

window.addEventListener('load', () => {
  setTimeout(runSimulation, 800);
  setTimeout(sendEmailsAsync, 2000); // slight delay so page renders first
});
</script>
<?php endif; ?>
</body>
</html>
