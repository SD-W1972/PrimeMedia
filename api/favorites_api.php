<?php
// Configuração inicial
header('Content-Type: application/json');

// Caminho absoluto para o arquivo de favoritos
$favoritesFile = __DIR__ . '/../data/favorites.json';

// Inicializa o arquivo se não existir
if (!file_exists($favoritesFile)) {
    file_put_contents($favoritesFile, json_encode(['favorites' => []]));
}

// Lê os favoritos existentes
$favorites = json_decode(file_get_contents($favoritesFile), true);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_favorites') {
   

    $favorites = json_decode(file_get_contents($favoritesFile), true);
    echo json_encode($favorites);
    exit;
}


// Processa a requisição
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($favorites);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os dados do POST
    $input = json_decode(file_get_contents('php://input'), true);
    $mediaId = (int)$input['mediaId'];

    // Alterna o status de favorito
    $index = array_search($mediaId, $favorites['favorites']);
    if ($index === false) {
        $favorites['favorites'][] = $mediaId;
    } else {
        array_splice($favorites['favorites'], $index, 1);
    }

    // Salva no arquivo
    file_put_contents($favoritesFile, json_encode($favorites));

    // Retorna a resposta
    echo json_encode([
        'success' => true,
        'isFavorite' => $index === false,
        'favorites' => $favorites['favorites']
    ]);
    exit;
}
?>