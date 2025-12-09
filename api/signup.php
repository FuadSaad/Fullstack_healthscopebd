<?php
/**
 * Signup API Endpoint
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Start session
startSession();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$name = $input['name'] ?? '';
$firstName = $input['firstName'] ?? '';
$lastName = $input['lastName'] ?? '';
$email = $input['email'] ?? '';
$phone = $input['phone'] ?? '';
$password = $input['password'] ?? '';

// Support both name formats
if (empty($name) && (!empty($firstName) || !empty($lastName))) {
    $name = trim($firstName . ' ' . $lastName);
}

// Validate input
if (empty($name) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Name, email, and password are required'
    ]);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit();
}

// Validate password strength (min 8 characters)
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 8 characters long'
    ]);
    exit();
}

// Get database connection
$conn = getDBConnection();

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'An account with this email already exists'
    ]);
    $stmt->close();
    closeDBConnection($conn);
    exit();
}
$stmt->close();

// Hash password using bcrypt
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Insert new user
$stmt = $conn->prepare("INSERT INTO users (name, email, phone, password_hash, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $name, $email, $phone, $passwordHash);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;

    // Auto-login: Set user session
    setUserSession($userId, $email, $name);

    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully',
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create account. Please try again.',
        'error' => $stmt->error
    ]);
}

$stmt->close();
closeDBConnection($conn);
?>