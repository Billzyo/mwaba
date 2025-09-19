<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SensorModel;
use App\Models\CropModel;
use App\Models\EquipmentModel;
use App\Config\AuthMiddleware;

class DashboardController extends BaseController {
    private $sensorModel;
    private $cropModel;
    private $equipmentModel;
    
    protected function initialize() {
        $this->sensorModel = new SensorModel();
        $this->cropModel = new CropModel();
        $this->equipmentModel = new EquipmentModel();
    }
    
    public function index() {
        // Require authentication
        AuthMiddleware::requireAuth();
        
        try {
            // Get current user
            $currentUser = AuthMiddleware::getCurrentUser();
            
            // Get latest sensor readings
            $sensorData = $this->sensorModel->getLatestReadings();
            $latestReadings = $this->formatSensorData($sensorData);
            
            // Get historical data for charts
            $historicalData = $this->sensorModel->getHistoricalData(24);
            $chartData = $this->formatChartData($historicalData);
            
            // Get crop health data
            $cropData = $this->cropModel->getCropHealthData();
            $cropChartData = $this->formatCropData($cropData);
            
            // Get equipment status
            $equipmentData = $this->equipmentModel->getActiveEquipment();
            
            $data = [
                'sensorData' => $latestReadings,
                'chartData' => $chartData,
                'cropData' => $cropChartData,
                'equipmentData' => $equipmentData,
                'pageTitle' => 'Farm Dashboard',
                'currentUser' => $currentUser,
                'activeMenu' => 'dashboard'
            ];
            
            echo $this->render('dashboard/index', $data);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getLatestData() {
        try {
            $sensorData = $this->sensorModel->getLatestReadings();
            $latestReadings = $this->formatSensorData($sensorData);
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $latestReadings,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getChartData() {
        try {
            $hours = $_GET['hours'] ?? 24;
            $historicalData = $this->sensorModel->getHistoricalData($hours);
            $chartData = $this->formatChartData($historicalData);
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $chartData
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    private function formatSensorData($sensorData) {
        $formatted = [];
        
        foreach ($sensorData as $reading) {
            $formatted[$reading['sensor_type']] = [
                'value' => $reading['value'],
                'status' => $reading['status'],
                'reading_time' => $reading['reading_time'],
                'location' => $reading['location']
            ];
        }
        
        return $formatted;
    }
    
    private function formatChartData($historicalData) {
        $chartData = [];
        
        foreach ($historicalData as $reading) {
            $type = $reading['sensor_type'];
            
            if (!isset($chartData[$type])) {
                $chartData[$type] = [
                    'times' => [],
                    'values' => []
                ];
            }
            
            $chartData[$type]['times'][] = date('H:i', strtotime($reading['reading_time']));
            $chartData[$type]['values'][] = (float)$reading['value'];
        }
        
        return $chartData;
    }
    
    private function formatCropData($cropData) {
        $formatted = [];
        
        foreach ($cropData as $crop) {
            $formatted[] = [
                'name' => $crop['name'],
                'health' => $crop['health_status'],
                'days_to_harvest' => max(0, $crop['days_to_harvest']),
                'crop_type' => $crop['crop_type'],
                'size' => $crop['size_hectares']
            ];
        }
        
        return $formatted;
    }
}
