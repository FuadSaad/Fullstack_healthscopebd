<?php
/**
 * Login API Endpoint
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Start session
startSession();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);
    exit();
}

// Get database connection
$conn = getDBConnection();

// Prepare SQL statement
$stmt = $conn->prepare("SELECT id, name, email, phone, password_hash, created_at FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password'
    ]);
    $stmt->close();
    closeDBConnection($conn);
    exit();
}

$user = $result->fetch_assoc();

// Verify password
if (password_verify($password, $user['password_hash'])) {
    // Set user session
    setUserSession($user['id'], $user['email'], $user['name']);

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'joined' => $user['created_at']
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password'
    ]);
}

$stmt->close();
closeDBConnection($conn);
?>