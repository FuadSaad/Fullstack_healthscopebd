<?php
/**
 * Save Symptom Check API Endpoint
 * Saves symptom analysis results to user's account
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Check if user is logged in (optional - can save for guests too)
startSession();
$userId = isLoggedIn() ? $_SESSION['user_id'] : null;

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$symptoms = $input['symptoms'] ?? [];
$predictedDisease = $input['predicted_disease'] ?? '';
$severity = $input['severity'] ?? 'mild';
$recommendations = $input['recommendations'] ?? '';

// Validate severity
$validSeverities = ['mild', 'moderate', 'severe'];
if (!in_array($severity, $validSeverities)) {
    $severity = 'mild';
}

// Validate input
if (empty($symptoms)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Symptoms are required'
    ]);
    exit();
}

// Convert symptoms array to JSON
$symptomsJson = json_encode($symptoms);

// Get database connection
$conn = getDBConnection();

// Insert symptom check record
$stmt = $conn->prepare("
    INSERT INTO symptom_checks (user_id, symptoms, predicted_disease, severity, recommendations, created_at) 
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("issss", $userId, $symptomsJson, $predictedDisease, $severity, $recommendations);

if ($stmt->execute()) {
    $reportId = $stmt->insert_id;

    echo json_encode([
        'success' => true,
        'message' => 'Symptom check saved successfully',
        'report_id' => $reportId
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save symptom check',
        'error' => $stmt->error
    ]);
}

$stmt->close();
closeDBConnection($conn);
?>