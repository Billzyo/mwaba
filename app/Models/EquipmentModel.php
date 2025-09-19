<?php

require_once __DIR__ . '/BaseModel.php';

class EquipmentModel extends BaseModel {
    protected $table = 'equipment';
    
    protected function getPrimaryKey() {
        return 'equipment_id';
    }
    
    public function getActiveEquipment() {
        $sql = "SELECT * FROM equipment WHERE status IN ('online', 'active') ORDER BY type, name";
        return $this->executeQuery($sql);
    }
    
    public function getEquipmentByType($type) {
        $sql = "SELECT * FROM equipment WHERE type = ? ORDER BY name";
        return $this->executeQuery($sql, [$type]);
    }
    
    public function getEquipmentByStatus($status) {
        $sql = "SELECT * FROM equipment WHERE status = ? ORDER BY name";
        return $this->executeQuery($sql, [$status]);
    }
    
    public function updateEquipmentStatus($equipmentId, $status) {
        $sql = "UPDATE equipment SET status = ? WHERE equipment_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $status, $equipmentId);
        
        return $stmt->execute();
    }
    
    public function getEquipmentNeedingMaintenance() {
        $sql = "SELECT * FROM equipment 
                WHERE status = 'maintenance' 
                OR last_maintenance < DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                ORDER BY last_maintenance ASC";
        
        return $this->executeQuery($sql);
    }
    
    public function updateLastMaintenance($equipmentId) {
        $sql = "UPDATE equipment SET last_maintenance = CURDATE() WHERE equipment_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $equipmentId);
        
        return $stmt->execute();
    }
}
