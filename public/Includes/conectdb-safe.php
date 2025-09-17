<?php
require_once "database/mywrap.php";

// Permite importar classes automaticamente com uso de namespaces
spl_autoload_register(function ($class) {
    $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    $file = str_replace("Includes", "", __DIR__).$class_path.'.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

// mysqli
$connection = new mywrap_con();
$connection->run("SET NAMES 'utf8'");
$connection->run("SET character_set_connection=utf8");
$connection->run("SET character_set_client=utf8");
$connection->run("SET character_set_results=utf8");
$connection->run("SET CHARACTER SET utf8");

// DESENVOLVIMENTO: Bypass da verificação de ban para evitar erro de tabela inexistente
$dev_mode = false; // Altere para false quando o banco estiver configurado - BANCO CONFIGURADO!

if (!$dev_mode) {
    try {
        $result = $connection->run("SELECT * FROM tb_ban WHERE ip = ?", "s", $_SERVER['REMOTE_ADDR']);
        if ($result->count()) {
            echo "Você não tem permissão para acessar esse site!";
            exit;
        }
    } catch (Exception $e) {
        // Em desenvolvimento, ignore erros de tabela inexistente
        if (strpos($e->getMessage(), "doesn't exist") === false) {
            throw $e; // Re-throw se não for erro de tabela inexistente
        }
    }
} else {
    // Modo desenvolvimento: não verificar ban
    // echo "<!-- Modo desenvolvimento: verificação de ban desabilitada -->";
}

global $connection;

function conectar(){
    global $connection;
    return $connection;
}

function desconectar(){
    global $connection;
    $connection = null;
}

function sair($pagina){
    header("location: $pagina");
    exit;
}

/* --------------- Váriaveis globais de sistema --------------- */

function get_global_var($nome) {
    global $connection;
    
    if (!$dev_mode) {
        try {
            $result = $connection->run("SELECT * FROM tb_variavel_global WHERE nome = ?", "s", $nome);
            if ($result->count()) {
                return $result->fetch()['valor'];
            }
        } catch (Exception $e) {
            // Em desenvolvimento, retornar valores padrão
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                return getDefaultGlobalVar($nome);
            }
            throw $e;
        }
    } else {
        return getDefaultGlobalVar($nome);
    }
    
    return "";
}

function getDefaultGlobalVar($nome) {
    // Valores padrão para desenvolvimento
    $defaults = [
        'versao' => '2.0-dev',
        'manutencao' => '0',
        'max_usuarios' => '1000',
        'servidor_status' => '1'
    ];
    
    return isset($defaults[$nome]) ? $defaults[$nome] : '';
}

function set_global_var($nome, $valor) {
    global $connection, $dev_mode;
    
    if (!$dev_mode) {
        try {
            $connection->run("REPLACE INTO tb_variavel_global (nome, valor) VALUES (?, ?)", "ss", array($nome, $valor));
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "doesn't exist") === false) {
                throw $e;
            }
            // Em desenvolvimento, ignore erros de tabela inexistente
        }
    }
    // Em modo desenvolvimento, não salvar no banco
}