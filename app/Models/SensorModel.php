<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Config\Cache;

class SensorModel extends BaseModel {
    protected $table = 'sensors';
    private $cache;
    
    public function __construct() {
        parent::__construct();
        $this->cache = Cache::getInstance();
    }
    
    protected function getPrimaryKey() {
        return 'sensor_id';
    }
    
    public function getLatestReadings() {
        return $this->cache->remember('latest_readings', 300, function() {
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
        });
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
    
    /**
     * Insert sensor reading with real-time support
     */
    public function insertSensorReading($data) {
        $deviceId = $data['device_id'];
        $mapping = $this->getDeviceSensorMapping();
        
        if (!isset($mapping[$deviceId])) {
            return false;
        }
        
        $success = true;
        $deviceMap = $mapping[$deviceId];
        
        // Insert temperature reading
        if (isset($deviceMap['temperature']) && isset($data['temperature'])) {
            $result = $this->insertReading(
                $deviceMap['temperature'], 
                $data['temperature'], 
                $this->getStatusFromValue('temperature', $data['temperature'])
            );
            $success = $success && $result;
        }
        
        // Insert humidity reading
        if (isset($deviceMap['humidity']) && isset($data['humidity'])) {
            $result = $this->insertReading(
                $deviceMap['humidity'], 
                $data['humidity'], 
                $this->getStatusFromValue('humidity', $data['humidity'])
            );
            $success = $success && $result;
        }
        
        // Insert soil moisture reading
        if (isset($deviceMap['soil_moisture']) && isset($data['soil_moisture'])) {
            $result = $this->insertReading(
                $deviceMap['soil_moisture'], 
                $data['soil_moisture'], 
                $this->getStatusFromValue('soil_moisture', $data['soil_moisture'])
            );
            $success = $success && $result;
        }
        
        return $success;
    }
    
    /**
     * Get status based on sensor value and thresholds
     */
    private function getStatusFromValue($sensorType, $value) {
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
     * Get historical data with sensor type filter
     */
    public function getHistoricalData($hours = 24, $sensorType = null) {
        $cacheKey = "historical_data_{$hours}_{$sensorType}";
        
        return $this->cache->remember($cacheKey, 600, function() use ($hours, $sensorType) {
            $sql = "SELECT sr.*, s.sensor_type, s.sensor_name, s.location
                    FROM sensor_readings sr
                    JOIN sensors s ON sr.sensor_id = s.sensor_id
                    WHERE sr.reading_time >= NOW() - INTERVAL ? HOUR";
            
            $params = [$hours];
            
            if ($sensorType) {
                $sql .= " AND s.sensor_type = ?";
                $params[] = $sensorType;
            }
            
            $sql .= " ORDER BY sr.reading_time ASC";
            
            return $this->executeQuery($sql, $params);
        });
    }
    
    /**
     * Get active alerts based on sensor readings
     */
    public function getActiveAlerts() {
        $sql = "SELECT sr.*, s.sensor_type, s.sensor_name, s.location
                FROM sensor_readings sr
                JOIN sensors s ON sr.sensor_id = s.sensor_id
                WHERE sr.status IN ('low', 'high')
                AND sr.reading_time >= NOW() - INTERVAL 1 HOUR
                ORDER BY sr.reading_time DESC";
        
        return $this->executeQuery($sql);
    }
    
    /**
     * Get offline sensors (no readings in last 5 minutes)
     */
    public function getOfflineSensors() {
        $sql = "SELECT s.*, sr.reading_time as last_reading
                FROM sensors s
                LEFT JOIN sensor_readings sr ON s.sensor_id = sr.sensor_id
                AND sr.reading_time = (
                    SELECT MAX(reading_time)
                    FROM sensor_readings
                    WHERE sensor_id = s.sensor_id
                )
                WHERE s.status = 'active'
                AND (sr.reading_time IS NULL OR sr.reading_time < NOW() - INTERVAL 5 MINUTE)
                ORDER BY s.sensor_name";
        
        return $this->executeQuery($sql);
    }
    
    /**
     * Get sensor statistics for dashboard
     */
    public function getSensorStatistics() {
        $sql = "SELECT 
                    s.sensor_type,
                    COUNT(*) as total_sensors,
                    COUNT(CASE WHEN s.status = 'active' THEN 1 END) as active_sensors,
                    AVG(sr.value) as avg_value,
                    MAX(sr.value) as max_value,
                    MIN(sr.value) as min_value
                FROM sensors s
                LEFT JOIN sensor_readings sr ON s.sensor_id = sr.sensor_id
                AND sr.reading_time = (
                    SELECT MAX(reading_time)
                    FROM sensor_readings
                    WHERE sensor_id = s.sensor_id
                )
                WHERE s.status = 'active'
                GROUP BY s.sensor_type";
        
        return $this->executeQuery($sql);
    }
    
    /**
     * Get real-time sensor data with status
     */
    public function getRealtimeData() {
        $sql = "SELECT sr.*, s.sensor_type, s.sensor_name, s.location
                FROM sensor_readings sr
                JOIN sensors s ON sr.sensor_id = s.sensor_id
                WHERE sr.reading_time = (
                    SELECT MAX(reading_time)
                    FROM sensor_readings
                    WHERE sensor_id = sr.sensor_id
                )
                AND s.status = 'active'
                ORDER BY s.sensor_type, s.location";
        
        $data = $this->executeQuery($sql);
        
        // Add status information
        foreach ($data as &$reading) {
            $reading['status'] = $this->getStatusFromValue($reading['sensor_type'], $reading['value']);
            $reading['unit'] = $this->getSensorUnit($reading['sensor_type']);
        }
        
        return $data;
    }

    /**
     * Fetch aggregated time series data for the last N days
     */
    public function fetchTimeSeriesData(int $days): array {
        $sql = "SELECT 
                    DATE(sr.reading_time) as date,
                    HOUR(sr.reading_time) as hour,
                    s.sensor_type,
                    AVG(sr.value) as avg_value,
                    MAX(sr.value) as max_value,
                    MIN(sr.value) as min_value
                FROM sensor_readings sr
                JOIN sensors s ON sr.sensor_id = s.sensor_id
                WHERE sr.reading_time >= NOW() - INTERVAL ? DAY
                GROUP BY DATE(sr.reading_time), HOUR(sr.reading_time), s.sensor_type
                ORDER BY date ASC, hour ASC";
        return $this->executeQuery($sql, [$days]);
    }

    /**
     * Fetch comparative data for recent vs previous period
     */
    public function fetchComparativeData(): array {
        $sql = "SELECT 
                    s.sensor_type,
                    AVG(sr.value) as current_avg,
                    (SELECT AVG(value) FROM sensor_readings sr2 
                     JOIN sensors s2 ON sr2.sensor_id = s2.sensor_id 
                     WHERE s2.sensor_type = s.sensor_type 
                     AND sr2.reading_time >= NOW() - INTERVAL 30 DAY) as previous_avg
                FROM sensor_readings sr
                JOIN sensors s ON sr.sensor_id = s.sensor_id
                WHERE sr.reading_time >= NOW() - INTERVAL 7 DAY
                GROUP BY s.sensor_type";
        return $this->executeQuery($sql);
    }

    /**
     * Fetch distribution buckets for the last 30 days
     */
    public function fetchDistributionData(): array {
        $sql = "SELECT 
                    s.sensor_type,
                    CASE 
                        WHEN sr.value < 20 THEN 'Low'
                        WHEN sr.value BETWEEN 20 AND 80 THEN 'Normal'
                        WHEN sr.value > 80 THEN 'High'
                    END as range_category,
                    COUNT(*) as count
                FROM sensor_readings sr
                JOIN sensors s ON sr.sensor_id = s.sensor_id
                WHERE sr.reading_time >= NOW() - INTERVAL 30 DAY
                GROUP BY s.sensor_type, range_category
                ORDER BY s.sensor_type, range_category";
        return $this->executeQuery($sql);
    }

    /**
     * Fetch simplified correlation data between two sensor types
     */
    public function fetchCorrelationData(string $type1, string $type2): array {
        $sql = "SELECT 
                    AVG(CASE WHEN s1.sensor_type = ? THEN sr1.value END) as avg1,
                    AVG(CASE WHEN s2.sensor_type = ? THEN sr2.value END) as avg2
                FROM sensor_readings sr1
                JOIN sensors s1 ON sr1.sensor_id = s1.sensor_id
                JOIN sensor_readings sr2 ON sr2.reading_time = sr1.reading_time
                JOIN sensors s2 ON sr2.sensor_id = s2.sensor_id
                WHERE sr1.reading_time >= NOW() - INTERVAL 30 DAY
                AND s1.sensor_type = ? AND s2.sensor_type = ?";
        return $this->executeQuery($sql, [$type1, $type2, $type1, $type2]);
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
}
