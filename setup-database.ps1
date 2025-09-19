# ==========================================
# Setup Automático do Banco SugoiGame
# ==========================================

param(
    [string]$MySQLPath = "",
    [string]$Username = "root", 
    [string]$Password = "",
    [switch]$Help
)

if ($Help) {
    Write-Host "=== Setup Automático do Banco SugoiGame ===" -ForegroundColor Green
    Write-Host ""
    Write-Host "Uso: .\setup-database.ps1 [opções]"
    Write-Host ""
    Write-Host "Parâmetros:"
    Write-Host "  -MySQLPath   Caminho para executável mysql.exe (opcional)"
    Write-Host "  -Username    Usuário MySQL (padrão: root)"
    Write-Host "  -Password    Senha MySQL (padrão: vazio)"
    Write-Host "  -Help        Mostra esta ajuda"
    Write-Host ""
    Write-Host "Exemplos:"
    Write-Host "  .\setup-database.ps1"
    Write-Host "  .\setup-database.ps1 -Username admin -Password minhasenha"
    Write-Host "  .\setup-database.ps1 -MySQLPath 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe'"
    exit
}

Write-Host "=== Setup Automático do Banco SugoiGame ===" -ForegroundColor Green
Write-Host ""

# Verificar se MySQL está instalado
if ([string]::IsNullOrEmpty($MySQLPath)) {
    $mysqlPaths = @(
        "mysql",
        "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe",
        "C:\Program Files\MySQL\MySQL Server 5.7\bin\mysql.exe",
        "C:\xampp\mysql\bin\mysql.exe",
        "C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe"
    )
    
    $MySQLPath = $null
    foreach ($path in $mysqlPaths) {
        try {
            if ($path -eq "mysql") {
                $null = Get-Command mysql -ErrorAction Stop
                $MySQLPath = "mysql"
                break
            } elseif (Test-Path $path) {
                $MySQLPath = $path
                break
            }
        } catch {
            continue
        }
    }
    
    if (-not $MySQLPath) {
        Write-Host "❌ MySQL não encontrado!" -ForegroundColor Red
        Write-Host ""
        Write-Host "Instale o MySQL usando uma das opções:"
        Write-Host "• Via Chocolatey: choco install mysql"
        Write-Host "• Via Winget: winget install Oracle.MySQL"
        Write-Host "• Download: https://dev.mysql.com/downloads/mysql/"
        Write-Host "• XAMPP: https://www.apachefriends.org/"
        Write-Host ""
        Write-Host "Ou especifique o caminho com -MySQLPath"
        exit 1
    }
}

Write-Host "✅ MySQL encontrado: $MySQLPath" -ForegroundColor Green

# Verificar se os arquivos de dump existem
$dumpFile = "database\dump-sugoi_v2limpa.sql"
$setupFile = "database\import-localhost.sql"

if (-not (Test-Path $dumpFile)) {
    Write-Host "❌ Arquivo não encontrado: $dumpFile" -ForegroundColor Red
    exit 1
}

if (-not (Test-Path $setupFile)) {
    Write-Host "❌ Arquivo não encontrado: $setupFile" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Arquivos de dump encontrados" -ForegroundColor Green

# Testar conexão MySQL
Write-Host ""
Write-Host "🔗 Testando conexão MySQL..." -ForegroundColor Yellow

$passwordArg = if ([string]::IsNullOrEmpty($Password)) { "" } else { "-p$Password" }

try {
    $testQuery = "SELECT 'Conexão OK' as status;"
    $testResult = & $MySQLPath -u $Username $passwordArg -e $testQuery 2>&1
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Erro na conexão MySQL:" -ForegroundColor Red
        Write-Host $testResult
        Write-Host ""
        Write-Host "Verifique:"
        Write-Host "• Se o MySQL está rodando: net start mysql"
        Write-Host "• Se as credenciais estão corretas"
        Write-Host "• Se a porta 3306 está disponível"
        exit 1
    }
    
    Write-Host "✅ Conexão MySQL estabelecida" -ForegroundColor Green
} catch {
    Write-Host "❌ Erro ao testar conexão: $_" -ForegroundColor Red
    exit 1
}

# Criar banco de dados
Write-Host ""
Write-Host "🗄️ Criando banco de dados..." -ForegroundColor Yellow

try {
    Get-Content $setupFile | & $MySQLPath -u $Username $passwordArg
    if ($LASTEXITCODE -ne 0) {
        throw "Erro ao executar setup inicial"
    }
    Write-Host "✅ Banco 'sugoi_v2' criado" -ForegroundColor Green
} catch {
    Write-Host "❌ Erro ao criar banco: $_" -ForegroundColor Red
    exit 1
}

# Importar estrutura e dados
Write-Host ""
Write-Host "📥 Importando dados do jogo..." -ForegroundColor Yellow
Write-Host "   Isso pode levar alguns minutos..." -ForegroundColor Gray

try {
    $startTime = Get-Date
    Get-Content $dumpFile | & $MySQLPath -u $Username $passwordArg sugoi_v2
    
    if ($LASTEXITCODE -ne 0) {
        throw "Erro ao importar dump"
    }
    
    $endTime = Get-Date
    $duration = $endTime - $startTime
    
    Write-Host "✅ Dados importados com sucesso!" -ForegroundColor Green
    Write-Host "   Tempo de importação: $($duration.TotalSeconds.ToString("F1"))s" -ForegroundColor Gray
} catch {
    Write-Host "❌ Erro ao importar dados: $_" -ForegroundColor Red
    exit 1
}

# Verificar importação
Write-Host ""
Write-Host "🔍 Verificando importação..." -ForegroundColor Yellow

try {
    $verifyQuery = "SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = 'sugoi_v2';"
    $tableCount = & $MySQLPath -u $Username $passwordArg -e $verifyQuery 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Verificação concluída" -ForegroundColor Green
        Write-Host "   Resultado: $tableCount" -ForegroundColor Gray
    } else {
        Write-Host "⚠️ Aviso: Não foi possível verificar a importação" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠️ Aviso: Erro na verificação: $_" -ForegroundColor Yellow
}

# Resumo final
Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "🎉 CONFIGURAÇÃO CONCLUÍDA COM SUCESSO!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Próximos passos:"
Write-Host "1. Inicie seu servidor web (Apache/Nginx)"
Write-Host "2. Acesse: http://localhost:8080/index-safe.php"
Write-Host "3. Configure o PHP se necessário"
Write-Host ""
Write-Host "Configuração do banco:"
Write-Host "• Host: localhost"
Write-Host "• Banco: sugoi_v2"
Write-Host "• Usuário: $Username"
Write-Host "• Charset: utf8mb4"
Write-Host ""
Write-Host "Arquivos importantes:"
Write-Host "• Configuração: public/Constantes/configs.dev.php"
Write-Host "• Conexão: public/Includes/database/"
Write-Host ""
Write-Host "Para logs e troubleshooting, verifique:"
Write-Host "• MySQL error log"
Write-Host "• PHP error log"
Write-Host "• Browser developer console"
Write-Host ""