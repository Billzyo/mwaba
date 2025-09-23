<?php

namespace App\Controllers;

abstract class BaseController {
    protected $model;
    protected $view;
    
    public function __construct() {
        $this->initialize();
    }
    
    protected function initialize() {
        // Override in child classes
    }
    
    protected function render($viewName, $data = []) {
        $viewFile = __DIR__ . '/../Views/' . $viewName . '.php';
        
        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewName}");
        }
        
        // Extract data to variables
        extract($data);
        
        // Make constants available in view
        $PUBLIC_PATH = defined('PUBLIC_PATH') ? PUBLIC_PATH : '/public';
        $APP_PATH = defined('APP_PATH') ? APP_PATH : __DIR__ . '/..';
        // Compute base path for building URLs
        $BASE_PATH = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
        if ($BASE_PATH === '/' || $BASE_PATH === '\\') { $BASE_PATH = ''; }
        
        // Start output buffering
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        return $content;
    }
    
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url) {
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
        if ($basePath === '/' || $basePath === '\\') { $basePath = ''; }
        // If absolute-app path is provided (starts with '/'), prefix with base path
        if (strpos($url, '/') === 0) {
            $url = $basePath . $url;
        }
        header("Location: {$url}");
        exit;
    }
    
    protected function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = ucfirst($field) . ' must be a valid email';
            }
            
            if (strpos($rule, 'numeric') !== false && !is_numeric($value)) {
                $errors[$field] = ucfirst($field) . ' must be numeric';
            }
        }
        
        return $errors;
    }
    
    protected function sanitizeInput($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}
