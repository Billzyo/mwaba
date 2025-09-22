<?php

namespace App\Models;

class SettingsModel extends BaseModel {
    protected $table = 'settings';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get setting by key
     */
    public function getByKey($key) {
        $sql = "SELECT * FROM {$this->table} WHERE setting_key = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $key);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Get setting value by key
     */
    public function getValue($key, $default = null) {
        $setting = $this->getByKey($key);
        return $setting ? $setting['setting_value'] : $default;
    }
    
    /**
     * Set setting value by key
     */
    public function setValue($key, $value) {
        $existing = $this->getByKey($key);
        
        if ($existing) {
            return $this->update($existing['id'], [
                'setting_value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            return $this->create([
                'setting_key' => $key,
                'setting_value' => $value,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Get all settings as key-value array
     */
    public function getAllAsArray() {
        $settings = $this->getAll();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $result;
    }
    
    /**
     * Update multiple settings at once
     */
    public function updateMultiple($settings) {
        $success = true;
        
        foreach ($settings as $key => $value) {
            if (!$this->setValue($key, $value)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Delete setting by key
     */
    public function deleteByKey($key) {
        $sql = "DELETE FROM {$this->table} WHERE setting_key = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $key);
        
        return $stmt->execute();
    }
    
    /**
     * Check if settings table exists
     */
    public function tableExists() {
        $sql = "SHOW TABLES LIKE '{$this->table}'";
        $result = $this->db->query($sql);
        
        return $result && $result->num_rows > 0;
    }
    
    /**
     * Create settings table if it doesn't exist
     */
    public function createTable() {
        if ($this->tableExists()) {
            return true;
        }
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `setting_key` varchar(100) NOT NULL,
            `setting_value` text,
            `description` varchar(255) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        return $this->db->query($sql) !== false;
    }
    
    /**
     * Insert default settings
     */
    public function insertDefaults() {
        $defaultSettings = [
            'farm_name' => 'Shantuka Farm',
            'timezone' => 'UTC',
            'temperature_unit' => 'celsius',
            'humidity_threshold' => '70',
            'soil_moisture_threshold' => '50',
            'notification_email' => '',
            'sensor_update_interval' => '30',
            'data_retention_days' => '365',
            'backup_enabled' => '1',
            'maintenance_mode' => '0'
        ];
        
        foreach ($defaultSettings as $key => $value) {
            $this->setValue($key, $value);
        }
    }
    
    /**
     * Get settings by category
     */
    public function getByCategory($category) {
        $sql = "SELECT * FROM {$this->table} WHERE setting_key LIKE ?";
        $stmt = $this->db->prepare($sql);
        $pattern = $category . '_%';
        $stmt->bind_param("s", $pattern);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $settings = [];
        
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    /**
     * Backup settings to JSON
     */
    public function backupToJson() {
        $settings = $this->getAll();
        $backup = [
            'timestamp' => date('Y-m-d H:i:s'),
            'settings' => $settings
        ];
        
        return json_encode($backup, JSON_PRETTY_PRINT);
    }
    
    /**
     * Restore settings from JSON
     */
    public function restoreFromJson($jsonData) {
        $data = json_decode($jsonData, true);
        
        if (!$data || !isset($data['settings'])) {
            return false;
        }
        
        // Clear existing settings
        $this->deleteAll();
        
        // Insert restored settings
        foreach ($data['settings'] as $setting) {
            $this->create([
                'setting_key' => $setting['setting_key'],
                'setting_value' => $setting['setting_value'],
                'description' => $setting['description'] ?? null,
                'created_at' => $setting['created_at'] ?? date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        return true;
    }
}
