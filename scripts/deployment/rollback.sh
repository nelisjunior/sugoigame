#!/bin/bash

# =============================================================================
# 🔄 Script de Rollback - Migração PHP 8.x
# =============================================================================

set -e # Exit on any error

# Configurações
PROJECT_DIR="/var/www/sugoigame"
BACKUP_DIR="/var/backups/sugoigame"
LOG_FILE="/var/log/sugoigame/rollback.log"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função de log
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

# Verificar se está executando como root ou com sudo
check_permissions() {
    if [[ $EUID -ne 0 ]]; then
        error "Este script deve ser executado como root ou com sudo"
    fi
}

# Listar backups disponíveis
list_backups() {
    log "📋 Backups disponíveis:"
    
    if [ ! -d "$BACKUP_DIR" ]; then
        error "Diretório de backup não encontrado: $BACKUP_DIR"
    fi
    
    echo ""
    echo "=== BACKUPS DE BANCO DE DADOS ==="
    ls -lah "$BACKUP_DIR"/*.sql.gz 2>/dev/null | head -10 || echo "Nenhum backup de banco encontrado"
    
    echo ""
    echo "=== BACKUPS DE ARQUIVOS ==="
    ls -lah "$BACKUP_DIR"/*.tar.gz 2>/dev/null | head -10 || echo "Nenhum backup de arquivos encontrado"
    echo ""
}

# Selecionar backup
select_backup() {
    if [ -n "$1" ]; then
        BACKUP_TIMESTAMP="$1"
    else
        echo "Por favor, forneça o timestamp do backup (YYYYMMDD_HHMMSS):"
        read -r BACKUP_TIMESTAMP
    fi
    
    DB_BACKUP_FILE="$BACKUP_DIR/sugoigame_backup_$BACKUP_TIMESTAMP.sql.gz"
    FILES_BACKUP_FILE="$BACKUP_DIR/sugoigame_files_$BACKUP_TIMESTAMP.tar.gz"
    
    if [ ! -f "$DB_BACKUP_FILE" ]; then
        error "Backup de banco não encontrado: $DB_BACKUP_FILE"
    fi
    
    if [ ! -f "$FILES_BACKUP_FILE" ]; then
        error "Backup de arquivos não encontrado: $FILES_BACKUP_FILE"
    fi
    
    log "✅ Backups selecionados:"
    log "   DB: $DB_BACKUP_FILE"
    log "   Files: $FILES_BACKUP_FILE"
}

# Confirmar rollback
confirm_rollback() {
    warning "⚠️  ATENÇÃO: Esta operação irá:"
    warning "   - Restaurar o banco de dados para o estado do backup"
    warning "   - Restaurar todos os arquivos para o estado do backup"
    warning "   - Perder todas as mudanças feitas após o backup"
    echo ""
    
    echo "Tem certeza que deseja continuar? [y/N]"
    read -r confirm
    
    if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
        log "Rollback cancelado pelo usuário"
        exit 0
    fi
}

# Parar serviços
stop_services() {
    log "⏹️  Parando serviços..."
    
    # Parar serviços customizados primeiro
    systemctl stop sugoigame-chat 2>/dev/null || true
    systemctl stop sugoigame-map 2>/dev/null || true
    
    # Parar Nginx
    systemctl stop nginx
    success "Nginx parado ✓"
    
    # Parar PHP-FPM
    systemctl stop php8.1-fpm 2>/dev/null || systemctl stop php8.2-fpm 2>/dev/null || true
    success "PHP-FPM parado ✓"
}

# Criar backup de emergência antes do rollback
emergency_backup() {
    log "🆘 Criando backup de emergência atual..."
    
    EMERGENCY_TIMESTAMP="emergency_$TIMESTAMP"
    EMERGENCY_DB="$BACKUP_DIR/sugoigame_backup_$EMERGENCY_TIMESTAMP.sql.gz"
    EMERGENCY_FILES="$BACKUP_DIR/sugoigame_files_$EMERGENCY_TIMESTAMP.tar.gz"
    
    # Backup do banco
    if mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$EMERGENCY_DB"; then
        success "Backup de emergência do banco criado ✓"
    else
        warning "Falha ao criar backup de emergência do banco"
    fi
    
    # Backup dos arquivos
    if tar -czf "$EMERGENCY_FILES" -C "$(dirname $PROJECT_DIR)" "$(basename $PROJECT_DIR)"; then
        success "Backup de emergência dos arquivos criado ✓"
    else
        warning "Falha ao criar backup de emergência dos arquivos"
    fi
}

# Restaurar banco de dados
restore_database() {
    log "🗄️  Restaurando banco de dados..."
    
    # Descompactar backup
    TEMP_SQL="/tmp/restore_$TIMESTAMP.sql"
    gunzip -c "$DB_BACKUP_FILE" > "$TEMP_SQL"
    
    # Restaurar banco
    if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$TEMP_SQL"; then
        success "Banco de dados restaurado ✓"
    else
        error "Falha ao restaurar banco de dados"
    fi
    
    # Limpar arquivo temporário
    rm -f "$TEMP_SQL"
}

# Restaurar arquivos
restore_files() {
    log "📂 Restaurando arquivos..."
    
    # Fazer backup do diretório atual
    mv "$PROJECT_DIR" "${PROJECT_DIR}_rollback_backup_$TIMESTAMP" 2>/dev/null || true
    
    # Restaurar arquivos do backup
    cd "$(dirname $PROJECT_DIR)"
    if tar -xzf "$FILES_BACKUP_FILE"; then
        success "Arquivos restaurados ✓"
    else
        error "Falha ao restaurar arquivos"
    fi
    
    # Definir permissões corretas
    chown -R www-data:www-data "$PROJECT_DIR"
    chmod -R 755 "$PROJECT_DIR"
    success "Permissões ajustadas ✓"
}

# Instalar PHP 7.4 se necessário
install_php74() {
    log "🔄 Verificando versão do PHP..."
    
    CURRENT_PHP=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    
    if [[ "$CURRENT_PHP" == "7.4" ]]; then
        success "PHP 7.4 já está ativo ✓"
        return
    fi
    
    warning "PHP atual: $CURRENT_PHP - Tentando ativar PHP 7.4..."
    
    # Verificar se PHP 7.4 está instalado
    if command -v php7.4 &> /dev/null; then
        # Parar versão atual
        systemctl stop php8.1-fpm 2>/dev/null || true
        systemctl stop php8.2-fpm 2>/dev/null || true
        
        # Ativar PHP 7.4
        update-alternatives --set php /usr/bin/php7.4
        systemctl start php7.4-fpm
        
        success "PHP 7.4 ativado ✓"
    else
        warning "PHP 7.4 não encontrado - mantendo versão atual"
    fi
}

# Iniciar serviços
start_services() {
    log "▶️  Iniciando serviços..."
    
    # Descobrir qual versão do PHP usar
    if systemctl is-enabled php7.4-fpm 2>/dev/null; then
        systemctl start php7.4-fpm
        success "PHP 7.4 FPM iniciado ✓"
    elif systemctl is-enabled php8.1-fpm 2>/dev/null; then
        systemctl start php8.1-fpm
        success "PHP 8.1 FPM iniciado ✓"
    elif systemctl is-enabled php8.2-fpm 2>/dev/null; then
        systemctl start php8.2-fpm
        success "PHP 8.2 FPM iniciado ✓"
    else
        warning "Nenhum serviço PHP-FPM encontrado"
    fi
    
    # Iniciar Nginx
    systemctl start nginx
    success "Nginx iniciado ✓"
    
    # Iniciar serviços customizados
    systemctl start sugoigame-chat 2>/dev/null && success "Serviço de chat iniciado ✓" || true
    systemctl start sugoigame-map 2>/dev/null && success "Serviço de mapa iniciado ✓" || true
}

# Verificar saúde após rollback
health_check() {
    log "🏥 Verificando saúde do sistema..."
    
    sleep 5
    
    # Verificar serviços
    if systemctl is-active --quiet nginx; then
        success "Nginx está ativo ✓"
    else
        error "Nginx não está ativo"
    fi
    
    # Verificar PHP
    PHP_SERVICE=$(systemctl list-units --type=service --state=active | grep -E "php[0-9\.]+-fpm" | head -1 | awk '{print $1}')
    if [ -n "$PHP_SERVICE" ]; then
        success "PHP-FPM está ativo ($PHP_SERVICE) ✓"
    else
        warning "Nenhum serviço PHP-FPM ativo encontrado"
    fi
    
    # Testar conexão com banco
    cd "$PROJECT_DIR"
    php -r "
        require 'public/Includes/conectdb.php';
        try {
            \$connection = new mywrap_con();
            echo 'Conexão com banco: OK\n';
        } catch (Exception \$e) {
            echo 'Erro na conexão: ' . \$e->getMessage() . '\n';
            exit(1);
        }
    "
    success "Conexão com banco verificada ✓"
    
    # Testar resposta HTTP (se health-check existir)
    if [ -f "$PROJECT_DIR/public/health-check.php" ]; then
        if curl -f -s -o /dev/null "http://localhost/health-check.php"; then
            success "Health check HTTP passou ✓"
        else
            warning "Health check HTTP falhou"
        fi
    fi
}

# Função principal
main() {
    log "🔄 Iniciando rollback da migração PHP 8.x..."
    
    check_permissions
    list_backups
    select_backup "$1"
    confirm_rollback
    stop_services
    emergency_backup
    restore_database
    restore_files
    install_php74
    start_services
    health_check
    
    success "✅ Rollback concluído com sucesso!"
    log "📋 Logs disponíveis em: $LOG_FILE"
    log "🆘 Backup de emergência criado em: $BACKUP_DIR"
    
    warning "⚠️  Lembre-se de:"
    warning "   - Verificar se todas as funcionalidades estão funcionando"
    warning "   - Monitorar logs por possíveis problemas"
    warning "   - Investigar a causa do problema que causou o rollback"
}

# Verificar argumentos
if [ "$1" == "--list" ]; then
    list_backups
    exit 0
fi

if [ "$1" == "--help" ] || [ "$1" == "-h" ]; then
    echo "Uso: $0 [TIMESTAMP] [--list] [--help]"
    echo ""
    echo "Argumentos:"
    echo "  TIMESTAMP    Timestamp do backup (YYYYMMDD_HHMMSS)"
    echo ""
    echo "Opções:"
    echo "  --list       Lista os backups disponíveis"
    echo "  --help       Mostra esta ajuda"
    echo ""
    echo "Exemplo:"
    echo "  $0 20231201_143022"
    exit 0
fi

# Executar rollback
main "$1"