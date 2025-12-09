<?php
/**
 * Authentication Helper Functions for HealthScope BD
 */

// Start session if not already started
function startSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn()
{
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current logged in user data
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'] ?? '',
        'name' => $_SESSION['user_name'] ?? ''
    ];
}

// Set user session after login/registration
function setUserSession($userId, $email, $name)
{
    startSession();
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    $_SESSION['logged_in_at'] = time();
}

// Destroy user session (logout)
function destroySession()
{
    startSession();

    // Unset all session variables
    $_SESSION = [];

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();
}

// Require authentication - redirect or return error if not logged in
function requireAuth($redirect = false)
{
    if (!isLoggedIn()) {
        if ($redirect) {
            header('Location: login.html');
            exit();
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required'
            ]);
            exit();
        }
    }
}

// Generate CSRF token
function generateCSRFToken()
{
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCSRFToken($token)
{
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>