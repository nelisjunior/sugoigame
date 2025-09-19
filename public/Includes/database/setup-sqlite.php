<?php
/**
 * Configuração de banco de dados para desenvolvimento
 * Cria banco SQLite temporário com as tabelas essenciais
 */

// Verificar se SQLite está disponível
if (!extension_loaded('sqlite3')) {
    die('Extensão SQLite3 não está disponível');
}

$db_path = __DIR__ . '/../../database/sugoi_dev.sqlite';
$db_dir = dirname($db_path);

// Criar diretório se não existir
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0777, true);
}

try {
    // Conectar ao SQLite
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar tabela tb_ban (a que está causando erro)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tb_ban (
            ip VARCHAR(30) NOT NULL
        )
    ");
    
    // Criar outras tabelas essenciais para evitar erros
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS chat (
            id_message INTEGER PRIMARY KEY AUTOINCREMENT,
            conta_id INTEGER,
            capitao VARCHAR(255),
            message VARCHAR(255),
            canal VARCHAR(255),
            date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tb_personagem (
            cod INTEGER PRIMARY KEY AUTOINCREMENT,
            conta VARCHAR(255),
            nome VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "✅ Banco SQLite criado com sucesso em: $db_path\n";
    echo "📋 Tabelas criadas: tb_ban, chat, tb_personagem\n";
    
} catch (PDOException $e) {
    echo "❌ Erro ao criar banco SQLite: " . $e->getMessage() . "\n";
}