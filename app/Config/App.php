<?php

namespace App\Config;

use App\Config\Router;
use App\Controllers\DashboardController;
use App\Controllers\SensorController;
use App\Controllers\CropController;
use App\Controllers\EquipmentController;

class App {
    private $router;
    
    public function __construct() {
        $this->registerAutoloader();
        $this->router = new Router();
        $this->setupRoutes();
    }
    
    private function registerAutoloader() {
        spl_autoload_register(function ($class) {
            $file = APP_PATH . '/' . str_replace('\\', '/', $class) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
    
    private function setupRoutes() {
        // Dashboard routes
        $this->router->add('GET', '/mwaba/', [DashboardController::class, 'index']);
        $this->router->add('GET', '/mwaba/dashboard', [DashboardController::class, 'index']);
        
        // Sensor routes
        $this->router->add('POST', '/mwaba/sensor/receive', [SensorController::class, 'receiveData']);
        $this->router->add('POST', '/mwaba/data-receiver.php', [SensorController::class, 'receiveData']);
        
        // Crop routes
        $this->router->add('GET', '/mwaba/crops', [CropController::class, 'index']);
        
        // Equipment routes
        $this->router->add('GET', '/mwaba/equipment', [EquipmentController::class, 'index']);
    }
    
    public function run() {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        
        $callback = $this->router->dispatch($uri, $method);
        
        if ($callback) {
            if (is_array($callback) && count($callback) === 2) {
                $controllerName = $callback[0];
                $methodName = $callback[1];
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
}
