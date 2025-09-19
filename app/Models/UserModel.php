<?php

namespace App\Models;

use App\Models\BaseModel;

class UserModel extends BaseModel {
    protected $table = 'users';
    
    protected function getPrimaryKey() {
        return 'user_id';
    }
    
    public function getUserByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function authenticateUser($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user) {
            // Check if password is hashed (starts with $2y$) or plain text
            if (password_get_info($user['password'])['algo'] !== null) {
                // Password is hashed, verify normally
                if (password_verify($password, $user['password'])) {
                    return $user;
                }
            } else {
                // Password is plain text, check directly (for backward compatibility)
                if ($user['password'] === $password) {
                    // Hash the password for future use
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $this->updatePassword($user['user_id'], $hashedPassword);
                    return $user;
                }
            }
        }
        
        return false;
    }
    
    public function updatePassword($userId, $hashedPassword) {
        $sql = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $hashedPassword, $userId);
        return $stmt->execute();
    }
    
    public function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        
        return $stmt->execute();
    }
    
    public function getUsersByRole($role) {
        $sql = "SELECT * FROM users WHERE role = ? ORDER BY full_name";
        return $this->executeQuery($sql, [$role]);
    }
    
    public function createUser($userData) {
        $requiredFields = ['username', 'password', 'full_name', 'email', 'role'];
        
        foreach ($requiredFields as $field) {
            if (!isset($userData[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
        
        return $this->create($userData);
    }
}
