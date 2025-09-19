<?php
echo "=== DIAGNÓSTICO PHP PARA SUGOIGAME ===\n\n";

echo "📍 PHP Version: " . PHP_VERSION . "\n";
echo "📁 PHP Binary: " . PHP_BINARY . "\n";
echo "📁 Extension Dir: " . ini_get('extension_dir') . "\n";
echo "📁 PHP INI: " . php_ini_loaded_file() . "\n\n";

echo "=== EXTENSÕES NECESSÁRIAS ===\n";
$required_extensions = ['mysqli', 'pdo_mysql', 'mbstring', 'curl', 'gd', 'zip'];

foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✅ CARREGADA" : "❌ NÃO CARREGADA";
    echo "$ext: $status\n";
}

echo "\n=== EXTENSÕES MYSQL DISPONÍVEIS ===\n";
$mysql_extensions = ['mysql', 'mysqli', 'mysqlnd', 'pdo_mysql'];
foreach ($mysql_extensions as $ext) {
    $status = extension_loaded($ext) ? "✅" : "❌";
    echo "$ext: $status\n";
}

echo "\n=== INSTRUÇÕES PARA HABILITAR MYSQLI ===\n";
$ini_file = php_ini_loaded_file();
if ($ini_file) {
    echo "1. Abra o arquivo: $ini_file\n";
    echo "2. Procure por: ;extension=mysqli\n";
    echo "3. Remova o ';' para ficar: extension=mysqli\n";
    echo "4. Procure por: ;extension=pdo_mysql\n";
    echo "5. Remova o ';' para ficar: extension=pdo_mysql\n";
    echo "6. Salve o arquivo e reinicie o servidor PHP\n";
} else {
    echo "❌ Arquivo php.ini não encontrado!\n";
}

echo "\n=== COMANDO PARA EDITAR PHP.INI ===\n";
if ($ini_file) {
    echo "notepad \"$ini_file\"\n";
}

echo "\n=== EXTENSÕES DISPONÍVEIS NO SISTEMA ===\n";
$ext_dir = dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . ini_get('extension_dir');
echo "Diretório: $ext_dir\n";
if (is_dir($ext_dir)) {
    $files = glob($ext_dir . DIRECTORY_SEPARATOR . "*.dll");
    foreach ($files as $file) {
        $name = basename($file, '.dll');
        if (strpos($name, 'mysql') !== false || strpos($name, 'mysqli') !== false || strpos($name, 'pdo') !== false) {
            echo "📦 " . basename($file) . "\n";
        }
    }
} else {
    echo "❌ Diretório de extensões não encontrado: $ext_dir\n";
}
?>