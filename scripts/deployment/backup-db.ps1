# 📋 Backup Script - Banco de Dados Sugoi Game
# Para Windows PowerShell

param(
    [string]$BackupPath = "D:\backups\sugoigame",
    [string]$DBHost = "localhost",
    [string]$DBUser = "root",
    [string]$DBName = "sugoigame",
    [switch]$Compress = $true,
    [switch]$AutoCleanup = $true
)

# Configurações
$Timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$BackupFile = "$BackupPath\sugoigame_backup_$Timestamp.sql"
$LogFile = "$BackupPath\backup.log"

# Função de log
function Write-Log {
    param($Message, $Level = "INFO")
    $LogMessage = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') [$Level] $Message"
    Write-Host $LogMessage
    Add-Content -Path $LogFile -Value $LogMessage
}

# Criar diretório de backup se não existir
if (!(Test-Path $BackupPath)) {
    New-Item -ItemType Directory -Path $BackupPath -Force | Out-Null
    Write-Log "Diretório de backup criado: $BackupPath"
}

try {
    Write-Log "🚀 Iniciando backup do banco de dados..."
    
    # Verificar se mysqldump está disponível
    if (!(Get-Command mysqldump -ErrorAction SilentlyContinue)) {
        throw "mysqldump não encontrado. Verifique se o MySQL está instalado e no PATH."
    }
    
    # Solicitar senha do banco
    $SecurePassword = Read-Host "Digite a senha do banco de dados" -AsSecureString
    $BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($SecurePassword)
    $Password = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)
    
    # Executar backup
    Write-Log "Criando backup: $BackupFile"
    
    $mysqldumpArgs = @(
        "--host=$DBHost",
        "--user=$DBUser",
        "--password=$Password",
        "--single-transaction",
        "--routines",
        "--triggers",
        "--result-file=$BackupFile",
        $DBName
    )
    
    & mysqldump @mysqldumpArgs
    
    if ($LASTEXITCODE -eq 0) {
        Write-Log "✅ Backup criado com sucesso: $BackupFile" "SUCCESS"
        
        # Verificar tamanho do arquivo
        $FileSize = (Get-Item $BackupFile).Length
        $FileSizeMB = [math]::Round($FileSize / 1MB, 2)
        Write-Log "Tamanho do backup: $FileSizeMB MB"
        
        # Compactar se solicitado
        if ($Compress) {
            Write-Log "Compactando backup..."
            $CompressedFile = "$BackupFile.gz"
            
            # Usar 7-Zip se disponível, senão usar .NET
            if (Get-Command 7z -ErrorAction SilentlyContinue) {
                & 7z a -tgzip "$CompressedFile" "$BackupFile"
                Remove-Item $BackupFile
                Write-Log "✅ Backup compactado: $CompressedFile" "SUCCESS"
            } else {
                # Compactar usando .NET (mais lento)
                $FileStream = [System.IO.File]::OpenRead($BackupFile)
                $CompressedStream = [System.IO.File]::Create($CompressedFile)
                $GzipStream = New-Object System.IO.Compression.GzipStream($CompressedStream, [System.IO.Compression.CompressionMode]::Compress)
                
                $FileStream.CopyTo($GzipStream)
                
                $GzipStream.Close()
                $CompressedStream.Close()
                $FileStream.Close()
                
                Remove-Item $BackupFile
                Write-Log "✅ Backup compactado: $CompressedFile" "SUCCESS"
            }
        }
        
        # Limpeza automática
        if ($AutoCleanup) {
            Write-Log "Executando limpeza automática (mantendo últimos 7 dias)..."
            $CutoffDate = (Get-Date).AddDays(-7)
            $OldBackups = Get-ChildItem -Path $BackupPath -Filter "sugoigame_backup_*.sql*" | Where-Object { $_.CreationTime -lt $CutoffDate }
            
            foreach ($OldBackup in $OldBackups) {
                Remove-Item $OldBackup.FullName
                Write-Log "Backup antigo removido: $($OldBackup.Name)"
            }
        }
        
    } else {
        throw "Erro no mysqldump. Código de saída: $LASTEXITCODE"
    }
    
} catch {
    Write-Log "❌ Erro durante o backup: $($_.Exception.Message)" "ERROR"
    exit 1
} finally {
    # Limpar senha da memória
    if ($Password) {
        $Password = $null
    }
}

Write-Log "🎉 Backup concluído com sucesso!"