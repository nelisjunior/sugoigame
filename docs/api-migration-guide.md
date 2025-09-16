# 🔄 Guia de Migração das APIs - Sugoi Game

## 📋 Visão Geral

Este documento explica como migrar do código legado para as novas APIs modernizadas do PHP 8.x.

## 🎯 Estratégia de Migração

### Fase A: Preparação ✅
- [x] Novas classes criadas em `public/Classes/Modern/`
- [x] Adapter de compatibilidade implementado
- [x] Testes unitários criados

### Fase B: Migração Gradual (Em andamento)
- [ ] Identificar pontos de uso das APIs antigas
- [ ] Substituir gradualmente por novas APIs
- [ ] Testar funcionalidades críticas

### Fase C: Limpeza
- [ ] Remover código legado
- [ ] Otimizar performance
- [ ] Finalizar documentação

## 🔧 APIs Modernizadas

### 1. Stripe API

#### ❌ Código Antigo (Stripe 6.x)
```php
// Forma antiga
require_once 'public/Includes/stripe/init.php';
\Stripe\Stripe::setApiKey(STRIPE_TOKEN_SECRET);

$payment_intent = \Stripe\PaymentIntent::create([
    'amount' => 1000,
    'currency' => 'brl',
]);
```

#### ✅ Código Novo (Stripe 12.x)
```php
// Forma nova - PHP 8.x
use SugoiGame\Modern\StripeService;

$stripe = new StripeService(STRIPE_TOKEN_SECRET);
$payment_intent = $stripe->createPaymentIntent(1000, 'brl', ['user_id' => 123]);
```

#### 🔄 Migração com Adapter
```php
// Migração gradual usando adapter
use SugoiGame\Modern\CompatibilityAdapter;

$stripe = CompatibilityAdapter::getStripe();
$payment_intent = $stripe->createPaymentIntent(1000, 'brl');
```

### 2. Email Service (PHPMailer)

#### ❌ Código Antigo
```php
// Forma antiga
require_once 'public/Classes/PHPMailer.php';

$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'localhost';
// Configurações manuais...
$mail->setFrom('noreply@sugoigame.com.br');
$mail->addAddress($email);
$mail->Subject = $subject;
$mail->Body = $body;
$mail->send();
```

#### ✅ Código Novo
```php
// Forma nova - PHP 8.x
use SugoiGame\Modern\EmailService;

$email = new EmailService([
    'host' => 'localhost',
    'port' => 587,
    'username' => SMTP_USER,
    'password' => SMTP_PASS,
]);

$email->sendHTML($to, $subject, $htmlBody);
```

#### 🔄 Migração com Adapter
```php
// Migração gradual
use function SugoiGame\Modern\send_legacy_email;

send_legacy_email($email, $subject, $body, true);
```

### 3. PagSeguro API

#### ❌ Código Antigo
```php
// Forma antiga
require_once 'public/Includes/PagSeguro/PagSeguroLibrary.php';

$paymentRequest = new PagSeguroPaymentRequest();
$paymentRequest->addItem('1', 'Item Test', 1, 100.00);
$paymentRequest->setSender('User Name', 'user@example.com');
// Configurações manuais...
$url = $paymentRequest->register($credentials);
```

#### ✅ Código Novo
```php
// Forma nova - PHP 8.x
use SugoiGame\Modern\PagSeguroService;

$pagSeguro = new PagSeguroService(PS_EMAIL, PS_TOKEN_SANDBOX, 'sandbox');

$paymentData = [
    'items' => [
        ['description' => 'Item Test', 'amount' => 100.00, 'quantity' => 1]
    ],
    'sender' => ['name' => 'User Name', 'email' => 'user@example.com'],
    'reference' => 'ORDER_001'
];

$result = $pagSeguro->createPayment($paymentData);
```

## 📍 Pontos de Migração Identificados

### Arquivos que Usam Stripe
```bash
grep -r "Stripe\|stripe" public/ --include="*.php" | head -10
```

### Arquivos que Usam PHPMailer
```bash
grep -r "PHPMailer\|mail->" public/ --include="*.php" | head -10
```

### Arquivos que Usam PagSeguro
```bash
grep -r "PagSeguro\|pagseguro" public/ --include="*.php" | head -10
```

## 🔄 Plano de Migração por Módulo

### 1. Sistema de Pagamentos

#### Arquivos a Migrar:
- `public/Scripts/Pagamentos/stripe_*.php`
- `public/Scripts/Pagamentos/pagseguro_*.php`
- `public/Classes/PayPal.php` (se usado)

#### Estratégia:
1. Criar novos endpoints usando APIs modernas
2. Testar em ambiente de desenvolvimento
3. Migrar gradualmente os endpoints
4. Remover código legado

### 2. Sistema de Emails

#### Arquivos a Migrar:
- `public/Funcoes/email_*.php`
- `public/Scripts/*/email.php`
- Qualquer arquivo que use PHPMailer diretamente

#### Estratégia:
1. Substituir função por função
2. Usar adapter para compatibilidade
3. Testar envio de emails críticos
4. Migrar templates de email

### 3. Sistema de Notificações

#### Arquivos a Migrar:
- `public/Scripts/Webhooks/`
- `public/Scripts/Notifications/`

#### Estratégia:
1. Implementar novos handlers de webhook
2. Testar com APIs de teste
3. Validar assinaturas de webhook
4. Migrar para produção

## 🧪 Estratégia de Testes

### 1. Testes Unitários
```bash
# Executar testes das novas classes
./vendor/bin/phpunit tests/Unit/StripeServiceTest.php
./vendor/bin/phpunit tests/Unit/EmailServiceTest.php
./vendor/bin/phpunit tests/Unit/PagSeguroServiceTest.php
```

### 2. Testes de Integração
```php
// Exemplo de teste de integração Stripe
class StripeIntegrationTest extends TestCase
{
    public function testCreateRealPaymentIntent(): void
    {
        if (!getenv('STRIPE_TEST_KEY')) {
            $this->markTestSkipped('Stripe test key not available');
        }
        
        $stripe = new StripeService(getenv('STRIPE_TEST_KEY'));
        $result = $stripe->createPaymentIntent(1000, 'brl');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
    }
}
```

### 3. Testes Funcionais
- Testar fluxo completo de pagamento
- Validar recebimento de webhooks
- Verificar envio de emails de confirmação

## 📊 Checklist de Migração

### Stripe
- [ ] Identificar todos os usos de Stripe 6.x
- [ ] Migrar para StripeService
- [ ] Testar webhooks
- [ ] Validar métodos de pagamento
- [ ] Atualizar documentação

### Email
- [ ] Identificar todos os usos de PHPMailer legacy
- [ ] Migrar para EmailService
- [ ] Testar envio de emails
- [ ] Validar templates
- [ ] Configurar SMTP adequadamente

### PagSeguro
- [ ] Identificar usos da biblioteca legada
- [ ] Migrar para PagSeguroService
- [ ] Testar API REST
- [ ] Validar notifications
- [ ] Atualizar configurações

## ⚠️ Pontos de Atenção

### Compatibilidade
- Manter APIs antigas funcionando durante migração
- Usar feature flags para alternar entre implementações
- Logs detalhados para debugging

### Performance
- Novas APIs são mais eficientes
- Melhor tratamento de erros
- Menor uso de memória

### Segurança
- Validação aprimorada de dados
- Melhor tratamento de credenciais
- Logs de segurança

## 🚀 Próximos Passos

1. **Identificar Usos**: Mapear todos os pontos que usam APIs antigas
2. **Priorizar**: Começar por funcionalidades críticas
3. **Testar**: Validar cada migração completamente
4. **Monitorar**: Acompanhar logs e performance
5. **Limpar**: Remover código legado após validação

## 📞 Suporte

### Recursos Úteis
- **Stripe API Docs**: https://stripe.com/docs/api
- **PHPMailer Docs**: https://github.com/PHPMailer/PHPMailer
- **PagSeguro API**: https://dev.pagseguro.uol.com.br/

### Contatos
- **Desenvolvedor Principal**: [email]
- **DevOps**: [email]
- **QA**: [email]

---

**Versão**: 1.0  
**Data**: 16 de setembro de 2025  
**Status**: Em desenvolvimento