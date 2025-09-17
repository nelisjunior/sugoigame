<?php
/**
 * Script para criar todas as tabelas necessárias do sistema de buff
 */

echo "🔧 Criando todas as tabelas de buff necessárias...\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=sugoi_v2', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado ao banco sugoi_v2!\n";
    
    // Tabelas de buff necessárias
    $buff_tables = [
        'tb_buff_global' => "
            CREATE TABLE IF NOT EXISTS tb_buff_global (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                descricao TEXT,
                tipo ENUM('ataque', 'defesa', 'velocidade', 'experiencia') DEFAULT 'ataque',
                valor INT DEFAULT 0,
                duracao INT DEFAULT 3600,
                ativo TINYINT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
        ",
        
        'tb_tripulacao' => "
            CREATE TABLE IF NOT EXISTS tb_tripulacao (
                id INT AUTO_INCREMENT PRIMARY KEY,
                conta_id INT NOT NULL,
                nome VARCHAR(255) NOT NULL,
                lvl INT DEFAULT 1,
                exp INT DEFAULT 0,
                status ENUM('ativo', 'inativo') DEFAULT 'ativo',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
        ",
        
        'tb_usuarios' => "
            CREATE TABLE IF NOT EXISTS tb_usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                conta_id INT NOT NULL,
                nome VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                status ENUM('ativo', 'inativo', 'banido') DEFAULT 'ativo',
                ultimo_login TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
        ",
        
        'tb_navio' => "
            CREATE TABLE IF NOT EXISTS tb_navio (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tripulacao_id INT NOT NULL,
                nome VARCHAR(255) NOT NULL DEFAULT 'Going Merry',
                tipo VARCHAR(100) DEFAULT 'básico',
                hp INT DEFAULT 1000,
                hp_max INT DEFAULT 1000,
                resistencia INT DEFAULT 100,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
        "
    ];
    
    $created_count = 0;
    foreach ($buff_tables as $table_name => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Tabela '$table_name' criada/verificada\n";
            $created_count++;
        } catch (PDOException $e) {
            echo "⚠️ Erro ao criar '$table_name': " . substr($e->getMessage(), 0, 100) . "...\n";
        }
    }
    
    // Inserir dados básicos de exemplo
    echo "\n📊 Inserindo dados básicos...\n";
    
    // Buff global de exemplo
    try {
        $pdo->exec("
            INSERT IGNORE INTO tb_buff_global (id, nome, descricao, tipo, valor, duracao) VALUES 
            (1, 'Boost de Ataque', 'Aumenta o ataque em 10%', 'ataque', 10, 3600),
            (2, 'Boost de Defesa', 'Aumenta a defesa em 15%', 'defesa', 15, 3600),
            (3, 'Experiência Dupla', 'Dobra a experiência ganha', 'experiencia', 100, 7200)
        ");
        echo "✅ Buffs globais básicos inseridos\n";
    } catch (Exception $e) {
        echo "⚠️ Erro ao inserir buffs: " . substr($e->getMessage(), 0, 50) . "...\n";
    }
    
    echo "\n📊 Resumo:\n";
    echo "✅ Tabelas processadas: $created_count/" . count($buff_tables) . "\n";
    
    // Verificar todas as tabelas importantes
    echo "\n🔍 Verificando todas as tabelas críticas:\n";
    $all_critical_tables = [
        'tb_ban', 'tb_tripulacao_buff', 'tb_personagem', 'tb_alianca', 
        'tb_conta', 'tb_variavel_global', 'tb_buff_global', 'tb_tripulacao',
        'tb_usuarios', 'tb_navio', 'chat'
    ];
    
    foreach ($all_critical_tables as $table) {
        try {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() > 0) {
                echo "  ✅ $table - OK\n";
            } else {
                echo "  ❌ $table - NÃO ENCONTRADA\n";
            }
        } catch (Exception $e) {
            echo "  ❌ $table - ERRO\n";
        }
    }
    
    echo "\n🎉 Todas as tabelas de sistema criadas!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>