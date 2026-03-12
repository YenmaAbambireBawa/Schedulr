<?php
/**
 * Database Configuration
 * Update these credentials based on your database setup
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;
    
    public function __construct() {
        $this->host     = getenv('MYSQLHOST')     ?: 'localhost';
        $this->db_name  = getenv('MYSQLDATABASE') ?: 'schedulr_db';
        $this->username = getenv('MYSQLUSER')     ?: 'root';
        $this->password = getenv('MYSQLPASSWORD') ?: '';
        $this->port     = getenv('MYSQLPORT')     ?: 3306;
    }
    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
    $this->conn = null;
    try {
        $dsn = "mysql:host=" . $this->host . 
               ";port=" . $this->port . 
               ";dbname=" . $this->db_name . 
               ";charset=utf8mb4";
        
        $this->conn = new PDO($dsn, $this->username, $this->password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);

    } catch(PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
    return $this->conn;
}
?>
