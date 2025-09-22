<?php

namespace App\Config;

use App\Config\Router;
use App\Controllers\DashboardController;
use App\Controllers\SensorController;
use App\Controllers\CropController;
use App\Controllers\EquipmentController;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\RealtimeController;
use App\Controllers\AnalyticsController;
use App\Controllers\PerformanceController;
use App\Controllers\SettingsController;

class App {
    private $router;
    
    public function __construct() {
        $this->router = new Router();
        $this->setupRoutes();
    }
    
    private function setupRoutes() {
        // Authentication routes (public)
        $this->router->add('GET', '/mwaba/login', [AuthController::class, 'login']);
        $this->router->add('POST', '/mwaba/auth/authenticate', [AuthController::class, 'authenticate']);
        $this->router->add('GET', '/mwaba/logout', [AuthController::class, 'logout']);
        
        // Dashboard routes (protected)
        $this->router->add('GET', '/mwaba/', [DashboardController::class, 'index']);
        $this->router->add('GET', '/mwaba/dashboard', [DashboardController::class, 'index']);
        
        // Sensor routes (protected)
        $this->router->add('POST', '/mwaba/sensor/receive', [SensorController::class, 'receiveData']);
        $this->router->add('POST', '/mwaba/data-receiver.php', [SensorController::class, 'receiveData']);
        
        // Crop routes (protected)
        $this->router->add('GET', '/mwaba/crops', [CropController::class, 'index']);
        
        // Equipment routes (protected)
        $this->router->add('GET', '/mwaba/equipment', [EquipmentController::class, 'index']);
        
        // User management routes (admin only)
        $this->router->add('GET', '/mwaba/users', [UserController::class, 'index']);
        $this->router->add('GET', '/mwaba/users/create', [UserController::class, 'create']);
        $this->router->add('POST', '/mwaba/users/store', [UserController::class, 'store']);
        $this->router->add('GET', '/mwaba/users/edit', [UserController::class, 'edit']);
        $this->router->add('POST', '/mwaba/users/update', [UserController::class, 'update']);
        $this->router->add('POST', '/mwaba/users/delete', [UserController::class, 'delete']);
        
        // Real-time API routes (protected)
        $this->router->add('GET', '/mwaba/api/realtime/data', [RealtimeController::class, 'getLatestData']);
        $this->router->add('GET', '/mwaba/api/realtime/charts', [RealtimeController::class, 'getChartData']);
        $this->router->add('GET', '/mwaba/api/realtime/alerts', [RealtimeController::class, 'getAlerts']);
        $this->router->add('POST', '/mwaba/api/realtime/broadcast', [RealtimeController::class, 'broadcastSensorData']);
        
        // Analytics routes (protected)
        $this->router->add('GET', '/mwaba/analytics', [AnalyticsController::class, 'index']);
        $this->router->add('GET', '/mwaba/api/analytics/data', [AnalyticsController::class, 'getAnalyticsData']);
        $this->router->add('GET', '/mwaba/api/analytics/report', [AnalyticsController::class, 'generateReport']);
        $this->router->add('GET', '/mwaba/api/analytics/export', [AnalyticsController::class, 'exportData']);
        
        // Performance routes (admin only)
        $this->router->add('GET', '/mwaba/performance', [PerformanceController::class, 'index']);
        $this->router->add('GET', '/mwaba/api/performance/metrics', [PerformanceController::class, 'getSystemMetrics']);
        $this->router->add('GET', '/mwaba/api/performance/cache-stats', [PerformanceController::class, 'getCacheStats']);
        $this->router->add('POST', '/mwaba/api/performance/clear-cache', [PerformanceController::class, 'clearCache']);
        
        // Settings routes (protected)
        $this->router->add('GET', '/mwaba/settings', [SettingsController::class, 'index']);
        $this->router->add('POST', '/mwaba/settings/update', [SettingsController::class, 'update']);
        $this->router->add('POST', '/mwaba/settings/reset', [SettingsController::class, 'reset']);
        
        // Additional API routes for dashboard and other features
        $this->router->add('GET', '/mwaba/api/dashboard/latest', [DashboardController::class, 'getLatestData']);
        $this->router->add('POST', '/mwaba/api/crops/update-health', [CropController::class, 'updateHealthStatus']);
        $this->router->add('POST', '/mwaba/api/equipment/update-status', [EquipmentController::class, 'updateStatus']);
        $this->router->add('POST', '/mwaba/api/equipment/update-maintenance', [EquipmentController::class, 'updateMaintenance']);
    }
    
    public function run() {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        $callback = $this->router->dispatch($uri, $method);
        
        if ($callback) {
            if (is_array($callback) && count($callback) === 2) {
                $controllerName = $callback[0];
                $methodName = $callback[1];
                
                // Ensure controller class is loaded
                if (!class_exists($controllerName)) {
                    $this->loadController($controllerName);
                }
                
                $controller = new $controllerName();
                $controller->$methodName();
            } else if (is_callable($callback)) {
                call_user_func($callback);
            }
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
    }
    
    private function loadController($controllerName) {
        // First load BaseController if not already loaded
        if (!class_exists('App\\Controllers\\BaseController')) {
            $baseControllerFile = APP_PATH . '/Controllers/BaseController.php';
            if (file_exists($baseControllerFile)) {
                require_once $baseControllerFile;
            }
        }
        
        // Load Database class if not already loaded
        if (!class_exists('App\\Config\\Database')) {
            $databaseFile = APP_PATH . '/Config/Database.php';
            if (file_exists($databaseFile)) {
                require_once $databaseFile;
            }
        }
        
        // Load BaseModel if not already loaded
        if (!class_exists('App\\Models\\BaseModel')) {
            $baseModelFile = APP_PATH . '/Models/BaseModel.php';
            if (file_exists($baseModelFile)) {
                require_once $baseModelFile;
            }
        }
        
        // Load all Model classes
        $modelFiles = [
            'SensorModel.php',
            'CropModel.php', 
            'EquipmentModel.php',
            'UserModel.php',
            'SettingsModel.php'
        ];
        
        // Load AuthMiddleware
        $authMiddlewareFile = APP_PATH . '/Config/AuthMiddleware.php';
        if (file_exists($authMiddlewareFile)) {
            require_once $authMiddlewareFile;
        }
        
        // Load Cache class
        $cacheFile = APP_PATH . '/Config/Cache.php';
        if (file_exists($cacheFile)) {
            require_once $cacheFile;
        }
        
        foreach ($modelFiles as $modelFile) {
            $modelPath = APP_PATH . '/Models/' . $modelFile;
            if (file_exists($modelPath)) {
                require_once $modelPath;
            }
        }
        
        // Load the requested controller
        $controllerFile = APP_PATH . '/Controllers/' . basename($controllerName) . '.php';
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
        }
    }
}
