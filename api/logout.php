<?php
/**
 * Logout API Endpoint
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Destroy the session
destroySession();

echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully'
]);
?>