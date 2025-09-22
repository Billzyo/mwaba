<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Config\AuthMiddleware;
use App\Config\Cache;

class PerformanceController extends BaseController {
    private $cache;
    
    protected function initialize() {
        $this->cache = Cache::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Performance Dashboard
     */
    public function index() {
        AuthMiddleware::requireRole('admin');
        
        try {
            $currentUser = AuthMiddleware::getCurrentUser();
            
            $performanceData = [
                'cache_stats' => $this->cache->getStats(),
                'system_metrics' => $this->collectSystemMetrics(),
                'database_stats' => $this->getDatabaseStats(),
                'api_performance' => $this->getApiPerformance(),
                'memory_usage' => $this->getMemoryUsage(),
                'query_performance' => $this->getQueryPerformance()
            ];
            
            $data = [
                'performanceData' => $performanceData,
                'pageTitle' => 'Performance Monitor',
                'activeMenu' => 'performance',
                'currentUser' => $currentUser
            ];
            
            echo $this->render('performance/index', $data);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get cache statistics
     */
    public function getCacheStats() {
        AuthMiddleware::requireRole('admin');
        
        try {
            $stats = $this->cache->getStats();
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $stats,
                'timestamp' => time()
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Clear cache
     */
    public function clearCache() {
        AuthMiddleware::requireRole('admin');
        
        try {
            $this->cache->flush();
            
            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Cache cleared successfully',
                'timestamp' => time()
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get system metrics
     */
    public function getSystemMetrics() {
        AuthMiddleware::requireRole('admin');
        
        try {
            $metrics = $this->collectSystemMetrics();
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $metrics,
                'timestamp' => time()
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get system metrics (private methods)
     */
    private function collectSystemMetrics() {
        return [
            'cpu_usage' => $this->getCpuUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'load_average' => $this->getLoadAverage(),
            'uptime' => $this->getUptime(),
            'php_version' => PHP_VERSION,
            'php_memory_limit' => ini_get('memory_limit'),
            'php_max_execution_time' => ini_get('max_execution_time')
        ];
    }
    
    private function getCpuUsage() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => $load[0] ?? 0,
                '5min' => $load[1] ?? 0,
                '15min' => $load[2] ?? 0
            ];
        }
        
        // Windows fallback
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic cpu get loadpercentage /value');
            if (is_string($output) && preg_match('/LoadPercentage=(\d+)/', $output, $matches)) {
                return ['current' => (int)$matches[1]];
            }
        }
        
        return ['current' => 0];
    }
    
    private function getMemoryUsage() {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit'),
            'percentage' => $this->getMemoryPercentage()
        ];
    }
    
    private function getMemoryPercentage() {
        $limit = ini_get('memory_limit');
        $current = memory_get_usage(true);
        
        if ($limit === '-1') {
            return 0;
        }
        
        $limitBytes = $this->convertToBytes($limit);
        return ($current / $limitBytes) * 100;
    }
    
    private function convertToBytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    private function getDiskUsage() {
        $total = disk_total_space('.');
        $free = disk_free_space('.');
        $used = $total - $free;
        
        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'percentage' => ($used / $total) * 100
        ];
    }
    
    private function getLoadAverage() {
        if (function_exists('sys_getloadavg')) {
            return sys_getloadavg();
        }
        
        return [0, 0, 0];
    }
    
    private function getUptime() {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('net stats srv');
            if (is_string($output) && preg_match('/Statistics since (.+)/', $output, $matches)) {
                return $matches[1];
            }
        } else {
            $uptime = shell_exec('uptime');
            return trim($uptime);
        }
        
        return 'Unknown';
    }
    
    private function getDatabaseStats() {
        // This would typically connect to the database and get stats
        return [
            'connections' => 1,
            'queries_per_second' => 25,
            'slow_queries' => 2,
            'cache_hit_ratio' => 0.95
        ];
    }
    
    private function getApiPerformance() {
        // This would typically come from application logs
        return [
            'avg_response_time' => '150ms',
            'requests_per_minute' => 120,
            'error_rate' => 0.02,
            'endpoints' => [
                '/mwaba/dashboard' => ['avg_time' => '120ms', 'requests' => 45],
                '/mwaba/api/realtime/data' => ['avg_time' => '80ms', 'requests' => 60],
                '/mwaba/analytics' => ['avg_time' => '250ms', 'requests' => 15]
            ]
        ];
    }
    
    private function getQueryPerformance() {
        // This would typically come from database query logs
        return [
            'slowest_queries' => [
                [
                    'query' => 'SELECT * FROM sensor_readings WHERE...',
                    'execution_time' => '250ms',
                    'count' => 5
                ],
                [
                    'query' => 'SELECT AVG(value) FROM sensor_readings...',
                    'execution_time' => '180ms',
                    'count' => 12
                ]
            ],
            'total_queries' => 1250,
            'avg_execution_time' => '45ms',
            'cache_hits' => 1180,
            'cache_misses' => 70
        ];
    }
}
