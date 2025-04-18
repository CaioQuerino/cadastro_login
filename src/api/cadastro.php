<?php 
header('Content-Type: application/json');

include_once __DIR__ . '/conn.php';

$BASE_URL = '/cadastro_login/src/';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    if (!$conn) {
        throw new Exception('Database connection failed', 500);
    }

    // Validate required fields
    $requiredFields = ['first-name', 'last-name', 'email', 'password'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        throw new Exception('Missing required fields: ' . implode(', ', $missingFields), 400);
    }

    // Sanitize inputs
    $first_name = mysqli_real_escape_string($conn, trim($_POST['first-name']));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['last-name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format', 400);
    }

    // Check password strength
    if (strlen($_POST['password']) < 10) {
        throw new Exception('Password must be at least 10 characters', 400);
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception('Email already registered', 409);
    }

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $first_name, $last_name, $email, $password);
    
    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error, 500);
    }

    // Success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Registration successful!',
        'redirect' => $BASE_URL . 'pages/success-201.html'
    ]);


} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);


} finally {
    if (isset($conn)) {
        $conn->close();
    }
}