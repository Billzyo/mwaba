<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/EquipmentModel.php';

class EquipmentController extends BaseController {
    private $equipmentModel;
    
    protected function initialize() {
        $this->equipmentModel = new EquipmentModel();
    }
    
    public function index() {
        try {
            $equipment = $this->equipmentModel->getAll();
            
            $data = [
                'equipment' => $equipment,
                'pageTitle' => 'Equipment Management'
            ];
            
            echo $this->render('equipment/index', $data);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getEquipment() {
        try {
            $equipment = $this->equipmentModel->getAll();
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $equipment
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getActiveEquipment() {
        try {
            $equipment = $this->equipmentModel->getActiveEquipment();
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $equipment
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getEquipmentByType() {
        try {
            $type = $_GET['type'] ?? '';
            
            if (empty($type)) {
                $this->jsonResponse(['error' => 'Equipment type is required'], 400);
            }
            
            $equipment = $this->equipmentModel->getEquipmentByType($type);
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $equipment
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function updateStatus() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['equipment_id']) || !isset($input['status'])) {
                $this->jsonResponse(['error' => 'Missing required fields'], 400);
            }
            
            $equipmentId = (int)$input['equipment_id'];
            $status = $this->equipmentModel->db->escape($input['status']);
            
            $validStatuses = ['online', 'offline', 'active', 'idle', 'maintenance'];
            if (!in_array($status, $validStatuses)) {
                $this->jsonResponse(['error' => 'Invalid status'], 400);
            }
            
            $success = $this->equipmentModel->updateEquipmentStatus($equipmentId, $status);
            
            if ($success) {
                $this->jsonResponse(['status' => 'success', 'message' => 'Equipment status updated']);
            } else {
                $this->jsonResponse(['error' => 'Failed to update equipment status'], 500);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getMaintenanceSchedule() {
        try {
            $equipment = $this->equipmentModel->getEquipmentNeedingMaintenance();
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $equipment
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function updateMaintenance() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['equipment_id'])) {
                $this->jsonResponse(['error' => 'Equipment ID is required'], 400);
            }
            
            $equipmentId = (int)$input['equipment_id'];
            $success = $this->equipmentModel->updateLastMaintenance($equipmentId);
            
            if ($success) {
                $this->jsonResponse(['status' => 'success', 'message' => 'Maintenance record updated']);
            } else {
                $this->jsonResponse(['error' => 'Failed to update maintenance record'], 500);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
