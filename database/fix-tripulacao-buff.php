<?php
/**
 * Script para corrigir estrutura da tabela tb_tripulacao_buff
 */

echo "🔧 Corrigindo estrutura da tabela tb_tripulacao_buff...\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=sugoi_v2', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado ao banco sugoi_v2!\n";
    
    // Verificar estrutura atual da tabela
    echo "🔍 Verificando estrutura atual...\n";
    $result = $pdo->query("DESCRIBE tb_tripulacao_buff");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Colunas atuais: " . implode(", ", $columns) . "\n\n";
    
    // Verificar se tripulacao_id existe
    if (!in_array('tripulacao_id', $columns)) {
        echo "➕ Adicionando coluna 'tripulacao_id'...\n";
        $pdo->exec("ALTER TABLE tb_tripulacao_buff ADD COLUMN tripulacao_id INT NOT NULL AFTER personagem_id");
        echo "✅ Coluna 'tripulacao_id' adicionada!\n";
    } else {
        echo "✅ Coluna 'tripulacao_id' já existe!\n";
    }
    
    // Verificar se expiracao existe (pode ser que seja expira_em)
    if (!in_array('expiracao', $columns)) {
        if (in_array('expira_em', $columns)) {
            echo "🔄 Renomeando coluna 'expira_em' para 'expiracao'...\n";
            $pdo->exec("ALTER TABLE tb_tripulacao_buff CHANGE expira_em expiracao TIMESTAMP NULL");
            echo "✅ Coluna renomeada para 'expiracao'!\n";
        } else {
            echo "➕ Adicionando coluna 'expiracao'...\n";
            $pdo->exec("ALTER TABLE tb_tripulacao_buff ADD COLUMN expiracao TIMESTAMP NULL");
            echo "✅ Coluna 'expiracao' adicionada!\n";
        }
    } else {
        echo "✅ Coluna 'expiracao' já existe!\n";
    }
    
    // Verificar estrutura final
    echo "\n🔍 Estrutura final da tabela:\n";
    $result = $pdo->query("DESCRIBE tb_tripulacao_buff");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Field']} ({$row['Type']}) {$row['Null']} {$row['Key']}\n";
    }
    
    echo "\n🎉 Estrutura da tabela tb_tripulacao_buff corrigida!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>