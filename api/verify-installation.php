<?php
/**
 * Installation Verification Script
 * Run this script to verify your installation is complete
 * Access via: http://yoursite.com/verify-installation.php
 */

echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
    h1 { color: #dc2626; }
    .success { color: #065f46; background: #d1fae5; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: #991b1b; background: #fee2e2; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: #92400e; background: #fef3c7; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: #1e40af; background: #dbeafe; padding: 10px; border-radius: 5px; margin: 10px 0; }
    pre { background: #f3f4f6; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

echo "<h1>📋 Schedulr Installation Verification</h1>";
echo "<p>This script checks if all required components are properly installed.</p>";
echo "<hr>";

$errors = [];
$warnings = [];
$success = [];

// Check 1: Database Connection
echo "<h2>1. Database Connection</h2>";
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=schedulr_db;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<div class='success'>✓ Database connection successful</div>";
    $success[] = "Database connected";
} catch (PDOException $e) {
    echo "<div class='error'>✗ Database connection failed: " . $e->getMessage() . "</div>";
    $errors[] = "Database connection failed";
    die("<p><strong>CRITICAL ERROR:</strong> Cannot proceed without database connection.</p>");
}

// Check 2: course_registrations table
echo "<h2>2. course_registrations Table</h2>";
try {
    $stmt = $db->query("DESCRIBE course_registrations");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='success'>✓ Table exists with " . count($columns) . " columns</div>";
    echo "<div class='info'>Columns: " . implode(", ", $columns) . "</div>";
    $success[] = "course_registrations table exists";
} catch (PDOException $e) {
    echo "<div class='error'>✗ Table does not exist: " . $e->getMessage() . "</div>";
    echo "<div class='warning'><strong>Action Required:</strong> Run create_course_registrations_table.sql</div>";
    $errors[] = "Missing course_registrations table";
}

// Check 3: Required Files
echo "<h2>3. Required Files</h2>";
$requiredFiles = [
    'api/submit-registration.php' => 'API endpoint for registration',
    '/student/dashboard.php' => 'Student dashboard',
    '/student/registration-pending.php' => 'Registration pending page',
    'pages/verify-registration.php' => 'Email verification page'
];

foreach ($requiredFiles as $file => $description) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "<div class='success'>✓ $file - $description</div>";
        $success[] = $file;
    } else {
        echo "<div class='error'>✗ Missing: $file - $description</div>";
        $errors[] = "Missing file: $file";
    }
}

// Check 4: PHP Extensions
echo "<h2>4. PHP Extensions</h2>";
$requiredExtensions = ['pdo', 'pdo_mysql', 'openssl', 'json'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='success'>✓ $ext extension loaded</div>";
        $success[] = "$ext extension";
    } else {
        echo "<div class='error'>✗ Missing PHP extension: $ext</div>";
        $errors[] = "Missing extension: $ext";
    }
}

// Check 5: Mail Function
echo "<h2>5. Email Configuration</h2>";
if (function_exists('mail')) {
    echo "<div class='success'>✓ mail() function available</div>";
    echo "<div class='warning'>⚠ Note: mail() may not work in development. Consider using SMTP or MailHog for testing.</div>";
    $warnings[] = "mail() available but may need SMTP configuration";
} else {
    echo "<div class='error'>✗ mail() function not available</div>";
    echo "<div class='warning'><strong>Action Required:</strong> Configure SMTP in submit-registration.php</div>";
    $errors[] = "mail() function not available";
}

// Check 6: Directory Permissions
echo "<h2>6. Directory Permissions</h2>";
$directories = ['api', 'pages', 'user_data'];
foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath) && is_writable($fullPath)) {
        echo "<div class='success'>✓ $dir directory is writable</div>";
        $success[] = "$dir writable";
    } else {
        echo "<div class='warning'>⚠ $dir directory may not be writable</div>";
        $warnings[] = "$dir permissions";
    }
}

// Check 7: Encryption Configuration
echo "<h2>7. Security Configuration</h2>";
$submitRegFile = __DIR__ . '/api/submit-registration.php';
if (file_exists($submitRegFile)) {
    $content = file_get_contents($submitRegFile);
    if (strpos($content, 'your-secret-encryption-key-change-this-in-production') !== false) {
        echo "<div class='error'>✗ Default encryption key detected!</div>";
        echo "<div class='warning'><strong>SECURITY WARNING:</strong> Change the encryption key in production!</div>";
        $errors[] = "Using default encryption key";
    } else {
        echo "<div class='success'>✓ Custom encryption key configured</div>";
        $success[] = "Encryption key configured";
    }
}

// Check 8: Sample Data
echo "<h2>8. Database Data</h2>";
try {
    $stmt = $db->query("SELECT COUNT(*) FROM courses");
    $courseCount = $stmt->fetchColumn();
    if ($courseCount > 0) {
        echo "<div class='success'>✓ Found $courseCount courses in database</div>";
        $success[] = "Courses data present";
    } else {
        echo "<div class='warning'>⚠ No courses found in database</div>";
        $warnings[] = "No courses in database";
    }
    
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $studentCount = $stmt->fetchColumn();
    if ($studentCount > 0) {
        echo "<div class='success'>✓ Found $studentCount student(s) in database</div>";
        $success[] = "Student users present";
    } else {
        echo "<div class='warning'>⚠ No students found in database</div>";
        $warnings[] = "No student users";
    }
} catch (PDOException $e) {
    echo "<div class='error'>✗ Could not check database data: " . $e->getMessage() . "</div>";
}

// Summary
echo "<hr>";
echo "<h2>📊 Installation Summary</h2>";
echo "<p><strong style='color: #065f46;'>✓ Successful Checks:</strong> " . count($success) . "</p>";
echo "<p><strong style='color: #92400e;'>⚠ Warnings:</strong> " . count($warnings) . "</p>";
echo "<p><strong style='color: #991b1b;'>✗ Errors:</strong> " . count($errors) . "</p>";

if (count($errors) === 0 && count($warnings) === 0) {
    echo "<div class='success'><h3>🎉 Installation Complete!</h3>";
    echo "<p>All checks passed. Your system is ready to use.</p>";
    echo "<p><a href='/student/dashboard.php' style='color: #dc2626; font-weight: bold;'>→ Go to Student Dashboard</a></p>";
    echo "</div>";
} elseif (count($errors) === 0) {
    echo "<div class='warning'><h3>⚠ Installation Complete with Warnings</h3>";
    echo "<p>Your system should work, but you may want to address the warnings above.</p>";
    echo "<p><a href='student/dashboard.php' style='color: #dc2626; font-weight: bold;'>→ Go to Student Dashboard</a></p>";
    echo "</div>";
} else {
    echo "<div class='error'><h3>✗ Installation Incomplete</h3>";
    echo "<p>Please fix the errors above before proceeding.</p>";
    echo "<p><strong>Quick Fixes:</strong></p>";
    echo "<ol>";
    if (in_array("Missing course_registrations table", $errors)) {
        echo "<li>Run: <code>mysql -u root -p schedulr_db < create_course_registrations_table.sql</code></li>";
    }
    foreach ($errors as $error) {
        if (strpos($error, 'Missing file') !== false) {
            echo "<li>Copy the missing file to the correct directory</li>";
        }
    }
    echo "</ol>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Generated: " . date('Y-m-d H:i:s') . "</small></p>";
?>