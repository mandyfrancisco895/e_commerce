<?php
// Set PHP timezone to Philippine Time
date_default_timezone_set('Asia/Manila');

// Disable display_errors for production to prevent breaking downloads
// We log errors instead so they don't corrupt the file stream
ini_set('display_errors', 0); 
ini_set('log_errors', 1);
error_reporting(E_ALL);

class Database {
    private $host = "127.0.0.1";
    private $dbname = "e-commerce_app"; 
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=utf8",
                                  $this->username, 
                                  $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set Database session to Philippine Time (+08:00)
            $this->conn->exec("SET time_zone = '+08:00'");
            
        } catch(PDOException $e) {
            // Echoing here can also break downloads if not careful, 
            // but for connection errors, it's usually acceptable to fail hard.
            echo "Database connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
// NOTICE: The closing php tag has been removed intentionally.
// Do not add it back. This prevents whitespace injection.