<?php
/**
 * Check Session API Endpoint
 * Returns current user data if logged in
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

startSession();

if (isLoggedIn()) {
    // Get fresh user data from database
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, name, email, phone, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'joined' => $user['created_at']
            ]
        ]);
    } else {
        // User not found in database, destroy session
        destroySession();
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ]);
    }

    $stmt->close();
    closeDBConnection($conn);
} else {
    echo json_encode([
        'success' => true,
        'logged_in' => false
    ]);
}
?>