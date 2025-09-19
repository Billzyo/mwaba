<?php

class App {
    private $router;
    
    public function __construct() {
        $this->router = new Router();
        $this->setupRoutes();
    }
    
    private function setupRoutes() {
        // Dashboard routes
        $this->router->get('/', 'DashboardController@index');
        $this->router->get('/dashboard', 'DashboardController@index');
        $this->router->get('/api/dashboard/latest', 'DashboardController@getLatestData');
        $this->router->get('/api/dashboard/charts', 'DashboardController@getChartData');
        
        // Sensor routes
        $this->router->post('/api/sensors/data', 'SensorController@receiveData');
        $this->router->get('/api/sensors', 'SensorController@getSensors');
        $this->router->get('/api/sensors/{id}/readings', 'SensorController@getSensorReadings');
        $this->router->get('/api/sensors/latest', 'SensorController@getLatestReadings');
        
        // Crop routes
        $this->router->get('/crops', 'CropController@index');
        $this->router->get('/api/crops', 'CropController@getCrops');
        $this->router->get('/api/crops/health', 'CropController@getCropHealth');
        $this->router->get('/api/crops/near-harvest', 'CropController@getCropsNearHarvest');
        $this->router->post('/api/crops/update-health', 'CropController@updateHealthStatus');
        $this->router->post('/api/crops', 'CropController@create');
        
        // Equipment routes
        $this->router->get('/equipment', 'EquipmentController@index');
        $this->router->get('/api/equipment', 'EquipmentController@getEquipment');
        $this->router->get('/api/equipment/active', 'EquipmentController@getActiveEquipment');
        $this->router->get('/api/equipment/type', 'EquipmentController@getEquipmentByType');
        $this->router->post('/api/equipment/update-status', 'EquipmentController@updateStatus');
        $this->router->get('/api/equipment/maintenance', 'EquipmentController@getMaintenanceSchedule');
        $this->router->post('/api/equipment/update-maintenance', 'EquipmentController@updateMaintenance');
        
        // Legacy routes for backward compatibility
        $this->router->post('/data-receiver.php', 'SensorController@receiveData');
    }
    
    public function run() {
        $this->router->dispatch();
    }
}
