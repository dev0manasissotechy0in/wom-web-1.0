<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'u972336461_wom_db';
    private $username = 'u972336461_wom_useri';
    private $password = '9s1&^V/b';
    public $conn;

    public function connect() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $this->logError("Connection Error: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
        
        return $this->conn;
    }

    private function logError($message) {
        $logDir = __DIR__ . '/../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, $logDir . '/errors.log');
    }
}
?>
