<?php
// Arquivo de teste simples para verificar se o servidor está funcionando
echo "<h1>🚀 Servidor SugoiGame - Teste</h1>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

echo "<h2>📋 Extensões PHP Disponíveis:</h2>";
$extensions = get_loaded_extensions();
sort($extensions);
echo "<ul>";
foreach ($extensions as $ext) {
    echo "<li>$ext</li>";
}
echo "</ul>";

echo "<h2>⚠️ Status do Banco de Dados:</h2>";
if (extension_loaded('mysqli')) {
    echo "✅ Extension mysqli está carregada";
} else {
    echo "❌ Extension mysqli NÃO está carregada";
    echo "<br>💡 Para resolver: habilite extension=mysqli no php.ini";
}

if (extension_loaded('pdo_mysql')) {
    echo "<br>✅ Extension pdo_mysql está carregada";
} else {
    echo "<br>❌ Extension pdo_mysql NÃO está carregada";
}

echo "<h2>📁 Estrutura do Projeto:</h2>";
echo "<p>Você está em: <code>" . __DIR__ . "</code></p>";
echo "<p>Arquivos disponíveis:</p>";
echo "<ul>";
$files = array_diff(scandir(__DIR__), array('..', '.'));
foreach (array_slice($files, 0, 10) as $file) {
    echo "<li>$file</li>";
}
echo "</ul>";

echo "<hr>";
echo "<p>🎯 <strong>Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Habilitar extensão mysqli no PHP</li>";
echo "<li>Configurar banco de dados MySQL</li>";
echo "<li>Testar conexão</li>";
echo "</ol>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 40px; }
h1 { color: #2196F3; }
h2 { color: #4CAF50; }
.error { color: #f44336; }
.success { color: #4CAF50; }
ul { column-count: 3; }
code { background: #f5f5f5; padding: 2px 4px; border-radius: 3px; }
</style>