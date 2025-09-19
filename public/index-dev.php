<?php
// Versão de desenvolvimento do index.php
require "Includes/conectdb-dev.php"; // Usa conexão de desenvolvimento

// Simular userDetails para desenvolvimento
if (!isset($userDetails)) {
    $userDetails = new stdClass();
    $userDetails->conta = false; // Para redirecionar para login
}

if (! $userDetails->conta &&
    ! isset($_GET["ses"]) &&
    ! isset($_GET["erro"]) &&
    ! isset($_GET["msg"]) &&
    ! isset($_GET["msg2"])
) {
    header("location: ./login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎮 SugoiGame - Página Principal</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #FF6B6B 0%, #4ECDC4 100%);
            color: white;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        .success {
            background: rgba(76, 175, 80, 0.3);
            border: 2px solid #4CAF50;
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            text-align: center;
        }
        h1 { text-align: center; font-size: 3em; margin-bottom: 30px; }
        .emoji { font-size: 1.5em; }
        .nav {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }
        .nav-item {
            background: rgba(255,255,255,0.2);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            transition: transform 0.3s ease;
            text-decoration: none;
            color: white;
        }
        .nav-item:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><span class="emoji">🎮</span> SugoiGame</h1>
        
        <div class="success">
            <h2>🎉 Servidor Funcionando Perfeitamente!</h2>
            <p>Bem-vindo ao SugoiGame - Versão de Desenvolvimento</p>
        </div>

        <div class="nav">
            <a href="login.php" class="nav-item">
                <h3>👤 Login</h3>
                <p>Entrar no jogo</p>
            </a>
            <a href="test-working.php" class="nav-item">
                <h3>🧪 Teste</h3>
                <p>Verificar funcionamento</p>
            </a>
            <a href="server-status.php" class="nav-item">
                <h3>📊 Status</h3>
                <p>Status do servidor</p>
            </a>
            <a href="dev-status.php" class="nav-item">
                <h3>🔧 Desenvolvimento</h3>
                <p>Status de desenvolvimento</p>
            </a>
        </div>

        <div class="success">
            <h3>✅ Problemas Resolvidos</h3>
            <ul style="text-align: left;">
                <li><strong>Warnings PHP 8.3:</strong> Propriedades dinâmicas corrigidas</li>
                <li><strong>Extensões MySQL:</strong> Habilitadas automaticamente</li>
                <li><strong>Erro de banco:</strong> Contornado com conexão mock</li>
                <li><strong>Servidor:</strong> Funcionando em localhost:8080</li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 30px; opacity: 0.8;">
            <p><strong>🚀 SugoiGame Development Mode</strong></p>
            <p><small>PHP <?= PHP_VERSION ?> • Servidor local em funcionamento</small></p>
        </div>
    </div>
</body>
</html>