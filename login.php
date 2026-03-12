<?php
/**
 * Login Handler
 * Processes user login requests
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Send JSON response
 */
function sendResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed', null, 405);
}

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (empty($data->email) || empty($data->password)) {
    sendResponse(false, 'Email and password are required', null, 400);
}

// Validate email format
if (!User::validateEmail($data->email)) {
    sendResponse(false, 'Invalid email format', null, 400);
}

try {
    // Initialize database and user
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        sendResponse(false, 'Database connection failed', null, 500);
    }

    $user = new User($db);
    $user->email = $data->email;

    // Check if user exists
    if (!$user->emailExists()) {
        sendResponse(false, 'Invalid email or password', null, 401);
    }

    // Check if account is active
    if (!$user->is_active) {
        sendResponse(false, 'Account has been deactivated. Please contact support.', null, 403);
    }

    // Verify password
    if (!$user->verifyPassword($data->password)) {
        sendResponse(false, 'Invalid email or password', null, 401);
    }

    // Check role if specified
    if (isset($data->role) && $user->role !== $data->role) {
        sendResponse(false, 'Access denied. Please select the correct account type.', null, 403);
    }

    // Update last login
    $user->updateLastLogin();

    // Handle "remember me"
    $rememberToken = null;
    if (isset($data->remember_me) && $data->remember_me === true) {
        $rememberToken = $user->generateRememberToken();
        
        // Set cookie for 30 days
        if ($rememberToken) {
            setcookie('remember_token', $rememberToken, time() + (30 * 24 * 60 * 60), '/', '', true, true);
        }
    }

    // Set session variables
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_email'] = $user->email;
    $_SESSION['user_name'] = $user->full_name;
    $_SESSION['user_role'] = $user->role;
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    // Prepare response data
    $responseData = [
        'user' => [
            'id' => $user->id,
            'name' => $user->full_name,
            'email' => $user->email,
            'role' => $user->role,
            'student_id' => $user->student_id
        ],
        'redirect_url' => $user->role === 'admin' ? '/admin/dashboard.php' : '/student/dashboard.php',
        'session_id' => session_id()
    ];

    if ($rememberToken) {
        $responseData['remember_token'] = $rememberToken;
    }

    sendResponse(true, 'Login successful', $responseData, 200);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    sendResponse(false, 'An error occurred during login. Please try again.', null, 500);
}
?>
