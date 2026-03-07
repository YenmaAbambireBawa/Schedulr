<?php
/**
 * Save Questionnaire API
 * Saves student questionnaire data to user_data folder
 * File format: ID_FullName.txt
 */

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../config/database.php';

// Require student authentication
try {
    Auth::requireStudent();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required: ' . $e->getMessage()
    ]);
    exit;
}

// Get current user data
$user = Auth::user();

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Log received data for debugging
error_log("Questionnaire submission from user: " . $user['id']);
error_log("Data received: " . print_r($data, true));

// Validate required fields
if (empty($data['program']) || empty($data['year']) || empty($data['gpa'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please complete all required fields'
    ]);
    exit;
}

if (empty($data['completed_courses']) || !is_array($data['completed_courses'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please select at least one completed course'
    ]);
    exit;
}

try {
    // Create user_data directory if it doesn't exist
    $userDataDir = __DIR__ . '/../user_data';
    
    if (!file_exists($userDataDir)) {
        if (!mkdir($userDataDir, 0755, true)) {
            throw new Exception('Failed to create user_data directory');
        }
        error_log("Created user_data directory: " . $userDataDir);
    }
    
    if (!is_writable($userDataDir)) {
        // Try to make it writable
        chmod($userDataDir, 0755);
        if (!is_writable($userDataDir)) {
            throw new Exception('user_data folder is not writable');
        }
    }

    // Clean full name for filename
    $fullName = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $user['name']);
    $fullName = str_replace(' ', '_', $fullName);
    
    $userId = $user['id'];
    $filename = $userId . '_' . $fullName . '.txt';
    $filepath = $userDataDir . '/' . $filename;

    error_log("Attempting to save file to: " . $filepath);

    // Prepare data
    $currentYear = $data['current_year'] ?? date('Y');
    $timestamp = date('Y-m-d H:i:s');

    // --- Fetch all courses dynamically from the database ---
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($pdo === null) {
        throw new Exception("Failed to establish database connection");
    }
    
    $courseList = [];

    $stmt = $pdo->query("SELECT course_code, course_name FROM courses");
    while ($row = $stmt->fetch()) {
        $courseList[$row['course_code']] = $row['course_name'];
    }

    // Format completed courses
    $completedCoursesFormatted = [];
    foreach ($data['completed_courses'] as $courseCode) {
        $courseName = $courseList[$courseCode] ?? 'Unknown Course';
        $completedCoursesFormatted[] = "$courseCode - $courseName";
    }

    // Create file content
    $fileContent = "================================================================================\n";
    $fileContent .= "                     SCHEDULR - STUDENT PROFILE\n";
    $fileContent .= "================================================================================\n\n";
    
    $fileContent .= "PERSONAL INFORMATION\n";
    $fileContent .= "-------------------\n";
    $fileContent .= "System ID      : " . $userId . "\n";
    $fileContent .= "Student ID     : " . ($user['student_id'] ?? 'N/A') . "\n";
    $fileContent .= "Full Name       : " . $user['name'] . "\n";
    $fileContent .= "Email           : " . $user['email'] . "\n";
    $fileContent .= "Profile Created : " . $timestamp . "\n\n";
    
    $fileContent .= "ACADEMIC INFORMATION\n";
    $fileContent .= "-------------------\n";
    $fileContent .= "Program/Major   : " . $data['program'] . "\n";
    $fileContent .= "Current Year    : Year " . $data['year'] . "\n";
    $fileContent .= "Academic Year   : " . $currentYear . "\n";
    $fileContent .= "Current GPA     : " . $data['gpa'] . " / 4.0\n\n";
    
    $fileContent .= "COMPLETED COURSES\n";
    $fileContent .= "-------------------\n";
    $fileContent .= "Total Completed : " . count($data['completed_courses']) . " courses\n\n";
    
    foreach ($completedCoursesFormatted as $index => $course) {
        $fileContent .= ($index + 1) . ". " . $course . "\n";
    }
    
    $fileContent .= "\n================================================================================\n";
    $fileContent .= "                     END OF PROFILE\n";
    $fileContent .= "================================================================================\n";

    // Save to file
    $result = file_put_contents($filepath, $fileContent);
    if ($result === false) {
        throw new Exception('Failed to save user data file - file_put_contents returned false');
    }

    error_log("Successfully saved file: " . $filepath . " (" . $result . " bytes)");

    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Profile saved successfully!',
        'data' => [
            'redirect_url' => '/schedulr/student/dashboard.php',
            'filename' => $filename,
            'filepath' => $filepath,
            'bytes_written' => $result
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in save-questionnaire.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while saving your profile: ' . $e->getMessage()
    ]);
}
?>