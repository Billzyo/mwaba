<?php
/**
 * Real-time Data API Endpoint
 * Provides sensor data for the dashboard
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Simulate sensor data (in real implementation, this would come from database or IoT devices)
    $sensorData = [
        'temperature' => round(20 + (rand(0, 100) / 10), 1),
        'humidity' => round(40 + (rand(0, 400) / 10), 1),
        'soil_moisture' => round(20 + (rand(0, 600) / 10), 1),
        'light_level' => rand(0, 100),
        'wind_speed' => round(0 + (rand(0, 200) / 10), 1),
        'ph_level' => round(6.0 + (rand(0, 20) / 10), 1)
    ];
    
    // Add timestamp
    $sensorData['timestamp'] = time();
    $sensorData['last_updated'] = date('Y-m-d H:i:s');
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $sensorData,
        'message' => 'Sensor data retrieved successfully'
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve sensor data: ' . $e->getMessage()
    ]);
}
?>
