<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SensorModel;
use App\Config\AuthMiddleware;

class RealtimeController extends BaseController {
    private $sensorModel;
    
    protected function initialize() {
        $this->sensorModel = new SensorModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Get latest sensor data for real-time updates
     */
    public function getLatestData() {
        try {
            // Require authentication for real-time data
            AuthMiddleware::requireAuth();
            
            $latestData = $this->sensorModel->getLatestReadings();
            $formattedData = $this->formatRealtimeData($latestData);
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $formattedData,
                'timestamp' => time()
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get historical data for charts
     */
    public function getChartData() {
        try {
            AuthMiddleware::requireAuth();
            
            $hours = $_GET['hours'] ?? 24;
            $sensorType = $_GET['sensor_type'] ?? null;
            
            $historicalData = $this->sensorModel->getHistoricalData($hours, $sensorType);
            $chartData = $this->formatChartData($historicalData);
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $chartData,
                'timestamp' => time()
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get system alerts and notifications
     */
    public function getAlerts() {
        try {
            AuthMiddleware::requireAuth();
            
            $alerts = $this->sensorModel->getActiveAlerts();
            $notifications = $this->getSystemNotifications();
            
            $this->jsonResponse([
                'status' => 'success',
                'alerts' => $alerts,
                'notifications' => $notifications,
                'timestamp' => time()
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Broadcast sensor data to WebSocket clients
     */
    public function broadcastSensorData() {
        try {
            // This endpoint can be called by IoT devices or internal systems
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Invalid JSON data'], 400);
                return;
            }
            
            // Validate required fields
            $requiredFields = ['device_id', 'temperature', 'humidity', 'soil_moisture'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    $this->jsonResponse(['status' => 'error', 'message' => "Missing field: {$field}"], 400);
                    return;
                }
            }
            
            // Store data in database
            $result = $this->sensorModel->insertSensorReading($data);
            
            if ($result) {
                // Broadcast to WebSocket clients
                $this->broadcastToWebSocket($data);
                
                $this->jsonResponse([
                    'status' => 'success',
                    'message' => 'Data broadcasted successfully',
                    'timestamp' => time()
                ]);
            } else {
                $this->jsonResponse(['status' => 'error', 'message' => 'Failed to store data'], 500);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Format data for real-time display
     */
    private function formatRealtimeData($data) {
        $formatted = [];
        
        foreach ($data as $reading) {
            $formatted[] = [
                'sensor_id' => $reading['sensor_id'],
                'sensor_name' => $reading['sensor_name'],
                'sensor_type' => $reading['sensor_type'],
                'value' => $reading['value'],
                'unit' => $this->getSensorUnit($reading['sensor_type']),
                'status' => $this->getSensorStatus($reading['sensor_type'], $reading['value']),
                'location' => $reading['location'],
                'timestamp' => $reading['timestamp']
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Format data for charts
     */
    private function formatChartData($data) {
        $chartData = [
            'temperature' => ['labels' => [], 'data' => []],
            'humidity' => ['labels' => [], 'data' => []],
            'soil_moisture' => ['labels' => [], 'data' => []]
        ];
        
        foreach ($data as $reading) {
            $timestamp = date('H:i', strtotime($reading['timestamp']));
            $sensorType = $reading['sensor_type'];
            
            if (isset($chartData[$sensorType])) {
                $chartData[$sensorType]['labels'][] = $timestamp;
                $chartData[$sensorType]['data'][] = (float)$reading['value'];
            }
        }
        
        return $chartData;
    }
    
    /**
     * Get sensor unit based on type
     */
    private function getSensorUnit($sensorType) {
        $units = [
            'temperature' => '°C',
            'humidity' => '%',
            'soil_moisture' => '%',
            'light' => 'lux',
            'wind' => 'km/h',
            'ph' => 'pH'
        ];
        
        return $units[$sensorType] ?? '';
    }
    
    /**
     * Get sensor status based on value
     */
    private function getSensorStatus($sensorType, $value) {
        $thresholds = [
            'temperature' => ['min' => 10, 'max' => 35],
            'humidity' => ['min' => 30, 'max' => 80],
            'soil_moisture' => ['min' => 20, 'max' => 80]
        ];
        
        if (!isset($thresholds[$sensorType])) {
            return 'normal';
        }
        
        $threshold = $thresholds[$sensorType];
        
        if ($value < $threshold['min']) {
            return 'low';
        } elseif ($value > $threshold['max']) {
            return 'high';
        }
        
        return 'normal';
    }
    
    /**
     * Get system notifications
     */
    private function getSystemNotifications() {
        // This could be expanded to include various system notifications
        $notifications = [];
        
        // Check for offline sensors
        $offlineSensors = $this->sensorModel->getOfflineSensors();
        foreach ($offlineSensors as $sensor) {
            $notifications[] = [
                'type' => 'warning',
                'title' => 'Sensor Offline',
                'message' => "Sensor {$sensor['sensor_name']} has been offline for more than 5 minutes",
                'timestamp' => time()
            ];
        }
        
        return $notifications;
    }
    
    /**
     * Broadcast data to WebSocket server
     */
    private function broadcastToWebSocket($data) {
        try {
            // Use cURL to send data to WebSocket server
            // Resolve WebSocket HTTP bridge URL dynamically
            $requestScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_ENV['WS_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $port = $_ENV['WS_PORT'] ?? null; // optional override
            // If HTTP_HOST already includes a port, don't append another
            $hostHasPort = (strpos($host, ':') !== false);
            $effectiveHost = $hostHasPort ? $host : ($port ? $host . ':' . $port : $host);
            // Default to 8080 if no port in host and no override
            if (!$hostHasPort && !$port) {
                $effectiveHost .= ':8080';
            }
            $webSocketUrl = sprintf('%s://%s/broadcast', $requestScheme, $effectiveHost);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $webSocketUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
        } catch (Exception $e) {
            // Log error but don't fail the request
            error_log("WebSocket broadcast error: " . $e->getMessage());
        }
    }
}
