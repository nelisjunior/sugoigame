# Script de Deploy para Windows - Migração PHP 8.x
param(
    [switch]$DryRun = $false,
    [switch]$Force = $false
)

$ErrorActionPreference = "Stop"

# Configurações
$ProjectPath = Get-Location
$BackupPath = "D:\backups\sugoigame"
$Timestamp = Get-Date -Format "yyyyMMdd_HHmmss"

Write-Host "=== Deploy da Migração PHP 8.x ===" -ForegroundColor Cyan
Write-Host "Projeto: $ProjectPath" -ForegroundColor Gray
Write-Host "Timestamp: $Timestamp" -ForegroundColor Gray
Write-Host ""

if ($DryRun) {
    Write-Host "🔍 MODO DRY-RUN ATIVO - Nenhuma alteração será feita" -ForegroundColor Yellow
    Write-Host ""
}

# Função para log
function Write-Log {
    param($Message, $Color = "White")
    $TimeStamp = Get-Date -Format "HH:mm:ss"
    Write-Host "[$TimeStamp] $Message" -ForegroundColor $Color
}

# 1. Verificar pré-requisitos
Write-Log "🔍 Verificando pré-requisitos..." "Blue"

# Verificar se estamos na branch correta
try {
    $currentBranch = git branch --show-current
    Write-Log "Branch atual: $currentBranch" "Gray"
    
    if ($currentBranch -ne "feature/php8-migration" -and !$Force) {
        Write-Log "❌ Não está na branch feature/php8-migration" "Red"
        Write-Log "Use -Force para ignorar ou mude para a branch correta" "Yellow"
        exit 1
    }
} catch {
    Write-Log "⚠️  Não foi possível verificar a branch Git" "Yellow"
}

# Verificar arquivos críticos
$criticalFiles = @(
    "composer.json",
    "public/Classes/Modern/StripeService.php",
    "public/Classes/Modern/EmailService.php",
    "public/Classes/Modern/PagSeguroService.php",
    "public/Classes/ModernServicesBootstrap.php"
)

foreach ($file in $criticalFiles) {
    if (Test-Path $file) {
        Write-Log "✅ $file" "Green"
    } else {
        Write-Log "❌ $file não encontrado" "Red"
        exit 1
    }
}

Write-Log "✅ Todos os arquivos críticos estão presentes" "Green"
Write-Log ""

# 2. Validar sintaxe PHP
Write-Log "🧪 Validando sintaxe PHP..." "Blue"

if (!$DryRun) {
    $result = php validate_migration.php
    if ($LASTEXITCODE -eq 0) {
        Write-Log "✅ Validação de sintaxe aprovada" "Green"
    } else {
        Write-Log "❌ Falha na validação de sintaxe" "Red"
        exit 1
    }
}

Write-Log ""

# 3. Criar backup (se não for dry-run)
if (!$DryRun) {
    Write-Log "📦 Criando backup..." "Blue"
    
    # Criar diretório de backup
    if (!(Test-Path $BackupPath)) {
        New-Item -ItemType Directory -Path $BackupPath -Force | Out-Null
        Write-Log "📁 Diretório de backup criado: $BackupPath" "Green"
    }
    
    # Backup dos arquivos
    $backupDir = "$BackupPath\files_$Timestamp"
    $filesToBackup = @(
        "public/Classes/PHPMailer.php",
        "public/Scripts/Vip/adquirir_stripe.php",
        "public/Scripts/Vip/adquirirPS.php",
        "public/Scripts/Geral/cadastro.php"
    )
    
    New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
    
    foreach ($file in $filesToBackup) {
        if (Test-Path $file) {
            $destDir = Join-Path $backupDir (Split-Path $file)
            if (!(Test-Path $destDir)) {
                New-Item -ItemType Directory -Path $destDir -Force | Out-Null
            }
            Copy-Item $file (Join-Path $backupDir $file) -Force
            Write-Log "📁 Backup: $file" "Gray"
        }
    }
    
    Write-Log "✅ Backup de arquivos criado em: $backupDir" "Green"
    Write-Log ""
}

# 4. Ativar modernização gradual
Write-Log "🔄 Ativando modernização gradual..." "Blue"

if (!$DryRun) {
    # Adicionar include do bootstrap nos arquivos principais
    $mainInclude = "public/Includes/conectdb.php"
    
    if (Test-Path $mainInclude) {
        $content = Get-Content $mainInclude -Raw
        $bootstrapInclude = "require_once __DIR__ . '/../Classes/ModernServicesBootstrap.php';"
        
        if ($content -notmatch [regex]::Escape($bootstrapInclude)) {
            $content = $content + "`n`n// Modern Services Bootstrap`n" + $bootstrapInclude + "`n"
            Set-Content $mainInclude $content -Encoding UTF8
            Write-Log "✅ Bootstrap adicionado ao conectdb.php" "Green"
        } else {
            Write-Log "ℹ️  Bootstrap já estava incluído" "Cyan"
        }
    }
}

Write-Log ""

# 5. Configurar redirecionamentos graduais
Write-Log "🔀 Configurando redirecionamentos graduais..." "Blue"

if (!$DryRun) {
    # Criar arquivo de configuração de feature flags
    $featureFlagsContent = @"
<?php
// Feature Flags para migração gradual
return [
    'use_modern_stripe' => false,      // Ativar quando Stripe estiver configurado
    'use_modern_email' => false,       // Ativar quando SMTP estiver configurado  
    'use_modern_pagseguro' => false,   // Ativar quando PagSeguro estiver configurado
    'use_modern_db' => false,          // Ativar após testes
    'use_modern_protector' => false,   // Ativar após testes
    'debug_modern_services' => true    // Log detalhado para debug
];
"@
    
    Set-Content "public/config/feature_flags.php" $featureFlagsContent -Encoding UTF8
    Write-Log "✅ Feature flags criados" "Green"
}

Write-Log ""

# 6. Instruções finais
Write-Log "📋 Próximos passos para ativação completa:" "Yellow"
Write-Log ""
Write-Log "1. Configurar PHP 8.x em produção:" "White"
Write-Log "   - Habilitar extensões: openssl, curl, mbstring, mysqli" "Gray"
Write-Log "   - Instalar Composer" "Gray"
Write-Log "   - Executar: composer install --optimize-autoloader --no-dev" "Gray"
Write-Log ""
Write-Log "2. Configurar variáveis de ambiente:" "White"
Write-Log "   - STRIPE_SECRET_KEY" "Gray"
Write-Log "   - SMTP_HOST, SMTP_USER, SMTP_PASS" "Gray"
Write-Log "   - PAGSEGURO_EMAIL, PAGSEGURO_TOKEN" "Gray"
Write-Log ""
Write-Log "3. Ativar serviços gradualmente:" "White"
Write-Log "   - Editar public/config/feature_flags.php" "Gray"
Write-Log "   - Ativar um serviço por vez" "Gray"
Write-Log "   - Monitorar logs em public/logs/" "Gray"
Write-Log ""
Write-Log "4. Rollback disponível em:" "White"
Write-Log "   - Scripts: scripts/deployment/rollback.ps1" "Gray"
Write-Log "   - Backup: $BackupPath" "Gray"
Write-Log ""

if ($DryRun) {
    Write-Log "🏁 DRY-RUN concluído com sucesso!" "Green"
} else {
    Write-Log "🚀 Deploy concluído com sucesso!" "Green"
    Write-Log "📊 Migração está 90% completa e pronta para ativação gradual!" "Cyan"
}

Write-Log ""
Write-Log "=== Deploy Finalizado ===" "Cyan"