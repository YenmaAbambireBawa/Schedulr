<?php
/**
 * Registration Handler
 * Processes user signup requests
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
if (empty($data->full_name)) {
    sendResponse(false, 'Full name is required', null, 400);
}

if (empty($data->email)) {
    sendResponse(false, 'Email is required', null, 400);
}

if (empty($data->password)) {
    sendResponse(false, 'Password is required', null, 400);
}

if (empty($data->confirm_password)) {
    sendResponse(false, 'Password confirmation is required', null, 400);
}

// Validate role
$role = isset($data->role) ? $data->role : 'student';
if (!in_array($role, ['student', 'admin'])) {
    sendResponse(false, 'Invalid role specified', null, 400);
}

// Validate student ID for students
if ($role === 'student' && empty($data->student_id)) {
    sendResponse(false, 'Student ID is required for student accounts', null, 400);
}

// Validate email format
if (!User::validateEmail($data->email)) {
    sendResponse(false, 'Invalid email format', null, 400);
}

// Check if passwords match
if ($data->password !== $data->confirm_password) {
    sendResponse(false, 'Passwords do not match', null, 400);
}

// Validate password strength
$passwordErrors = User::validatePasswordStrength($data->password);
if (!empty($passwordErrors)) {
    sendResponse(false, implode(', ', $passwordErrors), null, 400);
}

// Validate terms agreement
if (!isset($data->terms_agreed) || $data->terms_agreed !== true) {
    sendResponse(false, 'You must agree to the Terms of Service and Privacy Policy', null, 400);
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

    // Check if email already exists
    if ($user->emailExists()) {
        sendResponse(false, 'An account with this email already exists', null, 409);
    }

    // Set user properties
    $user->full_name = $data->full_name;
    $user->email = $data->email;
    $user->student_id = ($role === 'student') ? $data->student_id : null;
    $user->password = $data->password;
    $user->role = $role;

    // Create user
    if ($user->create()) {
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
            'redirect_url' => $user->role === 'admin' ? '/admin/dashboard.php' : '/student/questionnaire.php',
            'session_id' => session_id()
        ];

        sendResponse(true, 'Account created successfully', $responseData, 201);
    } else {
        sendResponse(false, 'Failed to create account. Please try again.', null, 500);
    }

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    sendResponse(false, 'An error occurred during registration. Please try again.', null, 500);
}
?>
