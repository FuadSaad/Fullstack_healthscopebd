<?php
/**
 * Save Disease Report API
 * Saves user-submitted disease reports to the database
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Start session
startSession();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$disease_type = trim($input['disease_type'] ?? '');
$severity = trim($input['severity'] ?? '');
$division = trim($input['division'] ?? '');
$district = trim($input['district'] ?? '');
$location = trim($input['location'] ?? '');
$symptoms = trim($input['symptoms'] ?? '');
$additional_info = trim($input['additional_info'] ?? '');
$latitude = isset($input['latitude']) ? floatval($input['latitude']) : null;
$longitude = isset($input['longitude']) ? floatval($input['longitude']) : null;

if (empty($disease_type) || empty($severity) || empty($division) || empty($district)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Get user ID (optional - anonymous reports allowed)
$user_id = isLoggedIn() ? getCurrentUser() : null;

// Build location name
$location_name = ucfirst($district) . ', ' . ucfirst($division);
if (!empty($location)) {
    $location_name = $location . ', ' . $location_name;
}

// Disease type mapping for display
$disease_names = [
    'flu' => 'Seasonal Flu',
    'dengue' => 'Dengue Fever',
    'covid' => 'COVID-19',
    'gastroenteritis' => 'Gastroenteritis',
    'typhoid' => 'Typhoid',
    'chickenpox' => 'Chickenpox',
    'other' => 'Other'
];
$disease_name = $disease_names[$disease_type] ?? ucfirst($disease_type);

// Connect to database
$conn = getDBConnection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Prepare statement
$stmt = $conn->prepare(
    "INSERT INTO disease_reports (user_id, disease_name, symptoms, severity, latitude, longitude, location_name, division, district, additional_info) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    // Table might not have division/district columns, try without them
    $stmt = $conn->prepare(
        "INSERT INTO disease_reports (user_id, disease_name, symptoms, severity, latitude, longitude, location_name) 
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        closeDBConnection($conn);
        exit;
    }

    $stmt->bind_param('isssdds', $user_id, $disease_name, $symptoms, $severity, $latitude, $longitude, $location_name);
} else {
    $stmt->bind_param('isssddssss', $user_id, $disease_name, $symptoms, $severity, $latitude, $longitude, $location_name, $division, $district, $additional_info);
}

if ($stmt->execute()) {
    $report_id = $conn->insert_id;

    echo json_encode([
        'success' => true,
        'message' => 'Report submitted successfully',
        'report_id' => $report_id,
        'disease' => $disease_name,
        'location' => $location_name
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save report: ' . $stmt->error]);
}

$stmt->close();
closeDBConnection($conn);
