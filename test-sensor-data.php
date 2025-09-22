<?php
/**
 * Test script to simulate sensor data for real-time testing
 */

$sensorData = [
    'device_id' => 'ESP32_ABC123',
    'temperature' => rand(15, 35),
    'humidity' => rand(30, 80),
    'soil_moisture' => rand(20, 80)
];

$url = 'http://localhost:8000/mwaba/api/realtime/broadcast';
$data = json_encode($sensorData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Sensor Data Test\n";
echo "================\n";
echo "Data sent: " . $data . "\n";
echo "Response Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
?>
