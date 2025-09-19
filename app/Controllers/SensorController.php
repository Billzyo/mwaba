<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SensorModel;

class SensorController extends BaseController {
    private $sensorModel;
    
    protected function initialize() {
        $this->sensorModel = new SensorModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function receiveData() {
        try {
            // Get POST data
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Invalid JSON data'], 400);
            }
            
            // Validate required fields
            $requiredFields = ['device_id', 'temperature', 'humidity', 'soil_moisture'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    $this->jsonResponse(['status' => 'error', 'message' => "Missing field: {$field}"], 400);
                }
            }
            
            // Sanitize inputs
            $deviceId = $this->sensorModel->db->escape($data['device_id']);
            $temperature = (float)$data['temperature'];
            $humidity = (float)$data['humidity'];
            $soilMoisture = (int)$data['soil_moisture'];
            
            // Get device sensor mapping
            $mapping = $this->sensorModel->getDeviceSensorMapping();
            
            if (!isset($mapping[$deviceId])) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Unknown device_id'], 400);
            }
            
            $deviceMap = $mapping[$deviceId];
            $success = true;
            $errors = [];
            
            // Insert sensor readings
            if (isset($deviceMap['temperature'])) {
                if (!$this->sensorModel->insertReading($deviceMap['temperature'], $temperature)) {
                    $success = false;
                    $errors[] = 'Failed to insert temperature reading';
                }
            }
            
            if (isset($deviceMap['humidity'])) {
                if (!$this->sensorModel->insertReading($deviceMap['humidity'], $humidity)) {
                    $success = false;
                    $errors[] = 'Failed to insert humidity reading';
                }
            }
            
            if (isset($deviceMap['soil_moisture'])) {
                if (!$this->sensorModel->insertReading($deviceMap['soil_moisture'], $soilMoisture)) {
                    $success = false;
                    $errors[] = 'Failed to insert soil moisture reading';
                }
            }
            
            if ($success) {
                $this->jsonResponse(['status' => 'success', 'message' => 'Data stored successfully']);
            } else {
                $this->jsonResponse(['status' => 'error', 'message' => 'Some data could not be stored', 'errors' => $errors], 500);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    public function getSensors() {
        try {
            $sensors = $this->sensorModel->getActiveSensors();
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $sensors
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getSensorReadings($sensorId) {
        try {
            $limit = $_GET['limit'] ?? 100;
            $readings = $this->sensorModel->getReadingsBySensorId($sensorId, $limit);
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $readings
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getLatestReadings() {
        try {
            $readings = $this->sensorModel->getLatestReadings();
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $readings
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
