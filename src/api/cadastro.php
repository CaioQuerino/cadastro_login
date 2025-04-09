<?php 
header('Content-Type: application/json');

// Correct include path (adjust according to your file structure)
include_once __DIR__ . '/conn.php';

$BASE_URL = '/cadastro_login/src/'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify connection exists
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        header(`Location: {$BASE_URL}pages/erro-500.html`);         
        exit;
    }

    // Get and sanitize input
    $first_name = isset($_POST['first-name']) ? mysqli_real_escape_string($conn, $_POST['first-name']) : null;
    $last_name = isset($_POST['last-name']) ? mysqli_real_escape_string($conn, $_POST['last-name']) : null;
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : null;
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Validate required fields
    if (!$first_name || !$last_name || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit;
    }

    // Check if email already exists (using prepared statement)
    $check_email = "SELECT email FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_email);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Email already registered.']);
        header(`Location: {$BASE_URL}pages/erro-409.html`);         
        exit;
    }

    // Insert into database using prepared statement
    $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $first_name, $last_name, $email, $password);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Registration successful!']);
        header(`Location: {$BASE_URL}pages/success-201.html`);         
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
        header(`Location: {$BASE_URL}pages/erro-500.html`);         
    }

    mysqli_stmt_close($stmt);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    header(`Location: {$BASE_URL}pages/erro-405.html`);
}

$database->closeConnection();
?>