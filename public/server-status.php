<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 SugoiGame - Servidor Local</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        .status-card {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            border-left: 5px solid #4CAF50;
        }
        .status-card.warning {
            border-left-color: #FF9800;
        }
        .status-card.error {
            border-left-color: #f44336;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin: 2px;
        }
        .badge.success { background: #4CAF50; }
        .badge.error { background: #f44336; }
        .badge.warning { background: #FF9800; }
        h1 { text-align: center; margin-bottom: 30px; }
        h2 { color: #fff; margin-top: 0; }
        .emoji { font-size: 1.5em; margin-right: 10px; }
        .code { 
            background: rgba(0,0,0,0.3); 
            padding: 10px; 
            border-radius: 8px; 
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .step {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
        }
        a {
            color: #81C784;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 SugoiGame - Servidor de Desenvolvimento</h1>
        
        <div class="grid">
            <div class="status-card">
                <h2><span class="emoji">✅</span>Servidor PHP</h2>
                <p><strong>Status:</strong> <span class="badge success">FUNCIONANDO</span></p>
                <p><strong>URL:</strong> <a href="http://localhost:8080">http://localhost:8080</a></p>
                <p><strong>PHP Version:</strong> <?= PHP_VERSION ?></p>
                <p><strong>Server Time:</strong> <?= date('d/m/Y H:i:s') ?></p>
            </div>

            <div class="status-card">
                <h2><span class="emoji">🔧</span>Extensões PHP</h2>
                <?php
                $required_exts = [
                    'mysqli' => 'Conexão MySQL',
                    'pdo_mysql' => 'PDO MySQL',
                    'mbstring' => 'Strings multibyte',
                    'curl' => 'Requisições HTTP',
                    'gd' => 'Manipulação de imagens',
                    'json' => 'Processamento JSON'
                ];
                
                foreach ($required_exts as $ext => $desc) {
                    $status = extension_loaded($ext);
                    $badge = $status ? 'success' : 'error';
                    $icon = $status ? '✅' : '❌';
                    echo "<p>$icon <strong>$ext:</strong> <span class='badge $badge'>" . ($status ? 'OK' : 'FALTANDO') . "</span><br><small>$desc</small></p>";
                }
                ?>
            </div>
        </div>

        <div class="status-card error">
            <h2><span class="emoji">🗄️</span>Banco de Dados</h2>
            <p><strong>Status:</strong> <span class="badge error">NÃO CONFIGURADO</span></p>
            <p><strong>Problema:</strong> Tabela 'sugoi_v2.tb_ban' não existe</p>
            <div class="step">
                <h3>📋 Para configurar o banco de dados:</h3>
                <ol>
                    <li>Instale o MySQL/MariaDB</li>
                    <li>Crie o banco de dados 'sugoi_v2'</li>
                    <li>Execute o script de criação das tabelas</li>
                    <li>Configure as credenciais de conexão</li>
                </ol>
            </div>
        </div>

        <div class="status-card warning">
            <h2><span class="emoji">⚠️</span>Avisos PHP 8.3</h2>
            <p>Encontrados avisos de compatibilidade:</p>
            <div class="code">
                PHP Deprecated: Creation of dynamic property mywrap_result::$columns is deprecated
            </div>
            <p><strong>Solução:</strong> Código precisa ser atualizado para PHP 8.3 (você está na branch feature/php8-migration ✅)</p>
        </div>

        <div class="grid">
            <div class="status-card">
                <h2><span class="emoji">📁</span>Estrutura do Projeto</h2>
                <p><strong>Diretório:</strong> <?= __DIR__ ?></p>
                <p><strong>Arquivos principais encontrados:</strong></p>
                <?php
                $important_files = ['index.php', 'login.php', 'header.php', 'composer.json'];
                foreach ($important_files as $file) {
                    $exists = file_exists(__DIR__ . '/' . $file);
                    $icon = $exists ? '✅' : '❌';
                    echo "<p>$icon $file</p>";
                }
                ?>
            </div>

            <div class="status-card">
                <h2><span class="emoji">🔗</span>Links Úteis</h2>
                <p><a href="http://localhost:8080">🏠 Página Principal</a></p>
                <p><a href="http://localhost:8080/test-server.php">🧪 Teste do Servidor</a></p>
                <p><a href="http://localhost:8080/login.php">👤 Login</a></p>
                <p><a href="http://localhost:8080/dev-status.php">📊 Status de Desenvolvimento</a></p>
            </div>
        </div>

        <div class="status-card">
            <h2><span class="emoji">🎯</span>Próximos Passos</h2>
            <div class="step">
                <h3>1. Configurar MySQL</h3>
                <div class="code">
                    # Instalar MySQL (se não tiver)<br>
                    # Criar banco: CREATE DATABASE sugoi_v2;<br>
                    # Importar schema: mysql -u root -p sugoi_v2 < database/schema.sql
                </div>
            </div>
            <div class="step">
                <h3>2. Corrigir Warnings PHP 8.3</h3>
                <p>Atualizar código na pasta <code>Includes/database/</code> para compatibilidade total</p>
            </div>
            <div class="step">
                <h3>3. Testar Funcionalidades</h3>
                <p>Após configurar o banco, testar login e funcionalidades principais</p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px; opacity: 0.8;">
            <p>🚀 Servidor iniciado com sucesso! Extensões MySQL habilitadas.</p>
            <p><small>Atualizado em: <?= date('d/m/Y H:i:s') ?></small></p>
        </div>
    </div>
</body>
</html>