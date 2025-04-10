<?php  
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'cadastro_login';

class Database {
    public $conn;

    public function __construct($host, $user, $pass, $db) {
        $this->conn = new mysqli($host, $user, $pass, $db);
        
        if ($this->conn->connect_error) {
            die(json_encode([
                'status' => 'error',
                'message' => 'Connection failed: ' . $this->conn->connect_error
            ]));
        }
        
        $this->conn->set_charset('utf8mb4');
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

$database = new Database($host, $user, $pass, $db);
$conn = $database->getConnection();
?>