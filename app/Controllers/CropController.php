<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/CropModel.php';

class CropController extends BaseController {
    private $cropModel;
    
    protected function initialize() {
        $this->cropModel = new CropModel();
    }
    
    public function index() {
        try {
            $crops = $this->cropModel->getAllCrops();
            
            $data = [
                'crops' => $crops,
                'pageTitle' => 'Crop Management'
            ];
            
            echo $this->render('crops/index', $data);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getCrops() {
        try {
            $crops = $this->cropModel->getAllCrops();
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $crops
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getCropHealth() {
        try {
            $cropHealth = $this->cropModel->getCropHealthData();
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $cropHealth
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getCropsNearHarvest() {
        try {
            $daysThreshold = $_GET['days'] ?? 30;
            $crops = $this->cropModel->getCropsNearHarvest($daysThreshold);
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $crops
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function updateHealthStatus() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['area_id']) || !isset($input['health_status'])) {
                $this->jsonResponse(['error' => 'Missing required fields'], 400);
            }
            
            $areaId = (int)$input['area_id'];
            $healthStatus = $this->cropModel->db->escape($input['health_status']);
            
            $validStatuses = ['excellent', 'good', 'fair', 'poor'];
            if (!in_array($healthStatus, $validStatuses)) {
                $this->jsonResponse(['error' => 'Invalid health status'], 400);
            }
            
            $success = $this->cropModel->updateCropHealth($areaId, $healthStatus);
            
            if ($success) {
                $this->jsonResponse(['status' => 'success', 'message' => 'Health status updated']);
            } else {
                $this->jsonResponse(['error' => 'Failed to update health status'], 500);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function create() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $this->jsonResponse(['error' => 'Invalid JSON data'], 400);
            }
            
            $requiredFields = ['name', 'crop_type', 'size_hectares', 'planting_date', 'estimated_harvest_date'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field])) {
                    $this->jsonResponse(['error' => "Missing required field: {$field}"], 400);
                }
            }
            
            $cropData = $this->sanitizeInput($input);
            $cropData['health_status'] = $cropData['health_status'] ?? 'good';
            
            $cropId = $this->cropModel->create($cropData);
            
            if ($cropId) {
                $this->jsonResponse([
                    'status' => 'success',
                    'message' => 'Crop created successfully',
                    'crop_id' => $cropId
                ]);
            } else {
                $this->jsonResponse(['error' => 'Failed to create crop'], 500);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
