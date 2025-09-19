# 📦 Script de Backup Simplificado - Sugoi Game
# Configurado para o ambiente atual

param(
    [string]$BackupPath = "D:\backups\sugoigame",
    [switch]$AutoCleanup = $true
)

# Configurações do banco (baseado em configs.dev.php)
$DBHost = "localhost"
$DBUser = "root"
$DBName = "sugoi_v2"
# Senha em branco conforme configuração

$Timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$BackupFile = "$BackupPath\sugoi_v2_backup_$Timestamp.sql"

# Criar diretório se não existir
if (!(Test-Path $BackupPath)) {
    New-Item -ItemType Directory -Path $BackupPath -Force | Out-Null
    Write-Host "✅ Diretório de backup criado: $BackupPath" -ForegroundColor Green
}

try {
    Write-Host "🚀 Iniciando backup do banco 'sugoi_v2'..." -ForegroundColor Blue
    
    # Verificar se mysqldump está disponível
    if (!(Get-Command mysqldump -ErrorAction SilentlyContinue)) {
        Write-Host "❌ mysqldump não encontrado no PATH" -ForegroundColor Red
        Write-Host "💡 Instale o MySQL ou adicione o diretório bin do MySQL ao PATH" -ForegroundColor Yellow
        Write-Host "   Exemplo: C:\Program Files\MySQL\MySQL Server 8.0\bin" -ForegroundColor Yellow
        exit 1
    }
    
    # Executar backup (sem senha conforme configuração)
    $mysqldumpArgs = @(
        "--host=$DBHost",
        "--user=$DBUser",
        "--single-transaction",
        "--routines",
        "--triggers",
        "--result-file=$BackupFile",
        $DBName
    )
    
    Write-Host "📄 Criando backup: $BackupFile" -ForegroundColor Cyan
    
    & mysqldump @mysqldumpArgs
    
    if ($LASTEXITCODE -eq 0) {
        $FileSize = (Get-Item $BackupFile).Length
        $FileSizeMB = [math]::Round($FileSize / 1MB, 2)
        Write-Host "✅ Backup criado com sucesso!" -ForegroundColor Green
        Write-Host "📊 Tamanho: $FileSizeMB MB" -ForegroundColor Cyan
        Write-Host "📁 Local: $BackupFile" -ForegroundColor Cyan
        
        # Compactar com 7-Zip se disponível
        if (Get-Command 7z -ErrorAction SilentlyContinue) {
            Write-Host "🗜️ Compactando backup..." -ForegroundColor Yellow
            & 7z a -tgzip "$BackupFile.gz" "$BackupFile"
            if ($LASTEXITCODE -eq 0) {
                Remove-Item $BackupFile
                Write-Host "✅ Backup compactado: $BackupFile.gz" -ForegroundColor Green
            }
        }
        
        # Limpeza automática (manter últimos 5 backups)
        if ($AutoCleanup) {
            $OldBackups = Get-ChildItem -Path $BackupPath -Filter "sugoi_v2_backup_*.sql*" | 
                         Sort-Object CreationTime -Descending | 
                         Select-Object -Skip 5
            
            if ($OldBackups) {
                Write-Host "🧹 Removendo backups antigos..." -ForegroundColor Yellow
                $OldBackups | ForEach-Object { 
                    Remove-Item $_.FullName
                    Write-Host "   Removido: $($_.Name)" -ForegroundColor Gray
                }
            }
        }
        
    } else {
        throw "Erro no mysqldump. Código: $LASTEXITCODE"
    }
    
} catch {
    Write-Host "❌ Erro durante o backup: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host "🎉 Backup concluído com sucesso!" -ForegroundColor Green
Write-Host "📋 Próximo passo: composer install (após instalar PHP 8.x)" -ForegroundColor Cyan