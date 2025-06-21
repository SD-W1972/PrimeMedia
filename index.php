<?php
session_start();
$is_logged_in = isset($_SESSION['logged_user']);
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [
        'admin' => password_hash('admin123', PASSWORD_DEFAULT) // Usuário padrão
    ];
}

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $action = $_POST['action'] ?? '';
    $deuerro = 0;
    if ($action === 'register') {
        // Registro simples
        if (!isset($_SESSION['users'][$username])) {
            $_SESSION['users'][$username] = password_hash($password, PASSWORD_DEFAULT);
            $message = "Registro feito! Faça login.";
        } else {
            $error = "Usuário já existe!";
        }
    } else {
        // Login
        if (isset($_SESSION['users'][$username]) && password_verify($password, $_SESSION['users'][$username])) {
            $_SESSION['logged_user'] = $username;
            header("Location: index.php");
            exit();
        } else {
         echo "<script>alert('Usuário ou senha incorretos!');</script>";
    
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PrimeMedia</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>
<body>

<img class="pfp_default_image" id="pfp_default_image_id" onclick="showLoginDiv()"src="data:image/jpeg;base64,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">
   



<div class="container_login" id="container_login_id">
        <h2>Media Player</h2>
        
        <?php if (isset($error)): ?>
            <div style="color: red;"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (isset($message)): ?>
            <div style="color: green;"><?= $message ?></div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" onclick="showForm('login')">Login</div>
            <div class="tab" onclick="showForm('register')">Registro</div>
        </div>

        <form id="loginForm" class="form active" method="POST">
            <input type="hidden" name="action" value="login">
            <div>
                <label>Usuário:</label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>Senha:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Entrar</button>
        </form>

        <form id="registerForm" class="form" method="POST">
            <input type="hidden" name="action" value="register">
            <div>
                <label>Novo Usuário:</label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>Nova Senha:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Registrar</button>
        </form>
    </div>

<?php if ($is_logged_in): ?>
<div id="favorites-controls" style="margin: 20px 0; text-align: center;">
    <button id="toggle-favorites-btn" style="padding: 10px 20px; cursor: pointer;">
        Mostrar Favoritos
    </button>
</div>
<?php endif; ?>

<div id="media-container" class="media-grid"></div>


    <?php if ($is_logged_in): ?>
    <div id="logout_link" class="logout" onclick="logoutUser()">
        Sair (Logout)
    </div>
    <?php endif; ?>
<br>

    <script>
        //login. Eu odeio o copilot morra copilot eu odeio o seu código copilot morra eu tive q fazer sozinho essa bosta
        //nao precisávamos do login no fim das contas mas é um diferencial legal
        const isLoggedIn = <?= $is_logged_in ? 'true' : 'false' ?>;
        
        function showForm(formId) {
            document.querySelectorAll('.form').forEach(f => f.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            
            document.getElementById(formId + 'Form').classList.add('active');
            event.currentTarget.classList.add('active');
        }
    
        const pfp = document.getElementById('pfp_default_image_id');
      
        function showLoginDiv() {
            const container = document.getElementById('container_login_id');
            const logoutDiv = document.getElementById('logout_link');
            if (!isLoggedIn) {
                if (container) container.style.display = (container.style.display === 'block') ? 'none' : 'block';
                if (logoutDiv) logoutDiv.style.display = 'none';
            } else {
                if (logoutDiv) logoutDiv.style.display = (logoutDiv.style.display === 'block') ? 'none' : 'block';
                if (container) container.style.display = 'none'; // Always hide login/register when logged in
            }
        }

        function logoutUser() {
            fetch('api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=logout'
            })
            .then(() => window.location.reload());
        }


        let favorites = [];


        function toggleFavorite(mediaId) {
            const index = favorites.indexOf(mediaId);
            
            if (index === -1) {
                favorites.push(mediaId);
            } else {
                favorites.splice(index, 1);
            }
            if(isLoggedIn){
            document.querySelectorAll('.media-item').forEach(item => {
                if (parseInt(item.dataset.id) === mediaId) {
                    item.classList.toggle('favorite');
                    const button = item.querySelector('button');
                    button.textContent = favorites.includes(mediaId) ? 
                        '❤️ Remover dos Favoritos' : '♡ Adicionar aos Favoritos';
                }
            });
            }else{
                alert('Você precisa estar logado para adicionar aos favoritos.');
                
            }
            updateFavoritesDisplay();
        }

        function updateFavoritesDisplay() {
            const container = document.getElementById('favorites-container');
            container.innerHTML = '<h2>Seus Favoritos</h2>';
            
            if (favorites.length === 0) {
                container.innerHTML += '<p>Nenhum favorito selecionado</p>';
                return;
            }
            
        }

        window.onload = loadMedia;


        //slop de ia pra adicionar aos favoritos pq eu tava com preguiça de fazer o backend

        // Função para favoritar/desfavoritar
async function toggleFavorite(mediaId) {
    try {
        const response = await fetch('api/favorites_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ mediaId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Atualiza a UI
            const mediaElement = document.querySelector(`.media-item[data-id="${mediaId}"]`);
            if (mediaElement) {
                mediaElement.classList.toggle('favorite', result.isFavorite);
                const button = mediaElement.querySelector('button');
                button.textContent = result.isFavorite 
                    ? '❤️ Remover dos Favoritos' 
                    : '♡ Adicionar aos Favoritos';
            }
        }
    } catch (error) {
        console.error('Erro ao favoritar:', error);
        alert('voce precisa estar logado para adicionar aos favoritos.');
    }
}

// Carrega favoritos ao iniciar
async function loadFavorites() {
    try {
        const response = await fetch('api/favorites_api.php');
        const data = await response.json();
        return data.favorites;
    } catch (error) {
        console.error('Erro ao carregar favoritos:', error);
        return [];
    }
}


async function loadMedia() {
    try {
        const [mediaResponse, favorites] = await Promise.all([
            fetch('data/media.json'),
            loadFavorites()
        ]);
        
        const mediaData = await mediaResponse.json();
        displayMedia(mediaData.media, favorites);
    } catch (error) {
        console.error('Erro ao carregar mídia:', error);
    }
}

function displayMedia(mediaItems, favorites) {
    const container = document.getElementById('media-container');
    container.innerHTML = '';
    
    mediaItems.forEach(item => {
        const isFavorite = favorites.includes(item.id);
        const mediaElement = document.createElement('div');
        mediaElement.className = `media-item ${isFavorite ? 'favorite' : ''}`;
        mediaElement.dataset.id = item.id;
        
        if(item.type === 'image') {
            mediaElement.innerHTML = `
                <h3>${item.title}</h3>
              <img src="${item.cover}" alt="${item.title}" style="max-width: 200px;">
                <button onclick="toggleFavorite(${item.id})">
                    ${isFavorite ? '❤️ Remover dos Favoritos' : '♡ Adicionar aos Favoritos'}
                </button>
            `;
        } 
        else if(item.type === 'pdf') {
            mediaElement.innerHTML = `
                <h3>${item.title}</h3>
                <a href="${item.url}" target="_blank">Abrir PDF</a>
                <button onclick="toggleFavorite(${item.id})">
                    ${isFavorite ? '❤️ Remover dos Favoritos' : '♡ Adicionar aos Favoritos'}
                </button>
            `;
        }
        
        container.appendChild(mediaElement);
    });
}

//CARREGA a merda

let showingFavorites = false;
const toggleBtn = document.getElementById('toggle-favorites-btn');
const mediaContainer = document.getElementById('media-container');

async function toggleFavoritesView() {
    showingFavorites = !showingFavorites;
    
    if (showingFavorites) {
        toggleBtn.textContent = "Mostrar Todos";
        await showOnlyFavorites();
    } else {
        toggleBtn.textContent = "Mostrar Favoritos";
        await loadMedia(); // Sua função original que carrega tudo
    }
}

async function showOnlyFavorites() {
    try {
        // Carrega ambos os dados simultaneamente
        const [mediaResponse, favoritesResponse] = await Promise.all([
            fetch('data/media.json'),
            fetch('api/favorites_api.php?action=get_favorites')
        ]);
        
        const mediaData = await mediaResponse.json();
        const favoritesData = await favoritesResponse.json();
        
        // Filtra apenas os favoritos
        const favoriteItems = mediaData.media.filter(item => 
            favoritesData.favorites.includes(item.id)
        );
        
        // Exibe
        displayFilteredMedia(favoriteItems);
    } catch (error) {
        console.error("Erro:", error);
    }
}

function displayFilteredMedia(items) {
    mediaContainer.innerHTML = '';
    
    items.forEach(item => {
        const mediaElement = document.createElement('div');
        mediaElement.className = 'media-item';
        mediaElement.dataset.id = item.id;
        mediaElement.innerHTML = `
            <h3>${item.title}</h3>
            <img src="${item.cover}" alt="${item.title}" style="max-width: 200px;">
            <button onclick="toggleFavorite(${item.id})">
                ❤️ Remover dos Favoritos
            </button>
        `;
        mediaContainer.appendChild(mediaElement);
    });
}

// Adicione o event listener
if (toggleBtn) {
    toggleBtn.addEventListener('click', toggleFavoritesView);
}
    </script>
</body>
</html>