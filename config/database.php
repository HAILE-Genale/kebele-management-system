<?php
// config/database.php

class Database {
    private $host = "localhost";
    private $db_name = "kebele_system";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // Setup PDO connection with MySQL
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            
            // Set attributes to use exceptions for errors and fetch associative arrays by default
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Database Connection Error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
