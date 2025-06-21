<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['logged_user']); // Alterado para consistência
}

function login($username, $password) {
    $users = [
        'admin' => password_hash('admin123', PASSWORD_DEFAULT),
    ];
    
    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        $_SESSION['logged_user'] = $username; // Usar apenas esta variável
        return true;
    }
    return false;
}

function logout() {
    unset($_SESSION['logged_user']); // Remover apenas esta
    session_destroy(); // Adicionado para limpeza completa
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    logout();
    echo json_encode(['success' => true]);
    exit;
}
?>