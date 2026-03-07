<?php
/**
 * Microsoft OAuth Callback Handler
 * Handles the callback from Microsoft OAuth and processes course registration
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// Microsoft OAuth Configuration
define('MICROSOFT_CLIENT_ID', 'YOUR_AZURE_APP_CLIENT_ID');
define('MICROSOFT_CLIENT_SECRET', 'YOUR_AZURE_APP_CLIENT_SECRET');
define('MICROSOFT_TENANT_ID', 'YOUR_TENANT_ID');
define('MICROSOFT_REDIRECT_URI', 'http://localhost/Schedulr/api/microsoft-callback.php');

// Check if authorization code is present
if (!isset($_GET['code'])) {
    die('Authorization code not found. Please try again.');
}

$authCode = $_GET['code'];
$state = isset($_GET['state']) ? json_decode($_GET['state'], true) : null;

// Exchange authorization code for access token
$tokenUrl = "https://login.microsoftonline.com/" . MICROSOFT_TENANT_ID . "/oauth2/v2.0/token";

$tokenData = [
    'client_id' => MICROSOFT_CLIENT_ID,
    'client_secret' => MICROSOFT_CLIENT_SECRET,
    'code' => $authCode,
    'redirect_uri' => MICROSOFT_REDIRECT_URI,
    'grant_type' => 'authorization_code',
    'scope' => 'openid profile email User.Read'
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die('Failed to obtain access token from Microsoft. Error: ' . $response);
}

$tokenResponse = json_decode($response, true);
$accessToken = $tokenResponse['access_token'];

// Get user profile from Microsoft Graph API
$graphUrl = "https://graph.microsoft.com/v1.0/me";

$ch = curl_init($graphUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

$profileResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die('Failed to retrieve user profile from Microsoft. Error: ' . $profileResponse);
}

$userProfile = json_decode($profileResponse, true);

// Verify the user's email matches the logged-in user
if (!isset($_SESSION['user_email']) || 
    strtolower($userProfile['mail']) !== strtolower($_SESSION['user_email'])) {
    die('Email mismatch. Please ensure you login with your institutional email: ' . $_SESSION['user_email']);
}

// Get registration data from session
$registrationDataJson = file_get_contents('php://input');
if (empty($registrationDataJson)) {
    // Try to get from POST or session
    $registrationDataJson = $_POST['registration_data'] ?? $_SESSION['pending_registration'] ?? null;
}

if (!$registrationDataJson) {
    die('Registration data not found. Please start the registration process again.');
}

$registrationData = is_string($registrationDataJson) ? json_decode($registrationDataJson, true) : $registrationDataJson;

// Database connection
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=schedulr_db;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Save registration request to database
try {
    $db->beginTransaction();
    
    // Create registrations table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS course_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            student_email VARCHAR(255) NOT NULL,
            registration_data JSON NOT NULL,
            microsoft_verified TINYINT(1) DEFAULT 1,
            status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ";
    $db->exec($createTableSQL);
    
    // Insert registration record
    $stmt = $db->prepare("
        INSERT INTO course_registrations 
        (user_id, student_email, registration_data, microsoft_verified, status) 
        VALUES (?, ?, ?, 1, 'pending')
    ");
    
    $stmt->execute([
        $registrationData['student_id'],
        $registrationData['student_email'],
        json_encode($registrationData)
    ]);
    
    $registrationId = $db->lastInsertId();
    
    $db->commit();
    
    // Clear pending registration from session
    unset($_SESSION['pending_registration']);
    
    // Redirect to success page
    header('Location: ../student/registration-success.php?id=' . $registrationId);
    exit;
    
} catch (Exception $e) {
    $db->rollBack();
    die('Failed to save registration: ' . $e->getMessage());
}
?>