<?php

namespace App\Config;

/**
 * Cache Manager for Performance Optimization
 * Supports multiple cache backends: File, Redis, Memory
 */
class Cache {
    private static $instance = null;
    private $driver;
    private $config;
    
    // Cache drivers
    const DRIVER_FILE = 'file';
    const DRIVER_REDIS = 'redis';
    const DRIVER_MEMORY = 'memory';
    
    // Cache prefixes
    const PREFIX_SENSOR = 'sensor_';
    const PREFIX_USER = 'user_';
    const PREFIX_ANALYTICS = 'analytics_';
    const PREFIX_DASHBOARD = 'dashboard_';
    
    private function __construct() {
        $this->config = [
            'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
            'ttl' => $_ENV['CACHE_TTL'] ?? 3600, // 1 hour default
            'file_path' => __DIR__ . '/../../cache/',
            'redis' => [
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'password' => $_ENV['REDIS_PASSWORD'] ?? null,
                'database' => $_ENV['REDIS_DB'] ?? 0
            ]
        ];
        
        $this->initializeDriver();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initializeDriver() {
        switch ($this->config['driver']) {
            case self::DRIVER_REDIS:
                $this->driver = new RedisCache($this->config['redis']);
                break;
            case self::DRIVER_MEMORY:
                $this->driver = new MemoryCache();
                break;
            case self::DRIVER_FILE:
            default:
                $this->driver = new FileCache($this->config['file_path']);
                break;
        }
    }
    
    /**
     * Store data in cache
     */
    public function put($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->config['ttl'];
        return $this->driver->put($key, $value, $ttl);
    }
    
    /**
     * Retrieve data from cache
     */
    public function get($key, $default = null) {
        return $this->driver->get($key, $default);
    }
    
    /**
     * Check if key exists in cache
     */
    public function has($key) {
        return $this->driver->has($key);
    }
    
    /**
     * Remove data from cache
     */
    public function forget($key) {
        return $this->driver->forget($key);
    }
    
    /**
     * Clear all cache
     */
    public function flush() {
        return $this->driver->flush();
    }
    
    /**
     * Get or set cache value
     */
    public function remember($key, $ttl, $callback) {
        $value = $this->get($key);
        
        if ($value === null) {
            $value = $callback();
            $this->put($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * Cache sensor data
     */
    public function cacheSensorData($sensorId, $data, $ttl = 300) {
        $key = self::PREFIX_SENSOR . $sensorId;
        return $this->put($key, $data, $ttl);
    }
    
    /**
     * Get cached sensor data
     */
    public function getCachedSensorData($sensorId) {
        $key = self::PREFIX_SENSOR . $sensorId;
        return $this->get($key);
    }
    
    /**
     * Cache analytics data
     */
    public function cacheAnalyticsData($type, $params, $data, $ttl = 1800) {
        $key = self::PREFIX_ANALYTICS . $type . '_' . md5(serialize($params));
        return $this->put($key, $data, $ttl);
    }
    
    /**
     * Get cached analytics data
     */
    public function getCachedAnalyticsData($type, $params) {
        $key = self::PREFIX_ANALYTICS . $type . '_' . md5(serialize($params));
        return $this->get($key);
    }
    
    /**
     * Cache dashboard data
     */
    public function cacheDashboardData($userId, $data, $ttl = 600) {
        $key = self::PREFIX_DASHBOARD . $userId;
        return $this->put($key, $data, $ttl);
    }
    
    /**
     * Get cached dashboard data
     */
    public function getCachedDashboardData($userId) {
        $key = self::PREFIX_DASHBOARD . $userId;
        return $this->get($key);
    }
    
    /**
     * Get cache statistics
     */
    public function getStats() {
        return $this->driver->getStats();
    }
}

/**
 * File Cache Implementation
 */
class FileCache {
    private $path;
    
    public function __construct($path) {
        $this->path = $path;
        
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }
    
    public function put($key, $value, $ttl) {
        $file = $this->getFilePath($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }
    
    public function get($key, $default = null) {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($file));
        
        if ($data['expires'] < time()) {
            unlink($file);
            return $default;
        }
        
        return $data['value'];
    }
    
    public function has($key) {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($file));
        return $data['expires'] >= time();
    }
    
    public function forget($key) {
        $file = $this->getFilePath($key);
        return file_exists($file) ? unlink($file) : true;
    }
    
    public function flush() {
        $files = glob($this->path . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
    
    public function getStats() {
        $files = glob($this->path . '*.cache');
        $totalSize = 0;
        $expiredCount = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                $expiredCount++;
            }
        }
        
        return [
            'total_files' => count($files),
            'total_size' => $totalSize,
            'expired_files' => $expiredCount,
            'driver' => 'file'
        ];
    }
    
    private function getFilePath($key) {
        return $this->path . md5($key) . '.cache';
    }
}

/**
 * Memory Cache Implementation
 */
class MemoryCache {
    private $cache = [];
    
    public function put($key, $value, $ttl) {
        $this->cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        return true;
    }
    
    public function get($key, $default = null) {
        if (!isset($this->cache[$key])) {
            return $default;
        }
        
        if ($this->cache[$key]['expires'] < time()) {
            unset($this->cache[$key]);
            return $default;
        }
        
        return $this->cache[$key]['value'];
    }
    
    public function has($key) {
        return isset($this->cache[$key]) && $this->cache[$key]['expires'] >= time();
    }
    
    public function forget($key) {
        unset($this->cache[$key]);
        return true;
    }
    
    public function flush() {
        $this->cache = [];
        return true;
    }
    
    public function getStats() {
        $expiredCount = 0;
        foreach ($this->cache as $item) {
            if ($item['expires'] < time()) {
                $expiredCount++;
            }
        }
        
        return [
            'total_items' => count($this->cache),
            'expired_items' => $expiredCount,
            'memory_usage' => memory_get_usage(true),
            'driver' => 'memory'
        ];
    }
}

/**
 * Redis Cache Implementation
 */
class RedisCache {
    private $redis;
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->redis = new \Redis();
            $this->redis->connect($this->config['host'], $this->config['port']);
            
            if ($this->config['password']) {
                $this->redis->auth($this->config['password']);
            }
            
            $this->redis->select($this->config['database']);
        } catch (Exception $e) {
            // Fallback to file cache if Redis is not available
            throw new Exception('Redis connection failed: ' . $e->getMessage());
        }
    }
    
    public function put($key, $value, $ttl) {
        return $this->redis->setex($key, $ttl, serialize($value));
    }
    
    public function get($key, $default = null) {
        $value = $this->redis->get($key);
        return $value !== false ? unserialize($value) : $default;
    }
    
    public function has($key) {
        return $this->redis->exists($key) > 0;
    }
    
    public function forget($key) {
        return $this->redis->del($key) > 0;
    }
    
    public function flush() {
        return $this->redis->flushdb();
    }
    
    public function getStats() {
        $info = $this->redis->info();
        return [
            'total_keys' => $info['db0']['keys'] ?? 0,
            'memory_usage' => $info['used_memory'] ?? 0,
            'uptime' => $info['uptime_in_seconds'] ?? 0,
            'driver' => 'redis'
        ];
    }
}
