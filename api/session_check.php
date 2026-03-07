<?php
/**
 * Session Check - Debug Helper
 * This file helps you check if you're logged in and what your session contains
 */

session_start();
header('Content-Type: application/json');

echo json_encode([
    'session_active' => session_status() === PHP_SESSION_ACTIVE,
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'logged_in' => isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : false,
    'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
    'user_email' => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null,
    'user_name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null,
    'user_role' => isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null,
    'is_admin' => (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'),
    'cookies' => $_COOKIE
], JSON_PRETTY_PRINT);