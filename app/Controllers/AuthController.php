<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController {
    private $userModel;
    
    protected function initialize() {
        $this->userModel = new UserModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $data = [
            'pageTitle' => 'Login - Farm Monitoring System'
        ];
        
        echo $this->render('auth/login', $data);
    }
    
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $this->redirect('/login?error=empty_fields');
            return;
        }
        
        // Authenticate user
        $user = $this->userModel->authenticateUser($username, $password);
        
        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Update last login
            $this->userModel->updateLastLogin($user['user_id']);
            
            // Redirect to dashboard
            $this->redirect('/dashboard');
        } else {
            $this->redirect('/login?error=invalid_credentials');
        }
    }
    
    public function logout() {
        session_start();
        session_destroy();
        $this->redirect('/login');
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }
    
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            exit;
        }
    }
}
