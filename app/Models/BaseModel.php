<?php

namespace App\Models;

use App\Config\Database;

abstract class BaseModel {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM {$this->table}";
        $result = $this->db->query($sql);
        
        if (!$result) {
            return false;
        }
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->getPrimaryKey()} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        $types = str_repeat('s', count($data));
        $values = array_values($data);
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            return $this->db->getLastInsertId();
        }
        
        return false;
    }
    
    public function update($id, $data) {
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->getPrimaryKey()} = ?";
        $stmt = $this->db->prepare($sql);
        
        $types = str_repeat('s', count($data)) . 'i';
        $values = array_values($data);
        $values[] = $id;
        $stmt->bind_param($types, ...$values);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->getPrimaryKey()} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
    
    protected function getPrimaryKey() {
        return 'id';
    }
    
    public function find($id) {
        return $this->getById($id);
    }
    
    public function deleteAll() {
        $sql = "DELETE FROM {$this->table}";
        return $this->db->query($sql) !== false;
    }
    
    protected function executeQuery($sql, $params = []) {
        if (empty($params)) {
            $result = $this->db->query($sql);
            if (!$result) {
                return false;
            }
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            return $data;
        }
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
}
