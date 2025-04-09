<?php
header('Content-Type: application/json');

include_once __DIR__ . '/conn.php';
require_once __DIR__ . '/crud.php';

$BASE_URL = '/cadastro_login/src/';

try {
    $crud = Crud::getInstance($conn);
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;
        
        if (!$email || !$password) {
            throw new Exception("Email e senha são obrigatórios");
        }
        
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $result = $crud->login($email, $password);
        
        if ($result['status']) {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => true,
                'use_strict_mode' => true
            ]);
            
            session_regenerate_id(true);
            $_SESSION['user'] = $result['user'];
            
            // Redirecionamento para profile.php
            echo json_encode([
                'status' => 'success',
                'message' => $result['message'],
                'redirect' => $BASE_URL . 'dashboard/profile.php'  // Alterado para profile.php
            ]);
            
        } else {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => $result['message']
            ]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro: ' . $e->getMessage()
    ]);
    error_log('Login Error: ' . $e->getMessage());
}
?>