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
    
    if ($action === 'register') {
        // Registro simples
        if (!isset($_SESSION['users'][$username])) {
            $_SESSION['users'][$username] = password_hash($password, PASSWORD_DEFAULT);
            $message = "Registro feito! Faça login.";
        } else {
            $error = "Usuário já existe!";
        }
    } elseif ($action === 'login') {
        // Login
        if (isset($_SESSION['users'][$username]) && password_verify($password, $_SESSION['users'][$username])) {
            $_SESSION['logged_user'] = $username;
            header("Location: index.php");
            exit();
        } else {
            $error = "Usuário ou senha incorretos!";
        }
    } elseif ($action === 'logout') {
        unset($_SESSION['logged_user']);
        header("Location: index.php");
        exit();
    }
}

// Inicializa favoritos se não existir
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

// Processa favoritos via AJAX
if (isset($_GET['action']) && $_GET['action'] === 'get_favorites') {
    header('Content-Type: application/json');
    echo json_encode(['favorites' => $_SESSION['favorites']]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mediaId'])) {
    $mediaId = (int)$_POST['mediaId'];
    $index = array_search($mediaId, $_SESSION['favorites']);
    
    if ($index === false) {
        $_SESSION['favorites'][] = $mediaId;
        echo json_encode(['success' => true, 'isFavorite' => true]);
    } else {
        array_splice($_SESSION['favorites'], $index, 1);
        echo json_encode(['success' => true, 'isFavorite' => false]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrimeMedia - Catálogo Magnífico</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Todos os estilos CSS do segundo arquivo aqui */
        :root {
            --netflix-red: #e50914;
            --primary-black: #0a0a0a;
            --rich-black: #141414;
            --deep-charcoal: #2d2d2d;
            --luxury-gold: #d4af37;
            --warm-gold: #f4e6a1;
            --accent-gold: #b8860b;
            --elegant-white: #f8f8f8;
            --subtle-gray: #8a8a8a;
            --shadow-gold: rgba(212, 175, 55, 0.3);
            --gradient-gold: linear-gradient(135deg, #d4af37 0%, #f4e6a1 50%, #b8860b 100%);
            --netflix-gradient: linear-gradient(90deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.8) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary-black);
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(212, 175, 55, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.03) 0%, transparent 50%);
            color: var(--elegant-white);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* NETFLIX-STYLE HEADER */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 80px;
            background: linear-gradient(180deg, rgba(10, 10, 10, 0.9) 0%, rgba(10, 10, 10, 0.6) 100%);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 60px;
            z-index: 1000;
            transition: background 0.3s ease;
        }

        .header.scrolled {
            background: rgba(10, 10, 10, 0.95);
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 4px rgba(212, 175, 55, 0.3);
        }

        .nav-controls {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .nav-btn {
            background: rgba(212, 175, 55, 0.1);
            border: 2px solid var(--luxury-gold);
            border-radius: 8px;
            padding: 12px 24px;
            color: var(--luxury-gold);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        .nav-btn:hover {
            background: var(--gradient-gold);
            color: var(--primary-black);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }

        .user-profile {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            border: 2px solid var(--luxury-gold);
            cursor: pointer;
            transition: all 0.3s ease;
            object-fit: cover;
        }

        .user-profile:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }

        /* HERO BANNER */
        .hero-banner {
            position: relative;
            height: 70vh;
            margin-top: 80px;
            background: linear-gradient(45deg, #1a1a1a 0%, #0a0a0a 100%);
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .hero-content {
            padding: 0 60px;
            max-width: 50%;
            z-index: 2;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.1;
        }

        .hero-subtitle {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            color: var(--warm-gold);
            margin-bottom: 30px;
            font-style: italic;
        }

        .hero-description {
            font-size: 1.2rem;
            color: var(--elegant-white);
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
        }

        .hero-btn {
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero-btn.primary {
            background: var(--gradient-gold);
            color: var(--primary-black);
        }

        .hero-btn.secondary {
            background: rgba(255, 255, 255, 0.2);
            color: var(--elegant-white);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .hero-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        /* CONTENT SECTIONS */
        .content-section {
            padding: 60px 0;
            margin-left: 60px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--warm-gold);
            margin-bottom: 40px;
            position: relative;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100px;
            height: 3px;
            background: var(--gradient-gold);
        }

        /* NETFLIX-STYLE ROWS */
        .media-row {
            margin-bottom: 50px;
            position: relative;
        }

        .media-carousel {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding: 10px 0;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .media-carousel::-webkit-scrollbar {
            display: none;
        }

        .media-card {
            min-width: 280px;
            height: 400px;
            background: var(--rich-black);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border: 1px solid rgba(212, 175, 55, 0.1);
        }

        .media-card:hover {
            transform: scale(1.05) translateY(-10px);
            z-index: 10;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.8);
            border-color: var(--luxury-gold);
        }

        .media-card-image {
            width: 100%;
            height: 70%;
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .media-card:hover .media-card-image {
            transform: scale(1.1);
        }

        .media-card-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.9) 100%);
            padding: 30px 20px 20px;
        }

        .media-card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--warm-gold);
            margin-bottom: 8px;
        }

        .media-card-type {
            font-size: 0.9rem;
            color: var(--subtle-gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .media-card-actions {
            display: flex;
            gap: 10px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }

        .media-card:hover .media-card-actions {
            opacity: 1;
            transform: translateY(0);
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--luxury-gold);
            background: rgba(212, 175, 55, 0.1);
            color: var(--luxury-gold);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .action-btn:hover {
            background: var(--luxury-gold);
            color: var(--primary-black);
        }

        /* MODAL STYLES */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            z-index: 2000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--rich-black);
            border-radius: 15px;
            padding: 40px;
            max-width: 90vw;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid var(--luxury-gold);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.8);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--warm-gold);
        }

        .close-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: var(--luxury-gold);
            color: var(--primary-black);
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .media-viewer {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .media-viewer video,
        .media-viewer img {
            width: 100%;
            border-radius: 10px;
        }

        .media-viewer iframe {
            width: 100%;
            height: 600px;
            border: none;
            border-radius: 10px;
        }

        /* LOGIN MODAL */
        .login-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.95) 0%, rgba(45, 45, 45, 0.9) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 20px;
            padding: 40px;
            width: 450px;
            max-width: 90vw;
            z-index: 2000;
            display: none;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.8);
        }

        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--warm-gold);
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.1rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 10px;
            background: rgba(10, 10, 10, 0.7);
            color: var(--elegant-white);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--luxury-gold);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }

        .form-btn {
            width: 100%;
            padding: 15px;
            background: var(--gradient-gold);
            border: none;
            border-radius: 10px;
            color: var(--primary-black);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }

        /* CUSTOM VIDEO PLAYER STYLES */
        .custom-video-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 30px 0 10px 0;
            background: rgba(20, 20, 20, 0.98);
            border-radius: 18px;
            box-shadow: 0 8px 40px var(--shadow-gold);
            min-width: 350px;
            min-height: 500px;
        }

        .custom-controls {
            display: flex;
            align-items: center;
            gap: 18px;
            width: 100%;
            max-width: 900px;
            margin: 18px auto 0 auto;
            padding: 12px 18px;
            background: rgba(34, 34, 34, 0.95);
            border-radius: 12px;
            box-shadow: 0 2px 12px #0006;
        }

        .gold-btn {
            background: var(--gradient-gold);
            border: none;
            color: var(--primary-black);
            font-size: 1.3rem;
            border-radius: 8px;
            padding: 7px 16px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 2px 8px var(--shadow-gold);
            transition: background 0.2s, transform 0.2s;
        }

        .gold-btn:hover {
            background: var(--luxury-gold);
            color: var(--primary-black);
            transform: scale(1.08);
        }

        #seek-bar {
            flex: 1;
            accent-color: var(--luxury-gold);
            height: 6px;
            border-radius: 4px;
            background: var(--shadow-gold);
            margin: 0 10px;
        }

        #seek-bar::-webkit-slider-thumb {
            background: var(--luxury-gold);
            border: 2px solid var(--primary-black);
            border-radius: 50%;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        #seek-bar::-moz-range-thumb {
            background: var(--luxury-gold);
            border: 2px solid var(--primary-black);
            border-radius: 50%;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        #volume-bar {
            width: 80px;
            accent-color: var(--luxury-gold);
        }

        /* Make modal bigger for video */
        #media-modal .modal-content {
            max-width: 1100px !important;
            min-width: 400px;
            min-height: 600px;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .header {
                padding: 0 20px;
            }
            
            .logo {
                font-size: 2rem;
            }
            
            .hero-content {
                padding: 0 20px;
                max-width: 90%;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .content-section {
                margin-left: 20px;
            }
            
            .media-card {
                min-width: 250px;
                height: 350px;
            }
        }

        /* SCROLLBAR STYLING */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--primary-black);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--luxury-gold);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--warm-gold);
        }

        /* LOGIN CONTAINER STYLES */
        .container_login {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.95) 0%, rgba(45, 45, 45, 0.9) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 20px;
            padding: 40px;
            width: 450px;
            max-width: 90vw;
            z-index: 2000;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.8);
        }

        .container_login h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.3);
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            color: var(--subtle-gray);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .tab.active {
            color: var(--luxury-gold);
            border-bottom: 2px solid var(--luxury-gold);
        }

        .form {
            display: none;
        }

        .form.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--warm-gold);
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.1rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 10px;
            background: rgba(10, 10, 10, 0.7);
            color: var(--elegant-white);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--luxury-gold);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }

        .form-btn {
            width: 100%;
            padding: 15px;
            background: var(--gradient-gold);
            border: none;
            border-radius: 10px;
            color: var(--primary-black);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }

        /* LOGOUT LINK */
        .logout {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(212, 175, 55, 0.1);
            border: 2px solid var(--luxury-gold);
            border-radius: 8px;
            padding: 12px 24px;
            color: var(--luxury-gold);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
            z-index: 2001;
            display: none;
        }

        .logout:hover {
            background: var(--gradient-gold);
            color: var(--primary-black);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }

        /* FAVORITE BUTTON */
        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .favorite-btn.favorited {
            color: #ff0000;
        }

        .favorite-btn:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="header" id="header">
        <div class="logo">PrimeMedia</div>
        <nav class="nav-controls">
            <?php if ($is_logged_in): ?>
                <button id="toggle-favorites-btn" class="nav-btn">Mostrar Favoritos</button>
            <?php endif; ?>
            <button class="nav-btn" id="all-media-btn">Catálogo</button>
            <img class="user-profile" id="pfp_default_image_id" onclick="showLoginDiv()" src="data:image/jpeg;base64,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">
        </nav>
    </header>

    <!-- HERO BANNER -->
    <section class="hero-banner">
        <div class="hero-content">
            <h1 class="hero-title">Experiência Magnífica</h1>
            <p class="hero-subtitle">O catálogo mais sublime do entretenimento</p>
            <p class="hero-description">
                Descubra uma coleção exclusiva de conteúdo premium, cuidadosamente selecionado para 
                os paladares mais refinados. Uma experiência cinematográfica única e sofisticada.
            </p>
            <div class="hero-buttons">
                <button class="hero-btn primary" onclick="scrollToContent()">Explorar Catálogo</button>
                <button class="hero-btn secondary" onclick="showLoginDiv()">Minha Conta</button>
            </div>
        </div>
    </section>

    <!-- LOGIN CONTAINER -->
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
            <div class="form-group">
                <label>Usuário:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Senha:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="form-btn">Entrar</button>
        </form>

        <form id="registerForm" class="form" method="POST">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label>Novo Usuário:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Nova Senha:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="form-btn">Registrar</button>
        </form>
    </div>

    <?php if ($is_logged_in): ?>
        <div id="logout_link" class="logout" onclick="logoutUser()">
            Sair (Logout)
        </div>
    <?php endif; ?>

    <!-- CONTENT SECTIONS -->
    <main id="main-content">
        <!-- Seção Todos os Conteúdos -->
        <section class="content-section" id="all-content">
            <h2 class="section-title">Catálogo Completo</h2>
            <div class="media-row">
                <div class="media-carousel" id="all-media-carousel"></div>
            </div>
        </section>

        <!-- Seção Favoritos -->
        <section class="content-section" id="favorites-content" style="display: none;">
            <h2 class="section-title">Seus Favoritos</h2>
            <div class="media-row">
                <div class="media-carousel" id="favorites-carousel"></div>
            </div>
        </section>

        <!-- Seções por Categoria -->
        <section class="content-section">
            <h2 class="section-title">Cinema de Prestígio</h2>
            <div class="media-row">
                <div class="media-carousel" id="video-carousel"></div>
            </div>
        </section>

        <section class="content-section">
            <h2 class="section-title">Galeria de Arte</h2>
            <div class="media-row">
                <div class="media-carousel" id="image-carousel"></div>
            </div>
        </section>

        <section class="content-section">
            <h2 class="section-title">Biblioteca Clássica</h2>
            <div class="media-row">
                <div class="media-carousel" id="pdf-carousel"></div>
            </div>
        </section>

        <section class="content-section">
            <h2 class="section-title">Sinfonias Exclusivas</h2>
            <div class="media-row">
                <div class="media-carousel" id="audio-carousel"></div>
            </div>
        </section>
    </main>

    <!-- MEDIA VIEWER MODAL -->
    <div class="modal" id="media-modal">
        <div class="modal-content" id="media-modal-content">
            <div class="modal-header">
                <span class="modal-title" id="modal-title"></span>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="media-viewer" id="media-viewer"></div>
        </div>
    </div>

    <script>
        // --- MEDIA DATA ---
        let baseMediaList = [
            {
                id: 1,
                type: 'pdf',
                title: 'Livro Clássico',
                src: 'https://ia902902.us.archive.org/19/items/diaryofawimpykidbookseriesbyjeffkinney_202004/Diary%20of%20a%20wimpy%20kid%20book01.pdf',
                thumb: 'https://img.icons8.com/ios-filled/200/ffd700/pdf.png',
                description: 'Um livro em PDF com navegação animada.'
            },
            {
                id: 2,
                type: 'image',
                title: 'Obra de Arte',
                src: 'assets/image.jpg',
                thumb: 'assets/image.jpg',
                description: 'Imagem com zoom e navegação.'
            },
            {
                id: 3,
                type: 'audio',
                title: 'Trilha Sonora',
                src: 'assets/sound.mp3',
                thumb: 'https://img.icons8.com/ios-filled/200/ffd700/musical-notes.png',
                description: 'Áudio com player customizado.'
            },
            {
                id: 4,
                type: 'video',
                title: 'Filme Exclusivo',
                src: 'assets/video.mp4',
                thumb: 'https://img.icons8.com/ios-filled/200/ffd700/video.png',
                description: 'Vídeo com player próprio.'
            }
        ];
        // Repeat each item 6 times to fill the catalog
        const mediaList = Array(6).fill(baseMediaList).flat().map((item, i) => ({
            ...item, 
            id: item.id + (i * 4),
            title: item.title + ' #' + (Math.floor(i/4) + 1)
        }));

        // --- RENDER MEDIA CAROUSELS ---
        function renderMediaCarousels() {
            const all = document.getElementById('all-media-carousel');
            const video = document.getElementById('video-carousel');
            const image = document.getElementById('image-carousel');
            const pdf = document.getElementById('pdf-carousel');
            const audio = document.getElementById('audio-carousel');
            all.innerHTML = video.innerHTML = image.innerHTML = pdf.innerHTML = audio.innerHTML = '';
            
            mediaList.forEach((item, idx) => {
                const card = `<div class='media-card' onclick='openMedia(${idx})'>
                    <img class='media-card-image' src='${item.thumb}' alt='${item.title}'>
                    <div class='media-card-info'>
                        <div class='media-card-title'>${item.title}</div>
                        <div class='media-card-type'>${item.type.toUpperCase()}</div>
                        <div style='font-size:0.95em;color:#ccc;'>${item.description}</div>
                    </div>
                    <button class="favorite-btn ${isFavorite(item.id) ? 'favorited' : ''}" 
                            onclick="toggleFavorite(event, ${item.id})">
                        ${isFavorite(item.id) ? '❤️' : '♡'}
                    </button>
                </div>`;
                
                all.innerHTML += card;
                if (item.type === 'video') video.innerHTML += card;
                if (item.type === 'image') image.innerHTML += card;
                if (item.type === 'pdf') pdf.innerHTML += card;
                if (item.type === 'audio') audio.innerHTML += card;
            });
        }

        // --- FAVORITES LOGIC ---
        async function toggleFavorite(event, mediaId) {
            event.stopPropagation();
            
            if (!isLoggedIn) {
                alert('Você precisa estar logado para adicionar aos favoritos.');
                showLoginDiv();
                return;
            }
            
            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `mediaId=${mediaId}`
                });
                
                const result = await response.json();
                if (result.success) {
                    const btn = event.target;
                    if (result.isFavorite) {
                        btn.classList.add('favorited');
                        btn.innerHTML = '❤️';
                    } else {
                        btn.classList.remove('favorited');
                        btn.innerHTML = '♡';
                    }
                    updateFavoritesView();
                }
            } catch (error) {
                console.error('Erro ao favoritar:', error);
            }
        }

        async function loadFavorites() {
            try {
                const response = await fetch('index.php?action=get_favorites');
                const data = await response.json();
                return data.favorites || [];
            } catch (error) {
                console.error('Erro ao carregar favoritos:', error);
                return [];
            }
        }

        function isFavorite(mediaId) {
            return favorites.includes(mediaId);
        }

        async function updateFavoritesView() {
            favorites = await loadFavorites();
            const favSection = document.getElementById('favorites-content');
            const favCarousel = document.getElementById('favorites-carousel');
            
            if (favorites.length === 0) {
                favCarousel.innerHTML = '<p>Nenhum favorito selecionado</p>';
                return;
            }
            
            favCarousel.innerHTML = '';
            mediaList.filter(item => favorites.includes(item.id)).forEach(item => {
                favCarousel.innerHTML += `<div class='media-card' onclick='openMedia(${mediaList.indexOf(item)})'>
                    <img class='media-card-image' src='${item.thumb}' alt='${item.title}'>
                    <div class='media-card-info'>
                        <div class='media-card-title'>${item.title}</div>
                        <div class='media-card-type'>${item.type.toUpperCase()}</div>
                    </div>
                    <button class="favorite-btn favorited" onclick="toggleFavorite(event, ${item.id})">❤️</button>
                </div>`;
            });
        }

        // --- TOGGLE FAVORITES VIEW ---
        async function toggleFavoritesView() {
            const allSection = document.getElementById('all-content');
            const favSection = document.getElementById('favorites-content');
            const toggleBtn = document.getElementById('toggle-favorites-btn');
            
            if (favSection.style.display === 'none') {
                allSection.style.display = 'none';
                favSection.style.display = 'block';
                toggleBtn.textContent = 'Mostrar Todos';
                await updateFavoritesView();
            } else {
                allSection.style.display = 'block';
                favSection.style.display = 'none';
                toggleBtn.textContent = 'Mostrar Favoritos';
            }
        }

        // --- MODAL LOGIC --- ALGUEM ME ENSINA A ABRIR PDF
        function openMedia(idx) {
            const media = mediaList[idx];
            document.getElementById('modal-title').textContent = media.title;
            let html = '';
            
            if (media.type === 'pdf') {
              html = `
                <h3>${item.title}</h3>
                <a href="${item.url}" target="_blank">Abrir PDF</a>
                <button onclick="toggleFavorite(${item.id})">
                    ${isFavorite ? '❤️ Remover dos Favoritos' : '♡ Adicionar aos Favoritos'}
                </button>
            `;
            } else if (media.type === 'image') {
                html = `<div style='overflow:hidden;text-align:center;'>
                    <img id='zoom-img' src='${media.src}' style='max-width:100%;max-height:70vh;cursor:zoom-in;border-radius:10px;transition:transform 0.3s;'>
                </div>`;
            } else if (media.type === 'audio') {
                html = `<div class="spotify-player">
                    <div class="album-art-container">
                        <div class="album-art">
                            <img src="${media.thumb}" alt="${media.title}" id="album-image">
                            <div class="play-overlay" id="play-overlay">
                                <div class="play-button-large" id="play-button-large">▶</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="track-info">
                        <h2 class="track-title">${media.title}</h2>
                        <p class="track-artist">PrimeMedia Collection</p>
                    </div>
                    
                    <div class="player-controls">
                        <div class="control-buttons">
                            <button class="control-btn" id="shuffle-btn">🔀</button>
                            <button class="control-btn" id="prev-btn">⏮</button>
                            <button class="control-btn main-play-btn" id="main-play-btn">▶</button>
                            <button class="control-btn" id="next-btn">⏭</button>
                            <button class="control-btn" id="repeat-btn">🔁</button>
                        </div>
                        
                        <div class="progress-container">
                            <span class="time-display" id="current-time-display">0:00</span>
                            <div class="progress-bar-container">
                                <div class="progress-bar" id="progress-bar">
                                    <div class="progress-fill" id="progress-fill"></div>
                                    <div class="progress-handle" id="progress-handle"></div>
                                </div>
                            </div>
                            <span class="time-display" id="duration-display">0:00</span>
                        </div>
                    </div>
                    
                    <div class="volume-controls">
                        <button class="control-btn" id="volume-btn">🔊</button>
                        <div class="volume-bar-container">
                            <div class="volume-bar" id="volume-bar">
                                <div class="volume-fill" id="volume-fill"></div>
                                <div class="volume-handle" id="volume-handle"></div>
                            </div>
                        </div>
                    </div>
                    
                    <audio id="spotify-audio" src="${media.src}" preload="metadata"></audio>
                </div>`;
            } else if (media.type === 'video') {
                html = `<div class="custom-video-wrapper">
                    <video id="custom-video" src="${media.src}" style="width:100%;max-width:1000px;max-height:70vh;border-radius:16px;background:#000;"></video>
                    <div class="custom-controls">
                        <button id="play-pause" class="gold-btn">&#9654;</button>
                        <input type="range" id="seek-bar" value="0" min="0" max="100" step="0.1">
                        <span id="current-time">0:00</span> / <span id="duration">0:00</span>
                        <button id="mute-btn" class="gold-btn">🔊</button>
                        <input type="range" id="volume-bar" min="0" max="1" step="0.01" value="1">
                        <button id="fullscreen-btn" class="gold-btn">⛶</button>
                    </div>
                </div>`;
            }
            
            document.getElementById('media-viewer').innerHTML = html;
            document.getElementById('media-modal').style.display = 'block';
            
            if (media.type === 'pdf') loadPDF(media.src);
            if (media.type === 'image') setupZoom();
            if (media.type === 'audio') setupSpotifyPlayer();
            if (media.type === 'video') setupCustomVideoPlayer();
        }

        function closeModal() {
            document.getElementById('media-modal').style.display = 'none';
            document.getElementById('media-viewer').innerHTML = '';
        }

        window.onclick = function(e) {
            if (e.target === document.getElementById('media-modal')) closeModal();
        }

        // --- PDF VIEWER ---
        let pdfDoc = null, pageNum = 1, pageRendering = false, pageNumPending = null, pdfScale = 1.5;
        
        function loadPDF(url) {
            if (!window.pdfjsLib) {
                if (!document.getElementById('pdfjs-script')) {
                    const script = document.createElement('script');
                    script.id = 'pdfjs-script';
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.2.67/pdf.min.js';
                    script.onload = () => {
                        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.2.67/pdf.worker.min.js';
                        loadPDF(url);
                    };
                    document.body.appendChild(script);
                }
                return;
            }
            
            const canvas = document.getElementById('pdf-canvas');
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            document.getElementById('pdf-page-info').textContent = '';
            
            window.pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
                pdfDoc = pdfDoc_;
                pageNum = 1;
                renderPage(pageNum);
            });
        }

        function renderPage(num) {
            pageRendering = true;
            pdfDoc.getPage(num).then(function(page) {
                const canvas = document.getElementById('pdf-canvas');
                const ctx = canvas.getContext('2d');
                const viewport = page.getViewport({ scale: pdfScale });
                
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                
                canvas.style.transition = 'none';
                canvas.style.transform = 'translateX(100vw)';
                canvas.style.opacity = 0.2;
                
                setTimeout(() => {
                    canvas.style.transition = 'transform 0.5s cubic-bezier(.4,2,.6,1), opacity 0.5s';
                    canvas.style.transform = 'translateX(0)';
                    
                    page.render({canvasContext: ctx, viewport: viewport}).promise.then(function() {
                        document.getElementById('pdf-page-info').textContent = `Página ${pageNum} de ${pdfDoc.numPages}`;
                        canvas.style.opacity = 1;
                        pageRendering = false;
                        
                        if (pageNumPending !== null) {
                            renderPage(pageNumPending);
                            pageNumPending = null;
                        }
                    });
                }, 50);
            });
        }

        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        function prevPage() {
            if (pageNum <= 1) return;
            pageNum--;
            queueRenderPage(pageNum);
        }

        function nextPage() {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            queueRenderPage(pageNum);
        }

        function zoomInPDF() {
            pdfScale = Math.min(pdfScale + 0.2, 3);
            queueRenderPage(pageNum);
        }

        function zoomOutPDF() {
            pdfScale = Math.max(pdfScale - 0.2, 0.5);
            queueRenderPage(pageNum);
        }

        // --- IMAGE ZOOM --- ÚNICA merda que funciona nesse site e é só um png do shrek
        function setupZoom() {
            const img = document.getElementById('zoom-img');
            let zoomed = false;
            
            img.onclick = function() {
                zoomed = !zoomed;
                img.style.transform = zoomed ? 'scale(2.2)' : 'scale(1)';
                img.style.cursor = zoomed ? 'zoom-out' : 'zoom-in';
            };
            
            img.onmousemove = function(e) {
                if (!zoomed) return;
                const rect = img.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width * 100;
                const y = (e.clientY - rect.top) / rect.height * 100;
                img.style.transformOrigin = `${x}% ${y}%`;
            };
            
            img.onmouseleave = function() {
                if (zoomed) img.style.transform = 'scale(1)';
                zoomed = false;
                img.style.cursor = 'zoom-in';
            };
        }

        // --- SPOTIFY PLAYER --- se fosse uma bosta
        function setupSpotifyPlayer() {
            const audio = document.getElementById('spotify-audio');
            if (!audio) return;

            const playBtnLarge = document.getElementById('play-button-large');
            const mainPlayBtn = document.getElementById('main-play-btn');
            const progressFill = document.getElementById('progress-fill');
            const progressHandle = document.getElementById('progress-handle');
            const progressBar = document.getElementById('progress-bar');
            const currentTimeDisplay = document.getElementById('current-time-display');
            const durationDisplay = document.getElementById('duration-display');
            const volumeBtn = document.getElementById('volume-btn');
            const volumeFill = document.getElementById('volume-fill');
            const volumeHandle = document.getElementById('volume-handle');
            const volumeBar = document.getElementById('volume-bar');
            const player = document.querySelector('.spotify-player');

            let isPlaying = false;
            let isDragging = false;

            function formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return `${mins}:${secs.toString().padStart(2, '0')}`;
            }

            function togglePlay() {
                if (isPlaying) {
                    audio.pause();
                    isPlaying = false;
                    playBtnLarge.textContent = '▶';
                    mainPlayBtn.textContent = '▶';
                    player.classList.remove('playing');
                    player.classList.add('paused');
                } else {
                    audio.play();
                    isPlaying = true;
                    playBtnLarge.textContent = '⏸';
                    mainPlayBtn.textContent = '⏸';
                    player.classList.remove('paused');
                    player.classList.add('playing');
                }
            }

            playBtnLarge.addEventListener('click', togglePlay);
            mainPlayBtn.addEventListener('click', togglePlay);

            audio.addEventListener('loadedmetadata', () => {
                durationDisplay.textContent = formatTime(audio.duration);
            });

            audio.addEventListener('timeupdate', () => {
                if (!isDragging) {
                    const progress = (audio.currentTime / audio.duration) * 100;
                    progressFill.style.width = progress + '%';
                    progressHandle.style.left = progress + '%';
                    currentTimeDisplay.textContent = formatTime(audio.currentTime);
                }
            });

            progressBar.addEventListener('click', (e) => {
                const rect = progressBar.getBoundingClientRect();
                const clickX = e.clientX - rect.left;
                const progress = clickX / rect.width;
                audio.currentTime = progress * audio.duration;
            });

            volumeBtn.addEventListener('click', () => {
                audio.muted = !audio.muted;
                volumeBtn.textContent = audio.muted ? '🔇' : '🔊';
                volumeFill.style.width = audio.muted ? '0%' : (audio.volume * 100) + '%';
            });

            volumeBar.addEventListener('click', (e) => {
                const rect = volumeBar.getBoundingClientRect();
                const clickX = e.clientX - rect.left;
                const volume = clickX / rect.width;
                audio.volume = volume;
                volumeFill.style.width = (volume * 100) + '%';
                volumeHandle.style.right = ((1 - volume) * 100) + '%';
                audio.muted = volume === 0;
                volumeBtn.textContent = audio.muted ? '🔇' : '🔊';
            });

            document.getElementById('shuffle-btn').addEventListener('click', function() {
                this.style.color = this.style.color === 'rgb(212, 175, 55)' ? '' : 'rgb(212, 175, 55)';
            });

            document.getElementById('repeat-btn').addEventListener('click', function() {
                this.style.color = this.style.color === 'rgb(212, 175, 55)' ? '' : 'rgb(212, 175, 55)';
            });
        }

        // --- CUSTOM VIDEO PLAYER ---
        function setupCustomVideoPlayer() {
            const video = document.getElementById('custom-video');
            if (!video) return;
            
            const playPause = document.getElementById('play-pause');
            const seekBar = document.getElementById('seek-bar');
            const currentTime = document.getElementById('current-time');
            const duration = document.getElementById('duration');
            const muteBtn = document.getElementById('mute-btn');
            const volumeBar = document.getElementById('volume-bar');
            const fullscreenBtn = document.getElementById('fullscreen-btn');

            video.addEventListener('loadedmetadata', () => {
                seekBar.max = video.duration;
                duration.textContent = formatTime(video.duration);
            });

            video.addEventListener('timeupdate', () => {
                seekBar.value = video.currentTime;
                currentTime.textContent = formatTime(video.currentTime);
            });

            seekBar.addEventListener('input', () => {
                video.currentTime = seekBar.value;
            });

            playPause.addEventListener('click', () => {
                if (video.paused) {
                    video.play();
                    playPause.innerHTML = '&#10073;&#10073;';
                } else {
                    video.pause();
                    playPause.innerHTML = '&#9654;';
                }
            });

            muteBtn.addEventListener('click', () => {
                video.muted = !video.muted;
                muteBtn.textContent = video.muted ? '🔇' : '🔊';
            });

            volumeBar.addEventListener('input', () => {
                video.volume = volumeBar.value;
                video.muted = video.volume === 0;
                muteBtn.textContent = video.muted ? '🔇' : '🔊';
            });

            fullscreenBtn.addEventListener('click', () => {
                if (video.requestFullscreen) video.requestFullscreen();
                else if (video.webkitRequestFullscreen) video.webkitRequestFullscreen();
                else if (video.msRequestFullscreen) video.msRequestFullscreen();
            });

            function formatTime(sec) {
                const m = Math.floor(sec / 60);
                const s = Math.floor(sec % 60).toString().padStart(2, '0');
                return `${m}:${s}`;
            }
        }

        // --- NAVIGATION ---
        function scrollToContent() {
            document.getElementById('all-content').scrollIntoView({behavior:'smooth'});
        }

        // --- LOGIN SYSTEM ---
        const isLoggedIn = <?= $is_logged_in ? 'true' : 'false' ?>;
        let favorites = [];
        
        function showForm(formId) {
            document.querySelectorAll('.form').forEach(f => f.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            
            document.getElementById(formId + 'Form').classList.add('active');
            event.currentTarget.classList.add('active');
        }
        
        function showLoginDiv() {
            const container = document.getElementById('container_login_id');
            const logoutDiv = document.getElementById('logout_link');
            
            if (!isLoggedIn) {
                container.style.display = (container.style.display === 'block') ? 'none' : 'block';
                if (logoutDiv) logoutDiv.style.display = 'none';
            } else {
                if (logoutDiv) logoutDiv.style.display = (logoutDiv.style.display === 'block') ? 'none' : 'block';
                container.style.display = 'none';
            }
        }

        function logoutUser() {
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=logout'
            }).then(() => window.location.reload());
        }

        // --- INITIALIZE ---
        document.getElementById('all-media-btn').addEventListener('click', scrollToContent);
        
        if (document.getElementById('toggle-favorites-btn')) {
            document.getElementById('toggle-favorites-btn').addEventListener('click', toggleFavoritesView);
        }

        // Load favorites on page load
        (async function init() {
            if (isLoggedIn) {
                favorites = await loadFavorites();
                renderMediaCarousels();
            }
        })();
    </script>
</body>
</html>