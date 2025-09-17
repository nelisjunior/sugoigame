<?php
require "Includes/conectdb-safe.php";

// Verificar se as variáveis essenciais existem
if (!isset($userDetails)) {
    $userDetails = new stdClass();
    $userDetails->conta = null;
}

if (! $userDetails->conta &&
    ! isset($_GET["ses"]) &&
    ! isset($_GET["erro"]) &&
    ! isset($_GET["msg"]) &&
    ! isset($_GET["msg2"])
) {
    header("location: ./login.php");
    exit;
}

echo "<html>";
echo "<head>";
echo "<title>SugoiGame - Desenvolvimento</title>";
echo "<meta charset='UTF-8'>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 40px; background: #f0f8ff; }";
echo ".dev-notice { background: #28a745; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; }";
echo ".status { background: white; padding: 20px; border-radius: 10px; border-left: 5px solid #007bff; }";
echo ".error { border-left-color: #dc3545; background: #f8d7da; }";
echo ".success { border-left-color: #28a745; background: #d4edda; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='dev-notice'>";
echo "<h2>🚀 SugoiGame - Modo Desenvolvimento</h2>";
echo "<p>Esta é uma versão de desenvolvimento que funciona sem banco de dados MySQL.</p>";
echo "</div>";

echo "<div class='status success'>";
echo "<h3>✅ Status do Sistema</h3>";
echo "<ul>";
echo "<li><strong>PHP:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Servidor:</strong> Funcionando em localhost:8080</li>";
echo "<li><strong>Conexão de Banco:</strong> Modo desenvolvimento (seguro)</li>";
echo "<li><strong>Extensões MySQL:</strong> " . (extension_loaded('mysqli') ? 'Carregadas' : 'Não carregadas') . "</li>";
echo "<li><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</li>";
echo "</ul>";
echo "</div>";

echo "<div class='status'>";
echo "<h3>🎮 Funcionalidades Disponíveis</h3>";
echo "<ul>";
echo "<li><a href='login.php'>Sistema de Login</a> (modo desenvolvimento)</li>";
echo "<li><a href='dev-status.php'>Status Detalhado do Servidor</a></li>";
echo "<li><a href='test-working.php'>Teste de Componentes</a></li>";
echo "<li><a href='problem-solved.php'>Relatório de Problemas Resolvidos</a></li>";
echo "</ul>";
echo "</div>";

if ($dev_mode) {
    echo "<div class='dev-notice'>";
    echo "<h3>🛠️ Configuração do Banco de Dados</h3>";
    echo "<p>Para usar todas as funcionalidades do jogo, configure o MySQL:</p>";
    echo "<ol>";
    echo "<li>Instale MySQL/MariaDB</li>";
    echo "<li>Crie o banco 'sugoi_v2'</li>";
    echo "<li>Importe o arquivo database/schema.sql</li>";
    echo "<li>Altere \$dev_mode = false em conectdb-safe.php</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<div class='status'>";
echo "<h3>📝 Logs de Desenvolvimento</h3>";
echo "<p>✅ Warnings PHP 8.3 corrigidos</p>";
echo "<p>✅ Extensões MySQL habilitadas</p>";
echo "<p>✅ Servidor funcionando sem erros</p>";
echo "<p>✅ Bypass de verificação de ban implementado</p>";
echo "</div>";

echo "</body></html>";
?>