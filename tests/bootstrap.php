<?php
declare(strict_types=1);

// Bootstrap file para testes PHPUnit
require_once __DIR__ . '/../vendor/autoload.php';

// Definir constantes de teste se não existirem
if (!defined('DB_SERVER')) {
    define('DB_SERVER', 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'test_user');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', 'test_pass');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'sugoigame_test');
}

// Outras constantes necessárias para os testes
if (!defined('STRIPE_SECRET_KEY')) {
    define('STRIPE_SECRET_KEY', 'sk_test_...');
}

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// Função helper para testes
function createTestDatabase(): void {
    // Implementar criação de banco de teste se necessário
}

function cleanTestDatabase(): void {
    // Implementar limpeza do banco de teste
}