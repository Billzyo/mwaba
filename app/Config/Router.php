<?php

namespace App\Config;

class Router {
    private $routes = [];
    private $basePath = '';
    
    public function __construct($basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }
    
    public function add($method, $path, $callback) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'callback' => $callback
        ];
    }
    
    public function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $this->basePath . $path,
            'handler' => $handler
        ];
    }
    
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    public function dispatch($uri, $method) {
        $uri = strtok($uri, '?'); // Remove query string
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                return $route['callback'] ?? $route['handler'] ?? null;
            }
        }
        
        return null; // No route found
    }
    
    private function matchPath($routePath, $requestPath) {
        // Convert route path to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $requestPath);
    }
    
    private function extractParams($routePath, $requestPath) {
        $params = [];
        
        // Extract parameter names from route
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        
        // Convert route path to regex and extract values
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        preg_match($pattern, $requestPath, $matches);
        
        // Combine parameter names with values
        for ($i = 1; $i < count($matches); $i++) {
            $params[$paramNames[1][$i - 1]] = $matches[$i];
        }
        
        return $params;
    }
    
    private function executeHandler($handler, $params = []) {
        if (is_string($handler)) {
            // Format: "Controller@method"
            if (strpos($handler, '@') !== false) {
                list($controllerName, $method) = explode('@', $handler);
                
                $controllerFile = __DIR__ . '/../Controllers/' . $controllerName . '.php';
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    
                    $controller = new $controllerName();
                    if (method_exists($controller, $method)) {
                        call_user_func_array([$controller, $method], $params);
                        return;
                    }
                }
            }
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }
        
        $this->handleNotFound();
    }
    
    private function handleNotFound() {
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }
}
