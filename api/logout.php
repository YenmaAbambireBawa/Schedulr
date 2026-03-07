<?php
/**
 * Logout Handler
 * Processes user logout requests
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        unset($_COOKIE['remember_token']);
    }

    // Clear session data
    $_SESSION = [];

    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();

    //  Redirect to homepage
    header("Location: /schedulr/index.html");
    exit;

} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());

    // Fallback redirect if something breaks
    header("Location: /schedulr/index.html");
    exit;
}
