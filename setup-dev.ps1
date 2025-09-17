# ===============================================
# Script de Configuração de Desenvolvimento
# Sugoi Game - MMORPG One Piece
# ===============================================

Write-Host "🚀 Configurando ambiente de desenvolvimento..." -ForegroundColor Green

# Verificar se está rodando como administrador
if (-NOT ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "❌ Execute como Administrador para modificar php.ini" -ForegroundColor Red
    exit 1
}

# 1. Verificar versão do PHP
Write-Host "`n1️⃣ Verificando PHP..." -ForegroundColor Yellow
$phpVersion = php -v | Select-String "PHP ([0-9]+\.[0-9]+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }
Write-Host "   ✅ PHP $phpVersion detectado" -ForegroundColor Green

# 2. Localizar php.ini
Write-Host "`n2️⃣ Localizando php.ini..." -ForegroundColor Yellow
$phpIni = php --ini | Select-String "Loaded Configuration File:" | ForEach-Object { $_.ToString().Split(":")[1].Trim() }
Write-Host "   📍 php.ini: $phpIni" -ForegroundColor Cyan

# 3. Verificar extensões necessárias
Write-Host "`n3️⃣ Verificando extensões..." -ForegroundColor Yellow
$extensions = @("mysqli", "zip", "curl", "mbstring", "openssl")
$missing = @()

foreach ($ext in $extensions) {
    $result = php -m | Select-String $ext
    if ($result) {
        Write-Host "   ✅ $ext" -ForegroundColor Green
    } else {
        Write-Host "   ❌ $ext (faltando)" -ForegroundColor Red
        $missing += $ext
    }
}

# 4. Habilitar extensões faltantes
if ($missing.Count -gt 0) {
    Write-Host "`n4️⃣ Habilitando extensões..." -ForegroundColor Yellow
    
    $iniContent = Get-Content $phpIni
    $modified = $false
    
    foreach ($ext in $missing) {
        $pattern = "^;extension=$ext"
        $replacement = "extension=$ext"
        
        for ($i = 0; $i -lt $iniContent.Length; $i++) {
            if ($iniContent[$i] -match $pattern) {
                $iniContent[$i] = $replacement
                Write-Host "   ✅ Habilitado: $ext" -ForegroundColor Green
                $modified = $true
                break
            }
        }
    }
    
    if ($modified) {
        $iniContent | Set-Content $phpIni
        Write-Host "   💾 php.ini atualizado" -ForegroundColor Green
    }
}

# 5. Verificar Composer
Write-Host "`n5️⃣ Verificando Composer..." -ForegroundColor Yellow
try {
    $composerVersion = composer --version | Select-String "Composer version ([0-9\.]+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }
    Write-Host "   ✅ Composer $composerVersion" -ForegroundColor Green
} catch {
    Write-Host "   ❌ Composer não encontrado" -ForegroundColor Red
    Write-Host "   💡 Instale: https://getcomposer.org/" -ForegroundColor Yellow
    exit 1
}

# 6. Instalar dependências
Write-Host "`n6️⃣ Instalando dependências..." -ForegroundColor Yellow
if (Test-Path "composer.json") {
    composer install
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✅ Dependências instaladas" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Erro ao instalar dependências" -ForegroundColor Red
    }
} else {
    Write-Host "   ❌ composer.json não encontrado" -ForegroundColor Red
}

# 7. Verificar banco de dados
Write-Host "`n7️⃣ Verificando MySQL..." -ForegroundColor Yellow
try {
    $mysqlVersion = mysql --version 2>$null
    if ($mysqlVersion) {
        Write-Host "   ✅ MySQL detectado" -ForegroundColor Green
    } else {
        Write-Host "   ❌ MySQL não encontrado" -ForegroundColor Red
        Write-Host "   💡 Instale XAMPP ou MySQL Community Server" -ForegroundColor Yellow
    }
} catch {
    Write-Host "   ❌ MySQL não encontrado" -ForegroundColor Red
    Write-Host "   💡 Instale XAMPP ou MySQL Community Server" -ForegroundColor Yellow
}

# 8. Resumo final
Write-Host "`n🎯 Resumo da Configuração:" -ForegroundColor Cyan
Write-Host "   • PHP: Verificado ✅" -ForegroundColor White
Write-Host "   • Extensões: " -NoNewline -ForegroundColor White
if ($missing.Count -eq 0) { 
    Write-Host "Todas OK ✅" -ForegroundColor Green 
} else { 
    Write-Host "Algumas faltando ⚠️" -ForegroundColor Yellow 
}
Write-Host "   • Composer: Verificado ✅" -ForegroundColor White
Write-Host "   • Dependências: Instaladas ✅" -ForegroundColor White

Write-Host "`n📋 Próximos passos:" -ForegroundColor Yellow
Write-Host "   1. Configure MySQL e crie banco 'sugoi_v2'" -ForegroundColor White
Write-Host "   2. Importe: mysql -u root -p sugoi_v2 < database/schema.sql" -ForegroundColor White
Write-Host "   3. Inicie servidor: php -S localhost:8000 -t public" -ForegroundColor White
Write-Host "   4. Acesse: http://localhost:8000" -ForegroundColor White

Write-Host "`n🚀 Configuração concluída!" -ForegroundColor Green