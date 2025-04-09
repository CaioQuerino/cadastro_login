<?php  
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_test';

class Database {
    public $conn;

    public function __construct($host, $user, $pass, $db) {
        $this->conn = mysqli_connect($host, $user, $pass, $db);
        
        if (!$this->conn) {
            die(json_encode([
                'status' => 'error',
                'message' => 'Connection failed: ' . mysqli_connect_error()
            ]));
        }
        
        mysqli_set_charset($this->conn, 'utf8mb4');
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        mysqli_close($this->conn);
    }
}

$database = new Database($host, $user, $pass, $db);
$conn = $database->getConnection();
?>