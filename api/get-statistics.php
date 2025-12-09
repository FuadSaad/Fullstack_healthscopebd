<?php
/**
 * Get Statistics API
 * Returns aggregated disease report statistics
 */

require_once '../config/database.php';

header('Content-Type: application/json');

// Connect to database
$conn = getDBConnection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stats = [
    'total_reports' => 0,
    'diseases' => [],
    'severity' => ['mild' => 0, 'moderate' => 0, 'severe' => 0],
    'divisions' => [],
    'hotspots' => 0,
    'affected_areas' => 0
];

// Get total reports count
$result = $conn->query("SELECT COUNT(*) as total FROM disease_reports");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['total_reports'] = intval($row['total']);
}

// Get disease breakdown
$result = $conn->query("SELECT disease_name, COUNT(*) as count FROM disease_reports GROUP BY disease_name ORDER BY count DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stats['diseases'][$row['disease_name']] = intval($row['count']);
    }
}

// Get severity breakdown
$result = $conn->query("SELECT severity, COUNT(*) as count FROM disease_reports GROUP BY severity");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (isset($stats['severity'][$row['severity']])) {
            $stats['severity'][$row['severity']] = intval($row['count']);
        }
    }
}

// Get division breakdown
$result = $conn->query("SELECT location_name, COUNT(*) as count FROM disease_reports GROUP BY location_name ORDER BY count DESC LIMIT 10");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stats['divisions'][$row['location_name']] = intval($row['count']);
    }
}

// Count unique locations as affected areas
$result = $conn->query("SELECT COUNT(DISTINCT location_name) as areas FROM disease_reports");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['affected_areas'] = intval($row['areas']);
}

// Count hotspots (locations with more than 3 reports)
$result = $conn->query("SELECT COUNT(*) as hotspots FROM (SELECT location_name FROM disease_reports GROUP BY location_name HAVING COUNT(*) >= 3) as h");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['hotspots'] = intval($row['hotspots']);
}

// Determine overall severity level
$max_severity = 'Low';
if ($stats['severity']['severe'] > $stats['total_reports'] * 0.3) {
    $max_severity = 'High';
} elseif ($stats['severity']['moderate'] > $stats['total_reports'] * 0.3) {
    $max_severity = 'Medium';
}
$stats['severity_level'] = $max_severity;

closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'stats' => $stats
]);
