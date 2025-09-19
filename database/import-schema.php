<?php
/**
 * Script para importar schema completo do banco sugoi_v2
 */

echo "📦 Importando schema completo...\n\n";

try {
    // Conectar ao banco sugoi_v2
    $pdo = new PDO('mysql:host=localhost;dbname=sugoi_v2', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado ao banco sugoi_v2!\n";
    
    // Ler arquivo schema.sql
    $schema_file = __DIR__ . '/schema.sql';
    if (!file_exists($schema_file)) {
        throw new Exception("Arquivo schema.sql não encontrado!");
    }
    
    $sql_content = file_get_contents($schema_file);
    echo "✅ Schema carregado (" . strlen($sql_content) . " bytes)\n";
    
    // Dividir em comandos individuais (separados por ;)
    $commands = explode(';', $sql_content);
    $success_count = 0;
    $error_count = 0;
    
    echo "🔄 Executando comandos SQL...\n";
    
    foreach ($commands as $command) {
        $command = trim($command);
        
        // Pular comandos vazios e comentários
        if (empty($command) || strpos($command, '--') === 0 || strpos($command, '/*') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($command);
            $success_count++;
            
            // Mostrar progresso para CREATE TABLE
            if (stripos($command, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE\s+`?(\w+)`?/i', $command, $matches);
                if (isset($matches[1])) {
                    echo "  ✅ Tabela '{$matches[1]}' criada\n";
                }
            }
            
        } catch (PDOException $e) {
            $error_count++;
            // Mostrar apenas erros que não sejam "tabela já existe"
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "  ⚠️ Erro: " . substr($e->getMessage(), 0, 100) . "...\n";
            }
        }
    }
    
    echo "\n📊 Resumo da importação:\n";
    echo "✅ Comandos executados com sucesso: $success_count\n";
    echo "⚠️ Comandos com erro/ignorados: $error_count\n";
    
    // Verificar algumas tabelas importantes
    $important_tables = ['tb_ban', 'chat', 'tb_variavel_global', 'tb_personagem', 'tb_alianca'];
    echo "\n🔍 Verificando tabelas importantes:\n";
    
    foreach ($important_tables as $table) {
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
    
    echo "\n🎉 Importação do schema concluída!\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante importação: " . $e->getMessage() . "\n";
    exit(1);
}
?>