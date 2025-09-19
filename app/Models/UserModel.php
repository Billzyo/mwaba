<?php

require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel {
    protected $table = 'users';
    
    protected function getPrimaryKey() {
        return 'user_id';
    }
    
    public function getUserByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        return $this->executeQuery($sql, [$username]);
    }
    
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        return $this->executeQuery($sql, [$email]);
    }
    
    public function authenticateUser($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
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
