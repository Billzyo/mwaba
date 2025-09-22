<?php
/**
 * WebSocket Test Script
 * Tests the WebSocket connection
 */

require_once __DIR__ . '/vendor/autoload.php';

use Ratchet\Client\WebSocket;
use Ratchet\Client\Connector;

$loop = React\EventLoop\Factory::create();
$connector = new Connector($loop);

echo "Testing WebSocket connection...\n";

$connector('ws://localhost:8080')
    ->then(function (WebSocket $conn) {
        echo "Connected to WebSocket server!\n";
        
        $conn->on('message', function ($msg) {
            echo "Received: " . $msg . "\n";
        });
        
        // Send a ping
        $conn->send(json_encode(['type' => 'ping']));
        
        // Close after 5 seconds
        $conn->close();
    }, function (\Exception $e) {
        echo "Could not connect: {$e->getMessage()}\n";
    });

$loop->run();
?>
