<?php
/**
 * Authentication Middleware
 * Validates user sessions and protects routes
 */
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class Auth {
    /**
     * Check if user is logged in
     * @return bool
     */
    public static function check() {
        // Check session
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            // Validate session timeout (30 minutes of inactivity)
            if (isset($_SESSION['last_activity'])) {
                $inactive_time = time() - $_SESSION['last_activity'];
                if ($inactive_time > 1800) { // 30 minutes
                    self::logout();
                    return false;
                }
            }
            
            // Update last activity time
            $_SESSION['last_activity'] = time();
            return true;
        }

        // Check remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            return self::authenticateByToken($_COOKIE['remember_token']);
        }

        return false;
    }

    /**
     * Authenticate user by remember token
     * @param string $token
     * @return bool
     */
    private static function authenticateByToken($token) {
        try {
            $database = new Database();
            $db = $database->getConnection();

            if (!$db) {
                return false;
            }

            $user = new User($db);
            
            if ($user->verifyRememberToken($token)) {
                // Set session variables
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_email'] = $user->email;
                $_SESSION['user_name'] = $user->full_name;
                $_SESSION['user_role'] = $user->role;
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();
                
                return true;
            }
        } catch (Exception $e) {
            error_log("Token authentication error: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Check if current request is an API call
     * @return bool
     */
    private static function isApiRequest() {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($requestUri, '/api/') !== false;
    }

    /**
     * Get current user ID
     * @return int|null
     */
    public static function userId() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }

    /**
     * Get current user email
     * @return string|null
     */
    public static function userEmail() {
        return isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
    }

    /**
     * Get current user name
     * @return string|null
     */
    public static function userName() {
        return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
    }

    /**
     * Get current user role
     * @return string|null
     */
    public static function userRole() {
        return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
    }

    /**
     * Get all current user data
     * @return array|null
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => self::userId(),
            'name' => self::userName(),
            'email' => self::userEmail(),
            'role' => self::userRole()
        ];
    }

    /**
     * Check if user is admin
     * @return bool
     */
    public static function isAdmin() {
        return self::check() && self::userRole() === 'admin';
    }

    /**
     * Check if user is student
     * @return bool
     */
    public static function isStudent() {
        return self::check() && self::userRole() === 'student';
    }

    /**
     * Require authentication
     * Redirect to login if not authenticated (or return JSON for API)
     * @param string $redirectUrl
     */
    public static function requireAuth($redirectUrl = '/student/questionnaire.php') {
        if (!self::check()) {
            if (self::isApiRequest()) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]);
                exit;
            } else {
                header('Location: ' . $redirectUrl);
                exit;
            }
        }
    }

    /**
     * Require admin role
     * Redirect if not admin (or return JSON for API)
     * @param string $redirectUrl
     */
    public static function requireAdmin($redirectUrl = '/index.html') {
        // First check if user is authenticated
        if (!self::check()) {
            if (self::isApiRequest()) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]);
                exit;
            } else {
                header('Location: ' . $redirectUrl);
                exit;
            }
        }
        
        // Then check if user is admin
        if (!self::isAdmin()) {
            if (self::isApiRequest()) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Admin access required'
                ]);
                exit;
            } else {
                header('Location: ' . $redirectUrl);
                exit;
            }
        }
    }

    /**
     * Require student role
     * Redirect if not student (or return JSON for API)
     * @param string $redirectUrl
     */
    public static function requireStudent($redirectUrl = '/index.html') {
        if (!self::isStudent()) {
            if (self::isApiRequest()) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Student access required'
                ]);
                exit;
            } else {
                header('Location: ' . $redirectUrl);
                exit;
            }
        }
    }

    /**
     * Logout user
     */
    public static function logout() {
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
            unset($_COOKIE['remember_token']);
        }

        // Destroy session
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Generate CSRF token
     * @return string
     */
    public static function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     * @param string $token
     * @return bool
     */
    public static function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
