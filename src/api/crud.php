<?php

include_once = __DIR__ . '/conn.php';

$BASE_URL = '/cadastro_login/src/'

class Crud {
    private $conn;
    private static $instance = null;

    // private constructor to prevent direct instantiation
    private function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Singleton pattern to ensure single instance
    private static function getInstance($dbConnection) {
        if (self::$instance === null) {
            self::$instance = new self($dbConnection);
        }
        return self::$instance;
    }

    // LOGIN FUNCTION (NEW)
    private function login($email, $password) {
        // Find user by email
        $user = $this->readAll('users', ['email' => $email], 1);
        
        if (empty($user)) {
            return ['status' => false, 'message' => 'User not found'];
        }
        
        $user = $user[0]; // Get first result
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            header(`Location: {$BASE_URL}api/profile.php`);
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => true,
                'use_strict_mode' => true
            ]);
            session_regenerate_id(true);
            $_SESSION['user'] = $user;
            
            
            unset($user['password']);
            return [
                'status' => true,
                'message' => 'Login successful',
                'user' => $user
            ];
        } else {
            return ['status' => false, 'message' => 'Incorrect password'];
        }
    }

    // Create operation
    private function create($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $values = array_values($data);

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $types = str_repeat('s', count($values)); // All parameters treated as strings
        $stmt->bind_param($types, ...$values);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $this->conn->insert_id;
    }

    // Read operation (single record)
    private function read($table, $id, $idColumn = 'id') {
        $sql = "SELECT * FROM $table WHERE $idColumn = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    // Read all operation (multiple records)
    private function readAll($table, $conditions = [], $limit = 100, $offset = 0) {
        $sql = "SELECT * FROM $table";
        $params = [];
        $types = '';

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "$column = ?";
                $params[] = $value;
                $types .= is_int($value) ? 'i' : 's';
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    // Update operation
    private function update($table, $id, $data, $idColumn = 'id') {
        $setClauses = [];
        $values = [];
        $types = '';

        foreach ($data as $column => $value) {
            $setClauses[] = "$column = ?";
            $values[] = $value;
            $types .= is_int($value) ? 'i' : 's';
        }

        $values[] = $id;
        $types .= is_int($id) ? 'i' : 's';

        $sql = "UPDATE $table SET " . implode(', ', $setClauses) . " WHERE $idColumn = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param($types, ...$values);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $stmt->affected_rows;
    }

    // Delete operation
    private function delete($table, $id, $idColumn = 'id') {
        $sql = "DELETE FROM $table WHERE $idColumn = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $stmt->affected_rows;
    }

    // Prevent cloning
    private function __clone() {}
}