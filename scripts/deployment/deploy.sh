#!/bin/bash

# =============================================================================
# 🚀 Script de Deploy - Migração PHP 8.x
# =============================================================================

set -e # Exit on any error

# Configurações
PROJECT_DIR="/var/www/sugoigame"
BACKUP_DIR="/var/backups/sugoigame"
LOG_FILE="/var/log/sugoigame/deploy.log"
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

# Verificar pré-requisitos
check_prerequisites() {
    log "🔍 Verificando pré-requisitos..."
    
    # Verificar PHP 8.x
    if ! command -v php &> /dev/null; then
        error "PHP não encontrado. Instale PHP 8.1+ antes de continuar."
    fi
    
    PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    if [[ $(echo "$PHP_VERSION < 8.1" | bc -l) -eq 1 ]]; then
        error "PHP $PHP_VERSION encontrado. Requer PHP 8.1+"
    fi
    
    success "PHP $PHP_VERSION ✓"
    
    # Verificar MySQL
    if ! command -v mysql &> /dev/null; then
        error "MySQL não encontrado"
    fi
    
    # Verificar Composer
    if ! command -v composer &> /dev/null; then
        error "Composer não encontrado"
    fi
    
    # Verificar Git
    if ! command -v git &> /dev/null; then
        error "Git não encontrado"
    fi
    
    success "Todos os pré-requisitos atendidos ✓"
}

# Criar backup do banco de dados
backup_database() {
    log "📦 Criando backup do banco de dados..."
    
    mkdir -p "$BACKUP_DIR"
    
    BACKUP_FILE="$BACKUP_DIR/sugoigame_backup_$TIMESTAMP.sql"
    
    if mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE"; then
        success "Backup criado: $BACKUP_FILE"
    else
        error "Falha ao criar backup do banco de dados"
    fi
    
    # Compactar backup
    gzip "$BACKUP_FILE"
    success "Backup compactado: $BACKUP_FILE.gz"
}

# Backup dos arquivos
backup_files() {
    log "📂 Criando backup dos arquivos..."
    
    BACKUP_FILES="$BACKUP_DIR/sugoigame_files_$TIMESTAMP.tar.gz"
    
    if tar -czf "$BACKUP_FILES" -C "$(dirname $PROJECT_DIR)" "$(basename $PROJECT_DIR)"; then
        success "Backup de arquivos criado: $BACKUP_FILES"
    else
        error "Falha ao criar backup dos arquivos"
    fi
}

# Atualizar código
update_code() {
    log "📥 Atualizando código..."
    
    cd "$PROJECT_DIR"
    
    # Fazer stash de mudanças locais se houver
    if ! git diff-index --quiet HEAD --; then
        warning "Salvando mudanças locais..."
        git stash push -m "Deploy stash $TIMESTAMP"
    fi
    
    # Atualizar código
    git fetch origin
    git checkout feature/php8-migration
    git pull origin feature/php8-migration
    
    success "Código atualizado ✓"
}

# Instalar dependências
install_dependencies() {
    log "📚 Instalando dependências..."
    
    cd "$PROJECT_DIR"
    
    # Instalar dependências PHP
    if composer install --no-dev --optimize-autoloader --no-interaction; then
        success "Dependências PHP instaladas ✓"
    else
        error "Falha ao instalar dependências PHP"
    fi
    
    # Instalar dependências Node.js (se necessário)
    if [ -f "servers/chat/package.json" ]; then
        cd servers/chat
        if npm ci --production; then
            success "Dependências Node.js instaladas ✓"
        else
            warning "Falha ao instalar dependências Node.js"
        fi
        cd "$PROJECT_DIR"
    fi
}

# Verificar configuração
verify_config() {
    log "🔧 Verificando configuração..."
    
    cd "$PROJECT_DIR"
    
    # Verificar extensões PHP
    php -m | grep -E "(mysqli|curl|json|mbstring)" > /dev/null
    if [ $? -eq 0 ]; then
        success "Extensões PHP necessárias estão instaladas ✓"
    else
        error "Extensões PHP necessárias não encontradas"
    fi
    
    # Verificar arquivos de configuração
    if [ ! -f "public/Includes/conectdb.php" ]; then
        warning "Arquivo de configuração do banco não encontrado"
    fi
    
    # Testar conexão com banco
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
    
    success "Configuração verificada ✓"
}

# Executar testes
run_tests() {
    log "🧪 Executando testes..."
    
    cd "$PROJECT_DIR"
    
    # Executar testes unitários
    if [ -f "vendor/bin/phpunit" ]; then
        if ./vendor/bin/phpunit --testsuite=Unit; then
            success "Testes unitários passaram ✓"
        else
            error "Testes unitários falharam"
        fi
    else
        warning "PHPUnit não encontrado, pulando testes"
    fi
}

# Reiniciar serviços
restart_services() {
    log "🔄 Reiniciando serviços..."
    
    # Reiniciar PHP-FPM
    if systemctl restart php8.1-fpm 2>/dev/null || systemctl restart php8.2-fpm 2>/dev/null; then
        success "PHP-FPM reiniciado ✓"
    else
        warning "Falha ao reiniciar PHP-FPM"
    fi
    
    # Reiniciar Nginx
    if systemctl restart nginx; then
        success "Nginx reiniciado ✓"
    else
        warning "Falha ao reiniciar Nginx"
    fi
    
    # Reiniciar serviços customizados
    if [ -f "/etc/systemd/system/sugoigame-chat.service" ]; then
        systemctl restart sugoigame-chat
        success "Serviço de chat reiniciado ✓"
    fi
    
    if [ -f "/etc/systemd/system/sugoigame-map.service" ]; then
        systemctl restart sugoigame-map
        success "Serviço de mapa reiniciado ✓"
    fi
}

# Verificar saúde do sistema
health_check() {
    log "🏥 Verificando saúde do sistema..."
    
    # Verificar se os serviços estão rodando
    sleep 5
    
    if systemctl is-active --quiet nginx; then
        success "Nginx está ativo ✓"
    else
        error "Nginx não está ativo"
    fi
    
    if systemctl is-active --quiet php8.1-fpm || systemctl is-active --quiet php8.2-fpm; then
        success "PHP-FPM está ativo ✓"
    else
        error "PHP-FPM não está ativo"
    fi
    
    # Testar resposta HTTP
    if curl -f -s -o /dev/null "http://localhost/health-check.php"; then
        success "Health check HTTP passou ✓"
    else
        warning "Health check HTTP falhou"
    fi
}

# Limpeza
cleanup() {
    log "🧹 Executando limpeza..."
    
    cd "$PROJECT_DIR"
    
    # Limpar cache do Composer
    composer clear-cache
    
    # Limpar logs antigos (manter últimos 30 dias)
    find /var/log/sugoigame -name "*.log" -mtime +30 -delete 2>/dev/null || true
    
    # Limpar backups antigos (manter últimos 7 dias)
    find "$BACKUP_DIR" -name "*.gz" -mtime +7 -delete 2>/dev/null || true
    
    success "Limpeza concluída ✓"
}

# Função principal
main() {
    log "🚀 Iniciando deploy da migração PHP 8.x..."
    
    check_permissions
    check_prerequisites
    backup_database
    backup_files
    update_code
    install_dependencies
    verify_config
    run_tests
    restart_services
    health_check
    cleanup
    
    success "🎉 Deploy concluído com sucesso!"
    log "📋 Logs disponíveis em: $LOG_FILE"
    log "💾 Backups disponíveis em: $BACKUP_DIR"
}

# Verificar argumentos
if [ "$1" == "--dry-run" ]; then
    log "🔍 Modo dry-run - apenas verificando pré-requisitos..."
    check_prerequisites
    exit 0
fi

if [ "$1" == "--help" ] || [ "$1" == "-h" ]; then
    echo "Uso: $0 [--dry-run] [--help]"
    echo ""
    echo "Opções:"
    echo "  --dry-run    Executa apenas verificações, sem fazer deploy"
    echo "  --help       Mostra esta ajuda"
    exit 0
fi

# Executar deploy
main