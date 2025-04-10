<?php
session_start();

$BASE_URL = '/cadastro_login/src/pages/';

// Verifica se o usuário está logado
if (!isset($_SESSION['user'])) {
    header('Location: ' . $BASE_URL . 'login.html');
    exit();
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Usuário</title>
    <link rel="stylesheet" href="/cadastro_login/src/css/style.css">
</head>
<body>
    <div class="profile-container">
        <h1>Bem-vindo, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
        
        <div class="profile-info">
            <p><strong>Nome completo:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <!-- Adicione mais informações do usuário conforme necessário -->
        </div>
        
        <a href="logout.php" class="logout-btn">Sair</a>
    </div>
</body>
</html>