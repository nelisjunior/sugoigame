<?php
/**
 * Script para criar tabelas essenciais que estão faltando
 */

echo "🔧 Criando tabelas essenciais...\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=sugoi_v2', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado ao banco sugoi_v2!\n";
    
    // Lista de tabelas essenciais que estão faltando
    $essential_tables = [
        'tb_tripulacao_buff' => "
            CREATE TABLE IF NOT EXISTS tb_tripulacao_buff (
                id INT AUTO_INCREMENT PRIMARY KEY,
                personagem_id INT NOT NULL,
                buff_tipo VARCHAR(50) NOT NULL,
                buff_valor INT DEFAULT 0,
                expira_em TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
        ",
        
        'tb_personagem' => "
            CREATE TABLE IF NOT EXISTS tb_personagem (
                cod INT AUTO_INCREMENT PRIMARY KEY,
                conta VARCHAR(255) NOT NULL,
                nome VARCHAR(255) NOT NULL,
                classe INT DEFAULT 1,
                lvl INT DEFAULT 1,
                exp INT DEFAULT 0,
                hp INT DEFAULT 100,
                fa INT DEFAULT 10,
                fd INT DEFAULT 10,
                agilidade INT DEFAULT 10,
                resistencia INT DEFAULT 10,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
        ",
        
        'tb_alianca' => "
            CREATE TABLE IF NOT EXISTS tb_alianca (
                cod_alianca INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                img INT NOT NULL DEFAULT 1,
                mural TEXT,
                xp INT DEFAULT 0,
                xp_max INT DEFAULT 500,
                lvl INT DEFAULT 1,
                score INT DEFAULT 0,
                vitorias INT DEFAULT 0,
                derrotas INT DEFAULT 0,
                banco BIGINT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
        ",
        
        'tb_conta' => "
            CREATE TABLE IF NOT EXISTS tb_conta (
                cod INT AUTO_INCREMENT PRIMARY KEY,
                login VARCHAR(255) UNIQUE NOT NULL,
                senha VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                ativo TINYINT DEFAULT 1,
                ultimo_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
        "
    ];
    
    $created_count = 0;
    foreach ($essential_tables as $table_name => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Tabela '$table_name' criada/verificada\n";
            $created_count++;
        } catch (PDOException $e) {
            echo "⚠️ Erro ao criar '$table_name': " . substr($e->getMessage(), 0, 100) . "...\n";
        }
    }
    
    echo "\n📊 Resumo:\n";
    echo "✅ Tabelas processadas: $created_count/" . count($essential_tables) . "\n";
    
    // Verificar se todas as tabelas importantes existem agora
    echo "\n🔍 Verificando tabelas críticas:\n";
    $critical_tables = ['tb_ban', 'tb_tripulacao_buff', 'tb_personagem', 'tb_alianca', 'tb_conta', 'tb_variavel_global'];
    
    foreach ($critical_tables as $table) {
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
    
    echo "\n🎉 Configuração de tabelas essenciais concluída!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>