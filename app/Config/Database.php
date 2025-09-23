<?php

namespace App\Config;

class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;
    
    public function __construct() {
        $this->host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
        $this->username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
        $this->password = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
        $this->database = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'farm_monitoring';
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new \mysqli($this->host, $this->username, $this->password, $this->database);
            
            if ($this->connection->connect_error) {
                throw new \Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
        } catch (\Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
    
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }
    
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    public function __destruct() {
        $this->close();
    }
}
