<?php

namespace App\Config;

class AuthMiddleware {
    private static function startSessionIfNeeded() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function requireAuth() {
        self::startSessionIfNeeded();
        
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\'); if ($base === '/' || $base === '\\') { $base = ''; }
            header('Location: ' . $base . '/login?error=session_expired');
            exit;
        }
    }
    
    public static function requireRole($requiredRole) {
        self::requireAuth();
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\'); if ($base === '/' || $base === '\\') { $base = ''; }
            header('Location: ' . $base . '/dashboard?error=insufficient_permissions');
            exit;
        }
    }
    
    public static function isLoggedIn() {
        self::startSessionIfNeeded();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'] ?? null,
                'username' => $_SESSION['username'] ?? null,
                'full_name' => $_SESSION['full_name'] ?? null,
                'email' => $_SESSION['email'] ?? null,
                'role' => $_SESSION['role'] ?? null
            ];
        }
        return null;
    }
    
    public static function logout() {
        self::startSessionIfNeeded();
        session_destroy();
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\'); if ($base === '/' || $base === '\\') { $base = ''; }
        header('Location: ' . $base . '/login');
        exit;
    }
}
