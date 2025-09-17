<?php
// Teste de conexão com o banco de dados
echo "🔧 Testando conexão com MySQL...\n";

// Configurações do banco
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'sugoi_v2';

try {
    // Criar conexão inicial sem banco específico
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        throw new Exception("Erro de conexão: " . $conn->connect_error);
    }
    
    echo "✅ Conexão MySQL estabelecida\n";
    
    // Criar banco de dados
    $sql = "CREATE DATABASE IF NOT EXISTS $db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql)) {
        echo "✅ Banco '$db' criado/verificado\n";
    } else {
        throw new Exception("Erro ao criar banco: " . $conn->error);
    }
    
    // Selecionar banco
    $conn->select_db($db);
    
    // Criar tabela de teste
    $sql = "CREATE TABLE IF NOT EXISTS test_connection (
        id INT AUTO_INCREMENT PRIMARY KEY,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        message VARCHAR(255) DEFAULT 'Database configured successfully'
    )";
    
    if ($conn->query($sql)) {
        echo "✅ Tabela de teste criada\n";
    } else {
        throw new Exception("Erro ao criar tabela: " . $conn->error);
    }
    
    // Inserir registro de teste
    $sql = "INSERT INTO test_connection (message) VALUES ('Banco configurado em " . date('Y-m-d H:i:s') . "')";
    if ($conn->query($sql)) {
        echo "✅ Registro de teste inserido\n";
    }
    
    // Verificar dados
    $result = $conn->query("SELECT COUNT(*) as total FROM test_connection");
    $row = $result->fetch_assoc();
    echo "✅ Total de registros de teste: " . $row['total'] . "\n";
    
    $conn->close();
    
    echo "\n🎉 Configuração do banco concluída com sucesso!\n";
    echo "📍 Host: $host\n";
    echo "📍 Banco: $db\n";
    echo "📍 Charset: utf8mb4\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>