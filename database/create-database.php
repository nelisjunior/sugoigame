<?php
/**
 * Script para criar banco de dados sugoi_v2 no MySQL do XAMPP
 */

echo "🚀 Configurando banco de dados MySQL...\n\n";

try {
    // Conectar ao MySQL (sem especificar banco)
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado ao MySQL com sucesso!\n";
    
    // Criar banco de dados
    $pdo->exec("CREATE DATABASE IF NOT EXISTS sugoi_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Banco 'sugoi_v2' criado com sucesso!\n";
    
    // Usar o banco
    $pdo->exec("USE sugoi_v2");
    echo "✅ Banco 'sugoi_v2' selecionado!\n";
    
    // Criar tabela tb_ban (a que está causando erro)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tb_ban (
            ip VARCHAR(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci
    ");
    echo "✅ Tabela 'tb_ban' criada!\n";
    
    // Criar algumas outras tabelas essenciais
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS chat (
            id_message INT NOT NULL AUTO_INCREMENT,
            conta_id INT DEFAULT NULL,
            capitao VARCHAR(255) DEFAULT NULL,
            message VARCHAR(255) DEFAULT NULL,
            canal VARCHAR(255) DEFAULT NULL,
            date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id_message)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3
    ");
    echo "✅ Tabela 'chat' criada!\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tb_variavel_global (
            nome VARCHAR(50) NOT NULL,
            valor TEXT,
            PRIMARY KEY (nome)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
    ");
    echo "✅ Tabela 'tb_variavel_global' criada!\n";
    
    // Inserir algumas variáveis globais padrão
    $pdo->exec("
        INSERT IGNORE INTO tb_variavel_global (nome, valor) VALUES 
        ('versao', '2.0-dev'),
        ('manutencao', '0'),
        ('servidor_status', '1'),
        ('max_usuarios', '1000')
    ");
    echo "✅ Variáveis globais inseridas!\n";
    
    echo "\n🎉 Banco de dados configurado com sucesso!\n";
    echo "📋 Tabelas criadas: tb_ban, chat, tb_variavel_global\n";
    echo "🔧 Agora você pode usar o modo produção!\n\n";
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>