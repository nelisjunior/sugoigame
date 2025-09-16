# Deploy simples da migracao PHP 8.x
Write-Host "=== Deploy da Migracao PHP 8.x ===" -ForegroundColor Cyan

# 1. Validar migracao
Write-Host "1. Validando migracao..." -ForegroundColor Blue
php validate_migration.php

# 2. Criar feature flags
Write-Host "2. Criando configuracao..." -ForegroundColor Blue

if (!(Test-Path "public/config")) {
    New-Item -ItemType Directory -Path "public/config" -Force | Out-Null
}

$flags = @'
<?php
return [
    'use_modern_stripe' => false,
    'use_modern_email' => false, 
    'use_modern_pagseguro' => false,
    'debug_mode' => true
];
'@

Set-Content "public/config/feature_flags.php" $flags -Encoding UTF8

Write-Host "3. Adicionando bootstrap..." -ForegroundColor Blue

$include = "public/Includes/conectdb.php"
if (Test-Path $include) {
    $content = Get-Content $include -Raw
    $bootstrap = "require_once __DIR__ . '/../Classes/ModernServicesBootstrap.php';"
    
    if ($content -notlike "*ModernServicesBootstrap*") {
        Add-Content $include "`n// Modern Services`n$bootstrap"
        Write-Host "Bootstrap adicionado" -ForegroundColor Green
    }
}

Write-Host "`n=== Deploy Concluido ===" -ForegroundColor Green
Write-Host "Proximos passos:" -ForegroundColor Yellow
Write-Host "1. Instalar PHP 8.x em producao" 
Write-Host "2. Executar: composer install"
Write-Host "3. Ativar servicos em feature_flags.php"
Write-Host "4. Monitorar logs"