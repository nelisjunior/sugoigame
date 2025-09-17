<?php
// Página de teste do ambiente de desenvolvimento
require_once 'Constantes/configs.dev.php';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎮 Sugoi Game - Ambiente de Desenvolvimento</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #1a1a2e; color: #eee; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; }
        .status { margin: 20px 0; padding: 15px; border-radius: 8px; }
        .success { background: #2d5a27; border-left: 4px solid #4caf50; }
        .warning { background: #5a4d27; border-left: 4px solid #ff9800; }
        .error { background: #5a2727; border-left: 4px solid #f44336; }
        .info { background: #27435a; border-left: 4px solid #2196f3; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .card { background: #16213e; padding: 20px; border-radius: 8px; border: 1px solid #0f3460; }
        .version { font-size: 0.9em; color: #888; }
        pre { background: #0f1419; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎮 Sugoi Game</h1>
            <h2>One Piece MMORPG - Ambiente de Desenvolvimento</h2>
            <div class="version">Migração PHP 8.x • Branch: feature/php8-migration</div>
        </div>

        <?php
        $status = [
            'php' => false,
            'extensions' => false,
            'database' => false,
            'composer' => false,
            'config' => false
        ];
        ?>

        <!-- Status do PHP -->
        <div class="status <?php echo version_compare(PHP_VERSION, '8.1.0', '>=') ? 'success' : 'error'; ?>">
            <h3>🐘 PHP Status</h3>
            <p><strong>Versão:</strong> <?php echo PHP_VERSION; ?></p>
            <p><strong>SAPI:</strong> <?php echo PHP_SAPI; ?></p>
            <p><strong>Requerido:</strong> ≥ 8.1.0</p>
            <?php $status['php'] = version_compare(PHP_VERSION, '8.1.0', '>='); ?>
        </div>

        <!-- Extensões PHP -->
        <div class="status <?php 
            $required_ext = ['mysqli', 'curl', 'json', 'mbstring', 'xml', 'zip'];
            $loaded_ext = get_loaded_extensions();
            $missing = array_diff($required_ext, $loaded_ext);
            echo empty($missing) ? 'success' : 'warning';
            $status['extensions'] = empty($missing);
        ?>">
            <h3>🔧 Extensões PHP</h3>
            <?php foreach($required_ext as $ext): ?>
                <p>
                    <?php echo extension_loaded($ext) ? '✅' : '❌'; ?> 
                    <strong><?php echo $ext; ?></strong>
                </p>
            <?php endforeach; ?>
            <?php if(!empty($missing)): ?>
                <p><strong>Faltando:</strong> <?php echo implode(', ', $missing); ?></p>
            <?php endif; ?>
        </div>

        <!-- Status do Banco -->
        <div class="status <?php
            try {
                $conn = new mysqli(str_replace('p:', '', DB_SERVER), DB_USER, DB_PASS, DB_NAME);
                if ($conn->connect_error) throw new Exception($conn->connect_error);
                $result = $conn->query("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
                $tables = $result->fetch_assoc()['total'];
                $conn->close();
                echo 'success';
                $status['database'] = true;
            } catch (Exception $e) {
                echo 'error';
                $status['database'] = false;
            }
        ?>">
            <h3>🗄️ Banco de Dados</h3>
            <p><strong>Host:</strong> <?php echo DB_SERVER; ?></p>
            <p><strong>Banco:</strong> <?php echo DB_NAME; ?></p>
            <p><strong>Usuário:</strong> <?php echo DB_USER; ?></p>
            <?php if($status['database']): ?>
                <p><strong>Status:</strong> ✅ Conectado</p>
                <p><strong>Tabelas:</strong> <?php echo $tables ?? 0; ?></p>
            <?php else: ?>
                <p><strong>Status:</strong> ❌ Erro de conexão</p>
                <p><strong>Erro:</strong> <?php echo $e->getMessage() ?? 'Desconhecido'; ?></p>
            <?php endif; ?>
        </div>

        <!-- Status do Composer -->
        <div class="status <?php 
            echo file_exists('../vendor/autoload.php') ? 'success' : 'error';
            $status['composer'] = file_exists('../vendor/autoload.php');
        ?>">
            <h3>📦 Composer</h3>
            <p><strong>Autoload:</strong> <?php echo file_exists('../vendor/autoload.php') ? '✅ Disponível' : '❌ Não encontrado'; ?></p>
            <?php if(file_exists('../composer.lock')): ?>
                <p><strong>Lock file:</strong> ✅ Presente</p>
                <?php
                $lock = json_decode(file_get_contents('../composer.lock'), true);
                $packages = count($lock['packages'] ?? []);
                $devPackages = count($lock['packages-dev'] ?? []);
                ?>
                <p><strong>Pacotes:</strong> <?php echo $packages; ?> (+ <?php echo $devPackages; ?> dev)</p>
            <?php endif; ?>
        </div>

        <div class="grid">
            <!-- Configurações -->
            <div class="card">
                <h3>⚙️ Configurações</h3>
                <p><strong>Ambiente:</strong> Desenvolvimento</p>
                <p><strong>Debug:</strong> <?php echo ini_get('display_errors') ? 'Ativo' : 'Inativo'; ?></p>
                <p><strong>Stripe:</strong> <?php echo defined('STRIPE_TOKEN_PUBLIC') ? 'Configurado' : 'Não configurado'; ?></p>
                <p><strong>PagSeguro:</strong> <?php echo defined('PS_ENV') ? PS_ENV : 'Não configurado'; ?></p>
            </div>

            <!-- Ações Rápidas -->
            <div class="card">
                <h3>🚀 Ações Rápidas</h3>
                <p>📊 <a href="?action=phpinfo" style="color: #4caf50;">Ver phpinfo()</a></p>
                <p>🗃️ <a href="../database/schema.sql" style="color: #4caf50;">Ver Schema SQL</a></p>
                <p>📝 <a href="../docs/" style="color: #4caf50;">Documentação</a></p>
                <p>🎮 <a href="index.php" style="color: #4caf50;">Ir para o Jogo</a></p>
            </div>
        </div>

        <!-- Status Geral -->
        <div class="status <?php 
            $all_ok = $status['php'] && $status['extensions'] && $status['database'] && $status['composer'];
            echo $all_ok ? 'success' : 'warning';
        ?>">
            <h3>📊 Status Geral</h3>
            <?php if($all_ok): ?>
                <p><strong>✅ Ambiente pronto para desenvolvimento!</strong></p>
                <p>Todos os componentes estão funcionando corretamente.</p>
            <?php else: ?>
                <p><strong>⚠️ Ambiente parcialmente configurado</strong></p>
                <p>Alguns componentes precisam de atenção.</p>
            <?php endif; ?>
        </div>

        <?php if(isset($_GET['action']) && $_GET['action'] === 'phpinfo'): ?>
            <div class="status info">
                <h3>📋 Informações do PHP</h3>
                <div style="background: white; color: black; padding: 20px; border-radius: 8px; margin-top: 10px;">
                    <?php phpinfo(); ?>
                </div>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 40px; color: #666;">
            <p>Sugoi Game - One Piece MMORPG • Ambiente de Desenvolvimento</p>
            <p>Servidor: localhost:8000 • Banco: <?php echo DB_NAME; ?></p>
        </div>
    </div>
</body>
</html>