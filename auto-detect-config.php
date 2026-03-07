<?php
/**
 * Automatic Configuration Detector
 * Run this file to automatically detect your URLs and generate configuration
 * Access: http://yoursite.com/auto-detect-config.php
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Configuration Detector - Schedulr</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #dc2626; margin-bottom: 10px; }
        h2 { color: #333; border-bottom: 2px solid #dc2626; padding-bottom: 10px; margin-top: 30px; }
        .info-box {
            background: #e0f2fe;
            border-left: 4px solid #0284c7;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .success { background: #d1fae5; border-left-color: #059669; }
        .warning { background: #fef3c7; border-left-color: #f59e0b; }
        .error { background: #fee2e2; border-left-color: #dc2626; }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        pre {
            background: #1f2937;
            color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .copy-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        .copy-btn:hover { background: #b91c1c; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
        }
        .url-link {
            color: #0284c7;
            text-decoration: none;
            word-break: break-all;
        }
        .url-link:hover { text-decoration: underline; }
        .step {
            background: #f9fafb;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #dc2626;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Auto Configuration Detector</h1>
        <p style="color: #666;">This tool automatically detects your URLs, paths, and generates configuration code.</p>

        <?php
        // Detect protocol
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $scriptDir = dirname($scriptName);
        
        // Calculate base URL
        if ($scriptDir === '/' || $scriptDir === '\\') {
            $baseUrl = "$protocol://$host";
            $basePath = '';
        } else {
            $baseUrl = "$protocol://$host$scriptDir";
            $basePath = $scriptDir;
        }
        
        $currentDir = __DIR__;
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        ?>

        <h2>📍 1. Detected URLs</h2>
        <table>
            <tr>
                <th>Type</th>
                <th>URL</th>
            </tr>
            <tr>
                <td><strong>Base URL</strong></td>
                <td><code><?php echo $baseUrl; ?></code></td>
            </tr>
            <tr>
                <td>Student Dashboard</td>
                <td><a href="<?php echo $baseUrl; ?>/student/dashboard.php" class="url-link" target="_blank">
                    <?php echo $baseUrl; ?>/student/dashboard.php
                </a></td>
            </tr>
            <tr>
                <td>Registration Pending</td>
                <td><a href="<?php echo $baseUrl; ?>/pages/registration-pending.php?id=1" class="url-link" target="_blank">
                    <?php echo $baseUrl; ?>/pages/registration-pending.php?id=1
                </a></td>
            </tr>
            <tr>
                <td>Email Verification</td>
                <td><code><?php echo $baseUrl; ?>/pages/verify-registration.php?token=XXX&id=1</code></td>
            </tr>
            <tr>
                <td>Installation Checker</td>
                <td><a href="<?php echo $baseUrl; ?>/verify-installation.php" class="url-link" target="_blank">
                    <?php echo $baseUrl; ?>/verify-installation.php
                </a></td>
            </tr>
        </table>

        <h2>📁 2. Detected Paths</h2>
        <table>
            <tr>
                <th>Type</th>
                <th>Path</th>
            </tr>
            <tr>
                <td>Current Directory</td>
                <td><code><?php echo $currentDir; ?></code></td>
            </tr>
            <tr>
                <td>Document Root</td>
                <td><code><?php echo $documentRoot; ?></code></td>
            </tr>
            <tr>
                <td>Base Path</td>
                <td><code><?php echo $basePath ? $basePath : '/'; ?></code></td>
            </tr>
        </table>

        <h2>⚙️ 3. Generated Configuration Code</h2>
        
        <div class="step">
            <h3>Step 1: Update Email Verification Link</h3>
            <p>Add this code to <code>api/submit-registration.php</code> (around line 60):</p>
            <pre id="code1"><?php 
$code1 = <<<CODE
// Email verification link - Auto-detected
\$protocol = isset(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] === 'on' ? "https" : "http";
\$baseUrl = \$protocol . "://" . \$_SERVER['HTTP_HOST'] . "$basePath";
\$verificationLink = \$baseUrl . "/pages/verify-registration.php?token=" . \$token . "&id=" . \$registrationId;
CODE;
echo htmlspecialchars($code1);
            ?></pre>
            <button class="copy-btn" onclick="copyToClipboard('code1')">Copy Code</button>
        </div>

        <div class="step">
            <h3>Step 2: Database Configuration File</h3>
            <p>Create <code>config/database.php</code> with this code:</p>
            <pre id="code2"><?php
$code2 = <<<'CODE'
<?php
/**
 * Database Configuration
 * Update these values with your database credentials
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'schedulr_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDatabaseConnection() {
    try {
        $db = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $db;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>
CODE;
echo htmlspecialchars($code2);
            ?></pre>
            <button class="copy-btn" onclick="copyToClipboard('code2')">Copy Code</button>
        </div>

        <div class="step">
            <h3>Step 3: Environment Configuration (.env)</h3>
            <p>Create <code>.env</code> file in your root directory:</p>
            <pre id="code3"><?php
$generatedKey = bin2hex(random_bytes(32));
$code3 = <<<CODE
# Database Configuration
DB_HOST=localhost
DB_NAME=schedulr_db
DB_USER=root
DB_PASS=

# Encryption Key (Auto-generated)
ENCRYPTION_KEY=$generatedKey

# Base URL
BASE_URL=$baseUrl

# Email Configuration (Update with your SMTP details)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
SMTP_FROM=noreply@schedulr.edu
CODE;
echo htmlspecialchars($code3);
            ?></pre>
            <button class="copy-btn" onclick="copyToClipboard('code3')">Copy Code</button>
            <div class="info-box warning" style="margin-top: 10px;">
                <strong>⚠️ Security Warning:</strong> Never commit the .env file to version control! Add it to .gitignore
            </div>
        </div>

        <h2>🗄️ 4. Database Quick Setup</h2>
        <div class="step">
            <h3>Command Line Setup</h3>
            <pre id="code4">mysql -u root -p schedulr_db < create_course_registrations_table.sql</pre>
            <button class="copy-btn" onclick="copyToClipboard('code4')">Copy Command</button>
        </div>

        <h2>✉️ 5. Email Testing</h2>
        
        <div class="info-box">
            <h3>Option A: MailHog (Recommended for Development)</h3>
            <p>MailHog catches all emails locally. No real emails are sent.</p>
            <pre id="code5"># Install MailHog
# Mac:
brew install mailhog
mailhog

# Then access at: http://localhost:8025</pre>
            <button class="copy-btn" onclick="copyToClipboard('code5')">Copy Command</button>
        </div>

        <div class="info-box">
            <h3>Option B: Gmail SMTP</h3>
            <ol>
                <li>Go to <a href="https://myaccount.google.com/security" target="_blank">Google Account Security</a></li>
                <li>Enable "2-Step Verification"</li>
                <li>Generate "App Password" for Mail</li>
                <li>Use that password in your SMTP configuration</li>
            </ol>
        </div>

        <h2>🧪 6. Test Your Setup</h2>
        
        <div class="step">
            <p><strong>Run the installation checker:</strong></p>
            <a href="<?php echo $baseUrl; ?>/verify-installation.php" class="url-link" target="_blank" style="font-size: 18px;">
                → <?php echo $baseUrl; ?>/verify-installation.php
            </a>
        </div>

        <?php
        // Check if files exist
        $files = [
            'api/submit-registration.php' => 'API Endpoint',
            'student/dashboard.php' => 'Student Dashboard',
            'student/registration-pending.php' => 'Registration Pending',
            'pages/verify-registration.php' => 'Email Verification',
            'create_course_registrations_table.sql' => 'Database Migration'
        ];

        echo "<h2>📦 7. File Status Check</h2>";
        echo "<table>";
        echo "<tr><th>File</th><th>Status</th><th>Action</th></tr>";
        
        foreach ($files as $file => $desc) {
            $exists = file_exists(__DIR__ . '/' . $file);
            $status = $exists ? '<span style="color: #059669;">✓ Found</span>' : '<span style="color: #dc2626;">✗ Missing</span>';
            $action = $exists ? 'Ready to use' : 'Need to copy this file';
            echo "<tr><td>$desc<br><code>$file</code></td><td>$status</td><td>$action</td></tr>";
        }
        echo "</table>";
        ?>

        <h2>📝 8. Quick Setup Checklist</h2>
        <div class="step">
            <input type="checkbox" id="c1"> <label for="c1">Run SQL migration to create database table</label><br>
            <input type="checkbox" id="c2"> <label for="c2">Copy all PHP files to correct directories</label><br>
            <input type="checkbox" id="c3"> <label for="c3">Update database credentials in config/database.php</label><br>
            <input type="checkbox" id="c4"> <label for="c4">Generate and set encryption key in .env</label><br>
            <input type="checkbox" id="c5"> <label for="c5">Configure email (MailHog or SMTP)</label><br>
            <input type="checkbox" id="c6"> <label for="c6">Test with verify-installation.php</label><br>
            <input type="checkbox" id="c7"> <label for="c7">Test full registration flow</label><br>
        </div>

        <div class="info-box success">
            <h3>🎉 Next Steps</h3>
            <ol>
                <li>Copy the generated configuration code above</li>
                <li>Update the files as indicated</li>
                <li>Run the database migration</li>
                <li>Test using the installation checker</li>
                <li>Try a full registration flow</li>
            </ol>
        </div>

        <div class="info-box warning">
            <h3>⚠️ Before Production</h3>
            <ul>
                <li>Change encryption key to a secure random value</li>
                <li>Use environment variables for sensitive data</li>
                <li>Enable HTTPS (SSL certificate)</li>
                <li>Configure production SMTP service</li>
                <li>Disable error display in PHP</li>
                <li>Set proper file permissions (755 for dirs, 644 for files)</li>
            </ul>
        </div>
    </div>

    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;
            
            navigator.clipboard.writeText(text).then(() => {
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = '✓ Copied!';
                btn.style.background = '#059669';
                
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = '#dc2626';
                }, 2000);
            }).catch(err => {
                alert('Failed to copy. Please copy manually.');
            });
        }
    </script>
</body>
</html>
