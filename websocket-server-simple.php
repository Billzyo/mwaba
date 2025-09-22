<?php
/**
 * Simple WebSocket Server for Real-time Farm Monitoring
 * A simplified version that should work with basic PHP setup
 */

require_once __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Factory;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer as RatchetHttpServer;
use Ratchet\WebSocket\WsServer;

class SimpleFarmWebSocket implements MessageComponentInterface {
    protected $clients;
    protected $sensorData;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->sensorData = [
            'temperature' => 25.5,
            'humidity' => 60.0,
            'soil_moisture' => 45.0
        ];
        
        echo "Simple Farm Monitoring WebSocket Server started\n";
        echo "Listening on ws://localhost:8080\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        
        // Send current sensor data to new client
        $conn->send(json_encode([
            'type' => 'initial_data',
            'data' => $this->sensorData,
            'timestamp' => time()
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data) {
            return;
        }

        switch ($data['type']) {
            case 'ping':
                $from->send(json_encode(['type' => 'pong', 'timestamp' => time()]));
                break;
            case 'sensor_data':
                $this->handleSensorData($data['data']);
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
        // Update sensor data
        $this->sensorData = array_merge($this->sensorData, $data);
        
        // Broadcast to all connected clients
        $this->broadcast([
            'type' => 'sensor_update',
            'data' => $data,
            'timestamp' => time()
        ]);
    }

    protected function broadcast($data) {
        $message = json_encode($data);
        
        foreach ($this->clients as $client) {
            $client->send($message);
        }
        
        echo "Broadcasting to " . count($this->clients) . " clients\n";
    }
}

try {
    // Create the event loop
    $loop = Factory::create();

    // Create WebSocket server
    $farmWebSocket = new SimpleFarmWebSocket();
    $webServer = new RatchetHttpServer(
        new WsServer($farmWebSocket)
    );

    // Create IoServer
    $server = IoServer::factory($webServer, 8080, '0.0.0.0');

    // Start the server
    $server->run();
} catch (Exception $e) {
    echo "Error starting WebSocket server: " . $e->getMessage() . "\n";
    echo "Make sure port 8080 is not in use by another application.\n";
}
?>
