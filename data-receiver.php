<?php
/**
 * Legacy data receiver endpoint
 * Redirects to the new MVC structure
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define application paths
define('APP_PATH', __DIR__ . '/app');
define('PUBLIC_PATH', __DIR__ . '/public');

// Include the MVC application
require_once APP_PATH . '/Config/App.php';

// Initialize and run the application
try {
    $app = new \App\Config\App();
    $app->run();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage()
    ]);
}