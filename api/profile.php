<?php
/**
 * Profile API Endpoint
 * Returns user profile data and symptom check history
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Require authentication
requireAuth();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Get user profile
$stmt = $conn->prepare("SELECT id, name, email, phone, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);
    $stmt->close();
    closeDBConnection($conn);
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Get symptom check history
$stmt = $conn->prepare("
    SELECT id, symptoms, predicted_disease, severity, recommendations, created_at 
    FROM symptom_checks 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 50
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    // Parse symptoms JSON
    $symptoms = json_decode($row['symptoms'], true);
    if ($symptoms === null) {
        $symptoms = $row['symptoms']; // Keep as string if not JSON
    }

    $reports[] = [
        'id' => $row['id'],
        'type' => 'symptom_check',
        'symptoms' => $symptoms,
        'predicted_disease' => $row['predicted_disease'],
        'severity' => $row['severity'],
        'recommendations' => $row['recommendations'],
        'date' => $row['created_at']
    ];
}
$stmt->close();

// Get disease reports for this user
$diseaseReports = [];
$stmt = $conn->prepare("
    SELECT id, disease_name, symptoms, severity, location_name, created_at 
    FROM disease_reports 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 50
");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $diseaseReports[] = [
            'id' => $row['id'],
            'type' => 'disease_report',
            'disease_name' => $row['disease_name'],
            'symptoms' => $row['symptoms'],
            'severity' => $row['severity'],
            'location' => $row['location_name'],
            'date' => $row['created_at']
        ];
    }
    $stmt->close();
}

// Return profile data
echo json_encode([
    'success' => true,
    'user' => [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'joined' => $user['created_at']
    ],
    'reports' => $reports,
    'disease_reports' => $diseaseReports,
    'total_reports' => count($reports),
    'total_disease_reports' => count($diseaseReports)
]);

closeDBConnection($conn);
?>