# 📚 README - Migração PHP 8.x

## 🎯 Objetivo

Este diretório contém toda a documentação e arquivos necessários para a migração do Sugoi Game de PHP 7.4 para PHP 8.x.

## 📁 Estrutura dos Arquivos

### Documentação Principal
- **`php-modernization-plan.md`** - Plano estratégico completo da migração
- **`php-migration-guide.md`** - Guia prático passo-a-passo
- **`current-state-analysis.md`** - Análise detalhada do estado atual

### Scripts de Automação
- **`scripts/deployment/deploy.sh`** - Script de deploy para produção (Linux)
- **`scripts/deployment/rollback.sh`** - Script de rollback em caso de problemas
- **`scripts/deployment/backup-db.ps1`** - Script de backup para Windows

### Configurações
- **`composer.json`** - Dependências PHP atualizadas
- **`phpunit.xml`** - Configuração de testes
- **`phpstan.neon`** - Análise estática de código
- **`.php-cs-fixer.php`** - Padrões de código

## 🚀 Quick Start

### 1. Preparação do Ambiente
```bash
# Criar backup do banco
./scripts/deployment/backup-db.ps1

# Instalar dependências
composer install

# Executar análise de código
composer analyse
```

### 2. Testes
```bash
# Executar testes unitários
composer test

# Análise de cobertura
composer test:coverage
```

### 3. Deploy
```bash
# Verificar pré-requisitos
./scripts/deployment/deploy.sh --dry-run

# Deploy completo
sudo ./scripts/deployment/deploy.sh
```

## 📋 Checklist de Migração

### Fase 1: Preparação ✅
- [x] Branch `feature/php8-migration` criada
- [x] Estrutura de testes configurada
- [x] Scripts de deploy preparados
- [x] Documentação completa

### Fase 2: Dependências
- [ ] Stripe PHP 12.x
- [ ] PHPMailer 6.8+
- [ ] PagSeguro API REST
- [ ] BladeOne 4.x

### Fase 3: Código
- [ ] Type hints adicionados
- [ ] Strict types implementados
- [ ] Deprecated code removido
- [ ] Error handling modernizado

### Fase 4: Testes
- [ ] Testes unitários
- [ ] Testes de integração
- [ ] Testes funcionais
- [ ] Performance benchmarks

### Fase 5: Deploy
- [ ] Ambiente de teste
- [ ] Validação completa
- [ ] Deploy em produção
- [ ] Monitoramento pós-deploy

## 🔧 Ferramentas de Desenvolvimento

### Análise de Código
```bash
# PHPStan - Análise estática
./vendor/bin/phpstan analyse

# PHP CS Fixer - Padrões de código
./vendor/bin/php-cs-fixer fix

# PHPUnit - Testes
./vendor/bin/phpunit
```

### Comandos Úteis
```bash
# Verificar sintaxe PHP 8.x
find . -name "*.php" -exec php -l {} \;

# Encontrar deprecated functions
grep -r "mysql_\|ereg\|split(" public/

# Verificar extensões necessárias
php -m | grep -E "(mysqli|curl|json|mbstring)"
```

## 🚨 Troubleshooting

### Problemas Comuns

#### 1. Erro "Call to undefined function"
```bash
# Verificar extensões
php -m

# Reinstalar extensões se necessário
sudo apt install php8.2-mysqli php8.2-curl php8.2-mbstring
```

#### 2. Problemas de Performance
```bash
# Ativar OPcache
echo "opcache.enable=1" >> /etc/php/8.2/fpm/php.ini

# Verificar configurações
php --ini
```

#### 3. Erros de Stripe/PagSeguro
- Verificar credenciais de API
- Validar URLs de webhook
- Consultar documentação atualizada

### Logs Importantes
```bash
# Logs PHP
tail -f /var/log/php8.2-fpm.log

# Logs da aplicação
tail -f /var/log/sugoigame/error.log

# Logs do Nginx
tail -f /var/log/nginx/error.log
```

## 📊 Monitoramento

### Health Check
Acesse: `http://localhost/health-check.php`

### Métricas a Acompanhar
- Tempo de resposta
- Uso de memória
- Erros PHP
- Status dos serviços

## 🔄 Rollback

Em caso de problemas:
```bash
# Listar backups disponíveis
./scripts/deployment/rollback.sh --list

# Executar rollback
sudo ./scripts/deployment/rollback.sh YYYYMMDD_HHMMSS
```

## 📞 Contatos e Suporte

### Recursos Úteis
- **PHP 8.2 Documentation**: https://www.php.net/manual/en/
- **Stripe PHP Library**: https://github.com/stripe/stripe-php
- **PHPUnit Documentation**: https://phpunit.de/documentation.html
- **PHPStan**: https://phpstan.org/

### Contribuição
1. Fazer fork do projeto
2. Criar branch para sua feature
3. Implementar mudanças
4. Executar testes
5. Abrir Pull Request

---

**Última atualização**: 16 de setembro de 2025  
**Versão**: 1.0  
**Status**: Preparação concluída - Pronto para Fase 2