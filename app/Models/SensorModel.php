<?php

namespace App\Models;

use App\Models\BaseModel;

class SensorModel extends BaseModel {
    protected $table = 'sensors';
    
    protected function getPrimaryKey() {
        return 'sensor_id';
    }
    
    public function getLatestReadings() {
        $sql = "SELECT sr.*, s.sensor_type, s.sensor_name, s.location 
                FROM sensor_readings sr
                JOIN sensors s ON sr.sensor_id = s.sensor_id
                WHERE sr.reading_time = (
                    SELECT MAX(reading_time)
                    FROM sensor_readings
                    WHERE sensor_id = sr.sensor_id
                )
                ORDER BY s.sensor_type";
        
        return $this->executeQuery($sql);
    }
    
    public function getHistoricalData($hours = 24) {
        $sql = "SELECT sr.*, s.sensor_type 
                FROM sensor_readings sr
                JOIN sensors s ON sr.sensor_id = s.sensor_id
                WHERE sr.reading_time >= NOW() - INTERVAL ? HOUR
                ORDER BY sr.reading_time ASC";
        
        return $this->executeQuery($sql, [$hours]);
    }
    
    public function getSensorTypes() {
        $sql = "SELECT DISTINCT sensor_type FROM sensors WHERE status = 'active'";
        return $this->executeQuery($sql);
    }
    
    public function getActiveSensors() {
        $sql = "SELECT * FROM sensors WHERE status = 'active' ORDER BY sensor_type, location";
        return $this->executeQuery($sql);
    }
    
    public function getSensorById($sensorId) {
        $sql = "SELECT * FROM sensors WHERE sensor_id = ?";
        return $this->executeQuery($sql, [$sensorId]);
    }
    
    public function getReadingsBySensorId($sensorId, $limit = 100) {
        $sql = "SELECT * FROM sensor_readings 
                WHERE sensor_id = ? 
                ORDER BY reading_time DESC 
                LIMIT ?";
        
        return $this->executeQuery($sql, [$sensorId, $limit]);
    }
    
    public function insertReading($sensorId, $value, $status = 'normal') {
        $sql = "INSERT INTO sensor_readings (sensor_id, value, reading_time, status) 
                VALUES (?, ?, NOW(), ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ids", $sensorId, $value, $status);
        
        return $stmt->execute();
    }
    
    public function getDeviceSensorMapping() {
        return [
            'ESP32_ABC123' => [
                'temperature' => 1,
                'humidity' => 2,
                'soil_moisture' => 3
            ],
            '682D2C3C1C78' => [
                'temperature' => 1,
                'humidity' => 2,
                'soil_moisture' => 3
            ]
        ];
    }
    
    public function getSensorIdByDeviceAndType($deviceId, $sensorType) {
        $mapping = $this->getDeviceSensorMapping();
        
        if (isset($mapping[$deviceId][$sensorType])) {
            return $mapping[$deviceId][$sensorType];
        }
        
        return null;
    }
}
