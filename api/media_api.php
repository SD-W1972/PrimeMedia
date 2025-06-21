<?php
session_start();
require_once 'auth.php';

$mediaFile = 'data/media.json';
$mediaData = json_decode(file_get_contents($mediaFile), true);

// Endpoint: Listar mídias
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = ['media' => $mediaData['media']];
    
    // Se logado, adiciona info de favoritos
    if (is_logged_in()) {
        $favorites = json_decode(file_get_contents('data/favorites.json'), true);
        $userFavorites = $favorites[$_SESSION['logged_user']] ?? [];
        $response['favorites'] = $userFavorites;
    }
    
    echo json_encode($response);
    exit;
}
?>