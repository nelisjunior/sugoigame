<?php
// Versão temporária do conectdb.php para desenvolvimento sem banco
// Remove a dependência de tabelas existentes

// Configurações de desenvolvimento
$dev_mode = true; // Set to false when database is configured

if ($dev_mode) {
    // Mock database connection for development
    class MockConnection {
        public function run($query, $types = null, $params = null) {
            // Return mock result for development
            return new MockResult();
        }
    }
    
    class MockResult {
        public function count() {
            return 0; // No ban records in development
        }
        
        public function fetch_array() {
            return false;
        }
        
        public function fetch_all_array() {
            return array();
        }
    }
    
    $connection = new MockConnection();
    
    // Log development notice
    error_log("[DEV MODE] Using mock database connection - configure MySQL to disable this");
    
} else {
    // Original database connection code
    require_once("database/mywrap_connection.php");
    require_once("config.php");

    $connection = new mywrap_con($host, $username, $password, $database);

    $connection->run("SET NAMES 'utf8'");
    $connection->run("SET character_set_connection=utf8");
    $connection->run("SET character_set_client=utf8");
    $connection->run("SET character_set_results=utf8");
    $connection->run("SET CHARACTER SET utf8");

    $result = $connection->run("SELECT * FROM tb_ban WHERE ip = ?", "s", $_SERVER['REMOTE_ADDR']);
    if ($result->count()) {
        echo "Você não tem permissão para acessar esse site!";
        exit;
    }
}

// Development status indicator
if ($dev_mode && (isset($_GET['dev']) || strpos($_SERVER['REQUEST_URI'], 'test-') !== false || strpos($_SERVER['REQUEST_URI'], 'server-status') !== false)) {
    echo "<!-- DEV MODE: Mock database connection active -->";
}
?>