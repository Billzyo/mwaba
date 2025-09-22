<?php
/**
 * WebSocket Server for Real-time Farm Monitoring
 * Handles real-time communication between dashboard and IoT devices
 */

require_once __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Factory;
use React\Socket\Server;
use React\Http\Server as HttpServer;
use React\Stream\WritableResourceStream;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer as RatchetHttpServer;
use Ratchet\WebSocket\WsServer;

class FarmMonitoringWebSocket implements MessageComponentInterface {
    protected $clients;
    protected $sensorData;
    protected $alertThresholds;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->sensorData = [];
        $this->alertThresholds = [
            'temperature' => ['min' => 10, 'max' => 35],
            'humidity' => ['min' => 30, 'max' => 80],
            'soil_moisture' => ['min' => 20, 'max' => 80]
        ];
        
        echo "Farm Monitoring WebSocket Server started\n";
        echo "Listening on ws://localhost:8080\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        
        // Send current sensor data to new client
        if (!empty($this->sensorData)) {
            $conn->send(json_encode([
                'type' => 'initial_data',
                'data' => $this->sensorData,
                'timestamp' => time()
            ]));
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data) {
            return;
        }

        switch ($data['type']) {
            case 'sensor_data':
                $this->handleSensorData($data['data']);
                break;
            case 'ping':
                $from->send(json_encode(['type' => 'pong', 'timestamp' => time()]));
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function handleSensorData($data) {
        // Store latest sensor data
        $this->sensorData = array_merge($this->sensorData, $data);
        
        // Check for alerts
        $alerts = $this->checkAlerts($data);
        
        // Prepare broadcast data
        $broadcastData = [
            'type' => 'sensor_update',
            'data' => $data,
            'alerts' => $alerts,
            'timestamp' => time()
        ];
        
        // Broadcast to all connected clients
        $this->broadcast($broadcastData);
        
        // Log sensor data
        $this->logSensorData($data);
    }

    protected function checkAlerts($data) {
        $alerts = [];
        
        foreach ($data as $sensorType => $value) {
            if (isset($this->alertThresholds[$sensorType])) {
                $thresholds = $this->alertThresholds[$sensorType];
                
                if ($value < $thresholds['min']) {
                    $alerts[] = [
                        'type' => 'warning',
                        'sensor' => $sensorType,
                        'message' => "Low {$sensorType}: {$value} (below {$thresholds['min']})",
                        'value' => $value,
                        'threshold' => $thresholds['min']
                    ];
                } elseif ($value > $thresholds['max']) {
                    $alerts[] = [
                        'type' => 'warning',
                        'sensor' => $sensorType,
                        'message' => "High {$sensorType}: {$value} (above {$thresholds['max']})",
                        'value' => $value,
                        'threshold' => $thresholds['max']
                    ];
                }
            }
        }
        
        return $alerts;
    }

    protected function broadcast($data) {
        $message = json_encode($data);
        
        foreach ($this->clients as $client) {
            $client->send($message);
        }
        
        echo "Broadcasting to " . count($this->clients) . " clients\n";
    }

    protected function logSensorData($data) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ];
        
        // Log to file
        file_put_contents(
            'logs/sensor_data.log',
            json_encode($logEntry) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    // Method to manually broadcast alerts
    public function broadcastAlert($alert) {
        $data = [
            'type' => 'alert',
            'alert' => $alert,
            'timestamp' => time()
        ];
        
        $this->broadcast($data);
    }

    // Method to get current sensor data
    public function getCurrentSensorData() {
        return $this->sensorData;
    }
}

// Create the event loop
$loop = Factory::create();

// Create WebSocket server
$farmWebSocket = new FarmMonitoringWebSocket();
$webServer = new RatchetHttpServer(
    new WsServer($farmWebSocket)
);

// Create IoServer
$server = IoServer::factory($webServer, 8080, '0.0.0.0');

// Start the server
$server->run();
