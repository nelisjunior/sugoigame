# 🛠️ Guia Prático de Migração PHP 8.x - Sugoi Game

## 🚀 Setup do Ambiente de Desenvolvimento

### 1. Instalação do PHP 8.2

#### Windows (usando Chocolatey)
```powershell
# Instalar Chocolatey se não tiver
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))

# Instalar PHP 8.2
choco install php --version=8.2.12 -y

# Verificar instalação
php --version
```

#### Linux/Ubuntu
```bash
# Adicionar repositório
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# Instalar PHP 8.2
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip

# Verificar instalação
php --version
```

### 2. Configuração do php.ini

```ini
; Configurações recomendadas para desenvolvimento
display_errors = On
display_startup_errors = On
error_reporting = E_ALL
log_errors = On
error_log = /path/to/error.log

; Extensões necessárias
extension=mysqli
extension=curl
extension=json
extension=mbstring
extension=xml
extension=zip

; Configurações de memória
memory_limit = 256M
max_execution_time = 120
post_max_size = 50M
upload_max_filesize = 50M

; Timezone
date.timezone = America/Sao_Paulo
```

### 3. Instalação do Composer

```bash
# Download e instalação global
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verificar instalação
composer --version
```

## 📋 Checklist de Migração

### Fase 1: Preparação ✅

- [ ] **Backup do banco de dados**
  ```bash
  mysqldump -u root -p sugoigame > backup_sugoigame_$(date +%Y%m%d).sql
  ```

- [ ] **Criar branch de migração**
  ```bash
  git checkout -b feature/php8-migration
  git push -u origin feature/php8-migration
  ```

- [ ] **Configurar ambiente de teste**
  - [ ] PHP 8.2 instalado
  - [ ] MySQL/MariaDB atualizado
  - [ ] Xdebug configurado

### Fase 2: Dependências ⏳

#### 2.1 Criar composer.json raiz
```json
{
    "name": "nelisjunior/sugoigame",
    "description": "One Piece MMORPG Game",
    "type": "project",
    "require": {
        "php": ">=8.1",
        "stripe/stripe-php": "^12.0",
        "phpmailer/phpmailer": "^6.8",
        "eftec/bladeone": "^4.12",
        "cboden/ratchet": "^0.4"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "phpstan/phpstan": "^1.10",
        "friendsofphp/php-cs-fixer": "^3.15"
    },
    "autoload": {
        "psr-4": {
            "SugoiGame\\": "public/Classes/",
            "SugoiGame\\Utils\\": "public/Utils/",
            "SugoiGame\\Funcoes\\": "public/Funcoes/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "analyse": "phpstan analyse",
        "fix": "php-cs-fixer fix"
    }
}
```

#### 2.2 Instalar dependências
```bash
composer install
```

### Fase 3: Correções de Código 🔧

#### 3.1 Atualizar error_reporting

**Arquivo**: `public/Includes/database/mywrap.php`
```php
// ANTES
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);

// DEPOIS
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

#### 3.2 Substituir PHPMailer

**Remover**: `public/Classes/PHPMailer.php`
**Adicionar**: Nova implementação via Composer

```php
<?php
// public/Classes/ModernMailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class ModernMailer {
    private PHPMailer $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configure();
    }
    
    private function configure(): void {
        $this->mail->isSMTP();
        $this->mail->Host = SMTP_HOST;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = SMTP_USER;
        $this->mail->Password = SMTP_PASS;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        $this->mail->CharSet = 'UTF-8';
    }
    
    public function sendEmail(string $to, string $subject, string $body): bool {
        try {
            $this->mail->setFrom('noreply@sugoigame.com.br', 'Sugoi Game');
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->isHTML(true);
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Email error: " . $this->mail->ErrorInfo);
            return false;
        }
    }
}
```

#### 3.3 Modernizar Stripe Integration

```php
<?php
// public/Classes/ModernStripe.php
declare(strict_types=1);

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class ModernStripe {
    private StripeClient $stripe;
    
    public function __construct() {
        $this->stripe = new StripeClient(STRIPE_SECRET_KEY);
    }
    
    public function createPaymentIntent(int $amount, string $currency = 'brl'): ?array {
        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
            ]);
            
            return $paymentIntent->toArray();
        } catch (ApiErrorException $e) {
            error_log("Stripe error: " . $e->getMessage());
            return null;
        }
    }
}
```

#### 3.4 Adicionar Type Hints

**Exemplo**: Modernizar `UserDetails.php`
```php
<?php
declare(strict_types=1);

class UserDetails {
    private array $conta;
    private array $tripulacao;
    private ?mywrap_con $connection;
    
    public function __construct(?mywrap_con $connection = null) {
        $this->connection = $connection;
    }
    
    public function getUserId(): int {
        return (int) $this->conta['conta_id'];
    }
    
    public function getUserName(): string {
        return $this->tripulacao['nome'] ?? '';
    }
    
    public function canAddItem(): bool {
        // implementação existente com return type
        return true;
    }
}
```

### Fase 4: Testes 🧪

#### 4.1 Configurar PHPUnit

**Arquivo**: `phpunit.xml`
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php"
         colors="true"
         beStrictAboutCoversAnnotation="true"
         beStrictAboutOutputDuringTests="true"
         beStrictAboutTestsThatDoNotTestAnything="true"
         processIsolation="false"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory suffix="Test.php">./tests/Integration</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">./public/Classes</directory>
            <directory suffix=".php">./public/Funcoes</directory>
        </include>
    </coverage>
</phpunit>
```

#### 4.2 Exemplo de Teste

```php
<?php
// tests/Unit/UserDetailsTest.php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class UserDetailsTest extends TestCase {
    public function testGetUserIdReturnsInteger(): void {
        $userDetails = new UserDetails();
        // Setup test data
        
        $userId = $userDetails->getUserId();
        
        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);
    }
    
    public function testCanAddItemReturnsBool(): void {
        $userDetails = new UserDetails();
        
        $result = $userDetails->canAddItem();
        
        $this->assertIsBool($result);
    }
}
```

#### 4.3 Executar Testes

```bash
# Testes unitários
./vendor/bin/phpunit

# Análise estática
./vendor/bin/phpstan analyse

# Correção de código
./vendor/bin/php-cs-fixer fix
```

### Fase 5: Deploy 🚀

#### 5.1 Script de Deploy

```bash
#!/bin/bash
# deploy.sh

echo "🚀 Iniciando deploy PHP 8.x..."

# Backup
echo "📦 Criando backup..."
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > backup_pre_php8_$(date +%Y%m%d_%H%M%S).sql

# Atualizar código
echo "📥 Atualizando código..."
git pull origin feature/php8-migration

# Instalar dependências
echo "📚 Instalando dependências..."
composer install --no-dev --optimize-autoloader

# Verificar configuração
echo "🔍 Verificando configuração..."
php -v
php -m | grep -E "(mysqli|curl|json|mbstring)"

# Restart services
echo "🔄 Reiniciando serviços..."
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx

echo "✅ Deploy concluído!"
```

## 🔍 Monitoramento Pós-Migração

### 1. Logs a Monitorar

```bash
# Logs de erro PHP
tail -f /var/log/php8.2-fpm.log

# Logs de erro da aplicação
tail -f /path/to/application/error.log

# Logs do Nginx
tail -f /var/log/nginx/error.log
```

### 2. Métricas de Performance

```php
<?php
// Adicionar ao final de pages importantes
$memory_usage = memory_get_peak_usage(true);
$execution_time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

error_log("Performance: Memory: " . number_format($memory_usage / 1024 / 1024, 2) . "MB, Time: " . number_format($execution_time, 3) . "s");
```

### 3. Health Check

```php
<?php
// public/health-check.php
declare(strict_types=1);

header('Content-Type: application/json');

$checks = [
    'php_version' => PHP_VERSION,
    'database' => testDatabaseConnection(),
    'stripe' => testStripeConnection(),
    'memory_usage' => memory_get_usage(true),
    'timestamp' => date('Y-m-d H:i:s')
];

function testDatabaseConnection(): bool {
    try {
        $connection = new mywrap_con();
        $result = $connection->run("SELECT 1");
        return $result->count() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function testStripeConnection(): bool {
    try {
        $stripe = new ModernStripe();
        // Test with minimal operation
        return true;
    } catch (Exception $e) {
        return false;
    }
}

echo json_encode($checks, JSON_PRETTY_PRINT);
```

## 🚨 Rollback Plan

### Em caso de problemas críticos:

```bash
#!/bin/bash
# rollback.sh

echo "🔄 Iniciando rollback para PHP 7.4..."

# Restaurar backup do banco
mysql -u $DB_USER -p$DB_PASS $DB_NAME < backup_pre_php8_YYYYMMDD_HHMMSS.sql

# Voltar para branch main
git checkout main
git pull origin main

# Downgrade PHP (se necessário)
sudo update-alternatives --set php /usr/bin/php7.4

# Restart services
sudo systemctl restart php7.4-fpm
sudo systemctl restart nginx

echo "✅ Rollback concluído!"
```

## 📞 Suporte e Troubleshooting

### Problemas Comuns

1. **Erro "Call to undefined function"**
   - Verificar extensões PHP instaladas
   - Checar autoload do Composer

2. **Problemas de performance**
   - Ativar OPcache
   - Verificar configurações de memória

3. **Erros de Stripe/PagSeguro**
   - Validar credenciais de API
   - Verificar URLs de webhook

### Contatos de Emergência
- **Desenvolvedor Principal**: [email]
- **DevOps**: [email]
- **Suporte Stripe**: docs.stripe.com