<?php
/**
 * Farm Monitoring System - Main Entry Point
 * MVC Architecture Implementation
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Define application paths
define('APP_PATH', __DIR__ . '/app');
define('PUBLIC_PATH', __DIR__ . '/public');

// Include autoloader
require_once APP_PATH . '/Config/App.php';

// Initialize and run the application
try {
    $app = new App();
    $app->run();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage()
    ]);
}
