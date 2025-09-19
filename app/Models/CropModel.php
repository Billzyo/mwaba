<?php

require_once __DIR__ . '/BaseModel.php';

class CropModel extends BaseModel {
    protected $table = 'crop_areas';
    
    protected function getPrimaryKey() {
        return 'area_id';
    }
    
    public function getAllCrops() {
        $sql = "SELECT *, 
                DATEDIFF(estimated_harvest_date, CURDATE()) as days_to_harvest
                FROM crop_areas 
                ORDER BY name";
        
        return $this->executeQuery($sql);
    }
    
    public function getCropHealthData() {
        $sql = "SELECT name, health_status, 
                DATEDIFF(estimated_harvest_date, CURDATE()) as days_to_harvest,
                crop_type, size_hectares
                FROM crop_areas 
                ORDER BY name";
        
        return $this->executeQuery($sql);
    }
    
    public function getCropsByType($cropType) {
        $sql = "SELECT * FROM crop_areas WHERE crop_type = ? ORDER BY name";
        return $this->executeQuery($sql, [$cropType]);
    }
    
    public function getCropsByHealthStatus($healthStatus) {
        $sql = "SELECT * FROM crop_areas WHERE health_status = ? ORDER BY name";
        return $this->executeQuery($sql, [$healthStatus]);
    }
    
    public function updateCropHealth($areaId, $healthStatus) {
        $sql = "UPDATE crop_areas SET health_status = ? WHERE area_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $healthStatus, $areaId);
        
        return $stmt->execute();
    }
    
    public function getCropsNearHarvest($daysThreshold = 30) {
        $sql = "SELECT * FROM crop_areas 
                WHERE DATEDIFF(estimated_harvest_date, CURDATE()) <= ? 
                AND DATEDIFF(estimated_harvest_date, CURDATE()) > 0
                ORDER BY estimated_harvest_date ASC";
        
        return $this->executeQuery($sql, [$daysThreshold]);
    }
}
