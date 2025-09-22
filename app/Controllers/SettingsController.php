<?php

namespace App\Controllers;

use App\Models\SettingsModel;
use App\Models\UserModel;

class SettingsController extends BaseController {
    private $settingsModel;
    
    protected function initialize() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->settingsModel = new SettingsModel();
        
        // Create settings table if it doesn't exist
        if (!$this->settingsModel->tableExists()) {
            $this->settingsModel->createTable();
            $this->settingsModel->insertDefaults();
        }
    }
    
    public function index() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: /mwaba/login');
            exit;
        }
        
        // Get current settings
        $settings = $this->getSystemSettings();
        
        // Get user preferences
        $userPreferences = $this->getUserPreferences();
        
        // Render settings page
        $content = $this->render('settings/index', [
            'settings' => $settings,
            'userPreferences' => $userPreferences,
            'currentUser' => $_SESSION,
            'pageTitle' => 'System Settings',
            'activeMenu' => 'settings'
        ]);
        
        // Make variables available for the layout
        $activeMenu = 'settings';
        $currentUser = $_SESSION;
        $pageTitle = 'System Settings';
        
        // Include layout
        include __DIR__ . '/../Views/layouts/main.php';
    }
    
    public function update() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }
        
        // Check if user has admin privileges for system settings
        $isAdmin = $_SESSION['role'] === 'admin';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->sanitizeInput($_POST);
            $settingsType = $input['settings_type'] ?? 'user';
            
            try {
                if ($settingsType === 'system' && $isAdmin) {
                    $this->updateSystemSettings($input);
                    $message = 'System settings updated successfully';
                } else if ($settingsType === 'user') {
                    $this->updateUserPreferences($input);
                    $message = 'User preferences updated successfully';
                } else {
                    throw new \Exception('Insufficient permissions');
                }
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => $message
                ]);
                
            } catch (\Exception $e) {
                $this->jsonResponse([
                    'error' => $e->getMessage()
                ], 400);
            }
        }
    }
    
    public function getSystemSettings() {
        $defaultSettings = [
            'farm_name' => 'Shantuka Farm',
            'timezone' => 'UTC',
            'temperature_unit' => 'celsius',
            'humidity_threshold' => 70,
            'soil_moisture_threshold' => 50,
            'notification_email' => '',
            'sensor_update_interval' => 30,
            'data_retention_days' => 365,
            'backup_enabled' => true,
            'maintenance_mode' => false
        ];
        
        try {
            $settings = $this->settingsModel->getAllAsArray();
            
            // Merge with defaults for missing settings
            return array_merge($defaultSettings, $settings);
            
        } catch (\Exception $e) {
            // Return defaults if database error
            return $defaultSettings;
        }
    }
    
    public function getUserPreferences() {
        try {
            $userModel = new UserModel();
            $user = $userModel->find($_SESSION['user_id']);
            
            return [
                'theme' => $user['theme'] ?? 'light',
                'language' => $user['language'] ?? 'en',
                'notifications' => $user['notifications'] ?? true,
                'email_alerts' => $user['email_alerts'] ?? true,
                'dashboard_layout' => $user['dashboard_layout'] ?? 'default'
            ];
            
        } catch (\Exception $e) {
            return [
                'theme' => 'light',
                'language' => 'en',
                'notifications' => true,
                'email_alerts' => true,
                'dashboard_layout' => 'default'
            ];
        }
    }
    
    private function updateSystemSettings($data) {
        $allowedSettings = [
            'farm_name', 'timezone', 'temperature_unit', 'humidity_threshold',
            'soil_moisture_threshold', 'notification_email', 'sensor_update_interval',
            'data_retention_days', 'backup_enabled', 'maintenance_mode'
        ];
        
        $settingsToUpdate = [];
        
        foreach ($allowedSettings as $key) {
            if (isset($data[$key])) {
                $value = $data[$key];
                
                // Validate specific settings
                if ($key === 'humidity_threshold' || $key === 'soil_moisture_threshold') {
                    $value = max(0, min(100, (int)$value));
                } elseif ($key === 'sensor_update_interval') {
                    $value = max(5, min(300, (int)$value));
                } elseif ($key === 'data_retention_days') {
                    $value = max(30, min(3650, (int)$value));
                } elseif ($key === 'backup_enabled' || $key === 'maintenance_mode') {
                    $value = $value === '1' || $value === 'true' ? 1 : 0;
                } elseif ($key === 'notification_email') {
                    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new \Exception('Invalid email format');
                    }
                }
                
                $settingsToUpdate[$key] = $value;
            }
        }
        
        // Update all settings at once
        if (!empty($settingsToUpdate)) {
            $this->settingsModel->updateMultiple($settingsToUpdate);
        }
    }
    
    private function updateUserPreferences($data) {
        $allowedPreferences = [
            'theme', 'language', 'notifications', 'email_alerts', 'dashboard_layout'
        ];
        
        $updateData = [];
        
        foreach ($allowedPreferences as $key) {
            if (isset($data[$key])) {
                $value = $data[$key];
                
                // Validate preferences
                if ($key === 'theme' && !in_array($value, ['light', 'dark', 'auto'])) {
                    continue;
                } elseif ($key === 'language' && !in_array($value, ['en', 'es', 'fr', 'de'])) {
                    continue;
                } elseif ($key === 'dashboard_layout' && !in_array($value, ['default', 'compact', 'detailed'])) {
                    continue;
                } elseif ($key === 'notifications' || $key === 'email_alerts') {
                    $value = $value === '1' || $value === 'true' ? 1 : 0;
                }
                
                $updateData[$key] = $value;
            }
        }
        
        if (!empty($updateData)) {
            $userModel = new UserModel();
            $userModel->update($_SESSION['user_id'], $updateData);
        }
    }
    
    public function reset() {
        // Check authentication and admin privileges
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->sanitizeInput($_POST);
            $resetType = $input['reset_type'] ?? '';
            
            try {
                switch ($resetType) {
                    case 'cache':
                        $this->clearCache();
                        $message = 'Cache cleared successfully';
                        break;
                        
                    case 'logs':
                        $this->clearLogs();
                        $message = 'Logs cleared successfully';
                        break;
                        
                    case 'settings':
                        $this->resetToDefaults();
                        $message = 'Settings reset to defaults successfully';
                        break;
                        
                    default:
                        throw new \Exception('Invalid reset type');
                }
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => $message
                ]);
                
            } catch (\Exception $e) {
                $this->jsonResponse([
                    'error' => $e->getMessage()
                ], 400);
            }
        }
    }
    
    private function clearCache() {
        $cacheDir = __DIR__ . '/../../cache/';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
    
    private function clearLogs() {
        $logDir = __DIR__ . '/../../logs/';
        if (is_dir($logDir)) {
            $files = glob($logDir . '*.log');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
    
    private function resetToDefaults() {
        // Delete all existing settings
        $settings = $this->settingsModel->getAll();
        foreach ($settings as $setting) {
            $this->settingsModel->delete($setting['id']);
        }
        
        // Insert default settings
        $this->settingsModel->insertDefaults();
    }
}
