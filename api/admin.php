<?php
/**
 * Admin API Endpoints
 * Handles CRUD operations for all admin functionality
 */

// Start output buffering
ob_start();

// Set JSON header FIRST
header('Content-Type: application/json');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Require files
try {
    require_once __DIR__ . '/../middleware/Auth.php';
    require_once __DIR__ . '/../config/database.php';
    
    // Require admin authentication
    Auth::requireAdmin();
    
    // Clear output buffer
    ob_end_clean();
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Authentication error: ' . $e->getMessage()
    ]);
    exit;
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    switch($action) {
        case 'stats':
            getStats($db);
            break;
            
        case 'users':
            handleUsers($db, $method);
            break;
            
        case 'courses':
            handleCourses($db, $method);
            break;
            
        case 'departments':
            handleDepartments($db, $method);
            break;
            
        case 'prerequisites':
            handlePrerequisites($db, $method);
            break;
            
        case 'registrations':
            handleRegistrations($db, $method);
            break;
            
        case 'questionnaires':
            handleQuestionnaires($db, $method);
            break;
            
        case 'sessions':
            handleSessions($db, $method);
            break;
            
        case 'login-attempts':
            handleLoginAttempts($db, $method);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// ============================================
// STATS
// ============================================
function getStats($db) {
    $stats = [
        'users' => $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'],
        'students' => $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'")->fetch()['count'],
        'admins' => $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch()['count'],
        'courses' => $db->query("SELECT COUNT(*) as count FROM courses")->fetch()['count'],
        'departments' => $db->query("SELECT COUNT(*) as count FROM departments")->fetch()['count'],
        'registrations' => $db->query("SELECT COUNT(*) as count FROM course_registrations")->fetch()['count'],
        'active_registrations' => $db->query("SELECT COUNT(*) as count FROM course_registrations WHERE registration_status != 'failed'")->fetch()['count'],
        'prerequisites' => $db->query("SELECT COUNT(*) as count FROM course_prerequisites")->fetch()['count'],
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}

// ============================================
// USERS
// ============================================
function handleUsers($db, $method) {
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                getUserById($db, $_GET['id']);
            } else {
                getAllUsers($db);
            }
            break;
            
        case 'POST':
            createUser($db);
            break;
            
        case 'PUT':
            updateUser($db);
            break;
            
        case 'DELETE':
            deleteUser($db);
            break;
    }
}

function getAllUsers($db) {
    $search = $_GET['search'] ?? '';
    $limit = $_GET['limit'] ?? 50;
    $offset = $_GET['offset'] ?? 0;
    
    $query = "SELECT id, full_name, email, student_id, role, is_active, created_at, last_login 
              FROM users 
              WHERE full_name LIKE :search OR email LIKE :search OR student_id LIKE :search
              ORDER BY created_at DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($query);
    $searchParam = "%{$search}%";
    $stmt->bindParam(':search', $searchParam);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
}

function getUserById($db, $id) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $user
    ]);
}

function createUser($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $required = ['full_name', 'email', 'password', 'role'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
            return;
        }
    }
    
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $data['email']]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email already exists']);
        return;
    }
    
    $stmt = $db->prepare("INSERT INTO users (full_name, email, student_id, password_hash, role, is_active) 
                          VALUES (:full_name, :email, :student_id, :password_hash, :role, :is_active)");
    
    $stmt->execute([
        ':full_name' => $data['full_name'],
        ':email' => $data['email'],
        ':student_id' => $data['student_id'] ?? null,
        ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        ':role' => $data['role'],
        ':is_active' => $data['is_active'] ?? 1
    ]);
    
    echo json_encode([
        'success' => true,
        'data' => ['id' => $db->lastInsertId()]
    ]);
}

function updateUser($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'User ID is required']);
        return;
    }
    
    $updates = [];
    $params = [':id' => $data['id']];
    
    if (isset($data['full_name'])) {
        $updates[] = "full_name = :full_name";
        $params[':full_name'] = $data['full_name'];
    }
    if (isset($data['email'])) {
        $updates[] = "email = :email";
        $params[':email'] = $data['email'];
    }
    if (isset($data['student_id'])) {
        $updates[] = "student_id = :student_id";
        $params[':student_id'] = $data['student_id'];
    }
    if (isset($data['role'])) {
        $updates[] = "role = :role";
        $params[':role'] = $data['role'];
    }
    if (isset($data['is_active'])) {
        $updates[] = "is_active = :is_active";
        $params[':is_active'] = $data['is_active'];
    }
    if (isset($data['password'])) {
        $updates[] = "password_hash = :password_hash";
        $params[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        return;
    }
    
    $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    echo json_encode(['success' => true]);
}

function deleteUser($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'User ID is required']);
        return;
    }
    
    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);
    
    echo json_encode(['success' => true]);
}

// ============================================
// COURSES
// ============================================
function handleCourses($db, $method) {
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                getCourseById($db, $_GET['id']);
            } else {
                getAllCourses($db);
            }
            break;
            
        case 'POST':
            createCourse($db);
            break;
            
        case 'PUT':
            updateCourse($db);
            break;
            
        case 'DELETE':
            deleteCourse($db);
            break;
    }
}

function getAllCourses($db) {
    $search = $_GET['search'] ?? '';
    $limit = $_GET['limit'] ?? 100;
    $offset = $_GET['offset'] ?? 0;
    
    $query = "SELECT c.*, d.dept_name, d.dept_code 
              FROM courses c 
              LEFT JOIN departments d ON c.dept_id = d.dept_id
              WHERE c.course_code LIKE :search OR c.course_name LIKE :search
              ORDER BY c.course_code ASC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($query);
    $searchParam = "%{$search}%";
    $stmt->bindParam(':search', $searchParam);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $courses
    ]);
}

function getCourseById($db, $id) {
    $stmt = $db->prepare("SELECT c.*, d.dept_name, d.dept_code 
                          FROM courses c 
                          LEFT JOIN departments d ON c.dept_id = d.dept_id
                          WHERE c.course_id = :id");
    $stmt->execute([':id' => $id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Course not found']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $course
    ]);
}

function createCourse($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $required = ['course_code', 'course_name', 'dept_id', 'credits'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
            return;
        }
    }
    
    $stmt = $db->prepare("INSERT INTO courses (course_code, course_name, dept_id, credits, course_description) 
                          VALUES (:course_code, :course_name, :dept_id, :credits, :course_description)");
    
    $stmt->execute([
        ':course_code' => $data['course_code'],
        ':course_name' => $data['course_name'],
        ':dept_id' => $data['dept_id'],
        ':credits' => $data['credits'],
        ':course_description' => $data['course_description'] ?? null
    ]);
    
    echo json_encode([
        'success' => true,
        'data' => ['id' => $db->lastInsertId()]
    ]);
}

function updateCourse($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['course_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Course ID is required']);
        return;
    }
    
    $updates = [];
    $params = [':course_id' => $data['course_id']];
    
    $allowedFields = ['course_code', 'course_name', 'dept_id', 'credits', 'course_description'];
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "{$field} = :{$field}";
            $params[":{$field}"] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        return;
    }
    
    $query = "UPDATE courses SET " . implode(', ', $updates) . " WHERE course_id = :course_id";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    echo json_encode(['success' => true]);
}

function deleteCourse($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['course_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Course ID is required']);
        return;
    }
    
    $stmt = $db->prepare("DELETE FROM courses WHERE course_id = :course_id");
    $stmt->execute([':course_id' => $data['course_id']]);
    
    echo json_encode(['success' => true]);
}

// ============================================
// DEPARTMENTS
// ============================================
function handleDepartments($db, $method) {
    switch($method) {
        case 'GET':
            getAllDepartments($db);
            break;
            
        case 'POST':
            createDepartment($db);
            break;
            
        case 'PUT':
            updateDepartment($db);
            break;
            
        case 'DELETE':
            deleteDepartment($db);
            break;
    }
}

function getAllDepartments($db) {
    $query = "SELECT d.*, COUNT(c.course_id) as course_count 
              FROM departments d
              LEFT JOIN courses c ON d.dept_id = c.dept_id
              GROUP BY d.dept_id
              ORDER BY d.dept_name ASC";
    
    $stmt = $db->query($query);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $departments
    ]);
}

function createDepartment($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $required = ['dept_code', 'dept_name'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
            return;
        }
    }
    
    $stmt = $db->prepare("INSERT INTO departments (dept_code, dept_name) VALUES (:dept_code, :dept_name)");
    $stmt->execute([
        ':dept_code' => $data['dept_code'],
        ':dept_name' => $data['dept_name']
    ]);
    
    echo json_encode([
        'success' => true,
        'data' => ['id' => $db->lastInsertId()]
    ]);
}

function updateDepartment($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['dept_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Department ID is required']);
        return;
    }
    
    $updates = [];
    $params = [':dept_id' => $data['dept_id']];
    
    if (isset($data['dept_code'])) {
        $updates[] = "dept_code = :dept_code";
        $params[':dept_code'] = $data['dept_code'];
    }
    if (isset($data['dept_name'])) {
        $updates[] = "dept_name = :dept_name";
        $params[':dept_name'] = $data['dept_name'];
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        return;
    }
    
    $query = "UPDATE departments SET " . implode(', ', $updates) . " WHERE dept_id = :dept_id";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    echo json_encode(['success' => true]);
}

function deleteDepartment($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['dept_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Department ID is required']);
        return;
    }
    
    $stmt = $db->prepare("DELETE FROM departments WHERE dept_id = :dept_id");
    $stmt->execute([':dept_id' => $data['dept_id']]);
    
    echo json_encode(['success' => true]);
}

// ============================================
// PREREQUISITES
// ============================================
function handlePrerequisites($db, $method) {
    switch($method) {
        case 'GET':
            getAllPrerequisites($db);
            break;
            
        case 'POST':
            createPrerequisite($db);
            break;
            
        case 'PUT':
            updatePrerequisite($db);
            break;
            
        case 'DELETE':
            deletePrerequisite($db);
            break;
    }
}

function getAllPrerequisites($db) {
    $query = "SELECT cp.*, 
              c1.course_code as course_code, c1.course_name as course_name,
              c2.course_code as prereq_code, c2.course_name as prereq_name
              FROM course_prerequisites cp
              JOIN courses c1 ON cp.course_id = c1.course_id
              JOIN courses c2 ON cp.prerequisite_course_id = c2.course_id
              ORDER BY c1.course_code ASC, cp.prerequisite_type ASC";
    
    $stmt = $db->query($query);
    $prerequisites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $prerequisites
    ]);
}

function createPrerequisite($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $required = ['course_id', 'prerequisite_course_id'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
            return;
        }
    }
    
    $stmt = $db->prepare("INSERT INTO course_prerequisites (course_id, prerequisite_course_id, prerequisite_type) 
                          VALUES (:course_id, :prerequisite_course_id, :prerequisite_type)");
    
    $stmt->execute([
        ':course_id' => $data['course_id'],
        ':prerequisite_course_id' => $data['prerequisite_course_id'],
        ':prerequisite_type' => $data['prerequisite_type'] ?? 'required'
    ]);
    
    echo json_encode([
        'success' => true,
        'data' => ['id' => $db->lastInsertId()]
    ]);
}

function updatePrerequisite($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['prereq_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Prerequisite ID is required']);
        return;
    }
    
    $stmt = $db->prepare("UPDATE course_prerequisites SET prerequisite_type = :prerequisite_type WHERE prereq_id = :prereq_id");
    $stmt->execute([
        ':prereq_id' => $data['prereq_id'],
        ':prerequisite_type' => $data['prerequisite_type']
    ]);
    
    echo json_encode(['success' => true]);
}

function deletePrerequisite($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['prereq_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Prerequisite ID is required']);
        return;
    }
    
    $stmt = $db->prepare("DELETE FROM course_prerequisites WHERE prereq_id = :prereq_id");
    $stmt->execute([':prereq_id' => $data['prereq_id']]);
    
    echo json_encode(['success' => true]);
}

// ============================================
// REGISTRATIONS
// ============================================
function handleRegistrations($db, $method) {
    switch($method) {
        case 'GET':
            getAllRegistrations($db);
            break;
            
        case 'PUT':
            updateRegistration($db);
            break;
            
        case 'DELETE':
            deleteRegistration($db);
            break;
    }
}

function getAllRegistrations($db) {
    $search = $_GET['search'] ?? '';
    
    $query = "SELECT cr.*, u.full_name, u.email
              FROM course_registrations cr
              LEFT JOIN users u ON cr.student_id = u.id
              WHERE cr.student_email LIKE :search OR u.full_name LIKE :search
              ORDER BY cr.submitted_at DESC";
    
    $stmt = $db->prepare($query);
    $searchParam = "%{$search}%";
    $stmt->bindParam(':search', $searchParam);
    $stmt->execute();
    
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $registrations
    ]);
}

function updateRegistration($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['registration_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Registration ID is required']);
        return;
    }
    
    $stmt = $db->prepare("UPDATE course_registrations SET registration_status = :status WHERE registration_id = :registration_id");
    $stmt->execute([
        ':registration_id' => $data['registration_id'],
        ':status' => $data['registration_status']
    ]);
    
    echo json_encode(['success' => true]);
}

function deleteRegistration($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['registration_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Registration ID is required']);
        return;
    }
    
    $stmt = $db->prepare("DELETE FROM course_registrations WHERE registration_id = :registration_id");
    $stmt->execute([':registration_id' => $data['registration_id']]);
    
    echo json_encode(['success' => true]);
}

// ============================================
// QUESTIONNAIRES
// ============================================
function handleQuestionnaires($db, $method) {
    switch($method) {
        case 'GET':
            getAllQuestionnaires($db);
            break;
            
        case 'DELETE':
            deleteQuestionnaire($db);
            break;
    }
}

function getAllQuestionnaires($db) {
    $query = "SELECT q.*, u.full_name, u.email
              FROM student_questionnaires q
              LEFT JOIN users u ON q.user_id = u.id
              ORDER BY q.created_at DESC";
    
    $stmt = $db->query($query);
    $questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $questionnaires
    ]);
}

function deleteQuestionnaire($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Questionnaire ID is required']);
        return;
    }
    
    $stmt = $db->prepare("DELETE FROM student_questionnaires WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);
    
    echo json_encode(['success' => true]);
}

// ============================================
// SESSIONS
// ============================================
function handleSessions($db, $method) {
    if ($method === 'GET') {
        $query = "SELECT s.*, u.full_name, u.email
                  FROM sessions s
                  LEFT JOIN users u ON s.user_id = u.id
                  ORDER BY s.last_activity DESC
                  LIMIT 100";
        
        $stmt = $db->query($query);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $sessions
        ]);
    }
}

// ============================================
// LOGIN ATTEMPTS
// ============================================
function handleLoginAttempts($db, $method) {
    if ($method === 'GET') {
        $query = "SELECT * FROM login_attempts ORDER BY attempted_at DESC LIMIT 100";
        
        $stmt = $db->query($query);
        $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $attempts
        ]);
    }
}