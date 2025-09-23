<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Config\AuthMiddleware;

class UserController extends BaseController {
    private $userModel;
    
    protected function initialize() {
        $this->userModel = new UserModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function index() {
        // Require admin authentication
        AuthMiddleware::requireRole('admin');
        
        try {
            $currentUser = AuthMiddleware::getCurrentUser();
            $users = $this->userModel->getAll();
            
            $data = [
                'users' => $users,
                'currentUser' => $currentUser,
                'pageTitle' => 'User Management',
                'activeMenu' => 'users'
            ];
            
            echo $this->render('users/index', $data);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function create() {
        // Require admin authentication
        AuthMiddleware::requireRole('admin');
        
        try {
            $currentUser = AuthMiddleware::getCurrentUser();
            
            $data = [
                'currentUser' => $currentUser,
                'pageTitle' => 'Create New User',
                'activeMenu' => 'users',
                'roles' => ['admin', 'farmer', 'viewer']
            ];
            
            echo $this->render('users/create', $data);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    public function store() {
        // Require admin authentication
        AuthMiddleware::requireRole('admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users');
            return;
        }
        
        try {
            $userData = [
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'full_name' => trim($_POST['full_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'role' => $_POST['role'] ?? 'viewer'
            ];
            
            // Validate required fields
            $requiredFields = ['username', 'password', 'full_name', 'email'];
            foreach ($requiredFields as $field) {
                if (empty($userData[$field])) {
                    $this->redirect('/users/create?error=missing_fields');
                    return;
                }
            }
            
            // Check if username or email already exists
            $existingUser = $this->userModel->getUserByUsername($userData['username']);
            if ($existingUser) {
                $this->redirect('/users/create?error=username_exists');
                return;
            }
            
            $existingEmail = $this->userModel->getUserByEmail($userData['email']);
            if ($existingEmail) {
                $this->redirect('/users/create?error=email_exists');
                return;
            }
            
            // Hash password
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Create user
            $userId = $this->userModel->createUser($userData);
            
            if ($userId) {
                $this->redirect('/users?success=user_created');
            } else {
                $this->redirect('/users/create?error=creation_failed');
            }
            
        } catch (Exception $e) {
            $this->redirect('/users/create?error=server_error');
        }
    }
    
    public function edit() {
        // Require admin authentication
        AuthMiddleware::requireRole('admin');
        
        $userId = $_GET['id'] ?? null;
        
        if (!$userId) {
            $this->redirect('/users');
            return;
        }
        
        try {
            $currentUser = AuthMiddleware::getCurrentUser();
            $user = $this->userModel->getById($userId);
            
            if (!$user) {
                $this->redirect('/users?error=user_not_found');
                return;
            }
            
            $data = [
                'user' => $user,
                'currentUser' => $currentUser,
                'pageTitle' => 'Edit User',
                'activeMenu' => 'users',
                'roles' => ['admin', 'farmer', 'viewer']
            ];
            
            echo $this->render('users/edit', $data);
            
        } catch (Exception $e) {
            $this->redirect('/users?error=server_error');
        }
    }
    
    public function update() {
        // Require admin authentication
        AuthMiddleware::requireRole('admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users');
            return;
        }
        
        $userId = $_POST['user_id'] ?? null;
        
        if (!$userId) {
            $this->redirect('/users');
            return;
        }
        
        try {
            $userData = [
                'full_name' => trim($_POST['full_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'role' => $_POST['role'] ?? 'viewer'
            ];
            
            // Validate required fields
            if (empty($userData['full_name']) || empty($userData['email'])) {
                $this->redirect("/users/edit?id={$userId}&error=missing_fields");
                return;
            }
            
            // Check if email already exists for another user
            $existingEmail = $this->userModel->getUserByEmail($userData['email']);
            if ($existingEmail && $existingEmail['user_id'] != $userId) {
                $this->redirect("/users/edit?id={$userId}&error=email_exists");
                return;
            }
            
            // Update password if provided
            if (!empty($_POST['password'])) {
                $userData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            // Update user
            $result = $this->userModel->update($userId, $userData);
            
            if ($result) {
                $this->redirect('/users?success=user_updated');
            } else {
                $this->redirect("/users/edit?id={$userId}&error=update_failed");
            }
            
        } catch (Exception $e) {
            $this->redirect("/users/edit?id={$userId}&error=server_error");
        }
    }
    
    public function delete() {
        // Require admin authentication
        AuthMiddleware::requireRole('admin');
        
        $userId = $_POST['user_id'] ?? null;
        
        if (!$userId) {
            $this->redirect('/users');
            return;
        }
        
        try {
            $currentUser = AuthMiddleware::getCurrentUser();
            
            // Prevent admin from deleting themselves
            if ($userId == $currentUser['user_id']) {
                $this->redirect('/users?error=cannot_delete_self');
                return;
            }
            
            $result = $this->userModel->delete($userId);
            
            if ($result) {
                $this->redirect('/users?success=user_deleted');
            } else {
                $this->redirect('/users?error=delete_failed');
            }
            
        } catch (Exception $e) {
            $this->redirect('/users?error=server_error');
        }
    }
}
