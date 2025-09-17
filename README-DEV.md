# 🎮 Sugoi Game - Guia de Desenvolvimento

**One Piece MMORPG - Ambiente de Desenvolvimento Configurado**

## 🚀 Status da Configuração

✅ **AMBIENTE PRONTO PARA DESENVOLVIMENTO!**

### ✅ Componentes Configurados

- **PHP 8.2.12** (XAMPP) com todas as extensões necessárias
- **MySQL/MariaDB** rodando e conectado
- **Composer** com 65 dependências instaladas
- **Servidor Web** ativo em localhost:8000
- **39 testes passando** (PHPUnit)
- **Análise estática** configurada (PHPStan)

---

## 🔧 Como Iniciar o Ambiente

### 1. **Iniciar MySQL**
```bash
# Execute o script criado:
.\start-mysql.bat

# Ou manualmente:
cd C:\xampp\mysql\bin
mysqld.exe --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
```

### 2. **Iniciar Servidor Web**
```bash
# Usar PHP do XAMPP:
C:\xampp\php\php.exe -S localhost:8000 -t public

# Ou configurar PATH e usar:
$env:PATH = "C:\xampp\php;C:\xampp\mysql\bin;" + $env:PATH
php -S localhost:8000 -t public
```

### 3. **Acessar o Jogo**
- **Status do Ambiente**: http://localhost:8000/dev-status.php
- **Jogo Principal**: http://localhost:8000/index.php
- **Teste de Banco**: http://localhost:8000/../test-db.php

---

## 📋 Configurações do Ambiente

### **Banco de Dados**
- **Host**: localhost:3306
- **Banco**: sugoi_v2
- **Usuário**: root
- **Senha**: (vazia)
- **Charset**: utf8mb4

### **APIs Configuradas**
- **Stripe**: Modo sandbox (tokens de teste)
- **PagSeguro**: Modo sandbox
- **SMTP**: Não configurado (opcional)

### **Dependências Principais**
- `stripe/stripe-php: ^12.8.0`
- `phpmailer/phpmailer: ^6.10.0`
- `eftec/bladeone: ^4.19.1`
- `phpunit/phpunit: ^10.5.55`
- `phpstan/phpstan: ^1.12.29`

---

## 🧪 Comandos de Desenvolvimento

### **Testes**
```bash
# Todos os testes (39 passando)
C:\xampp\php\php.exe vendor/bin/phpunit

# Testes com cobertura
composer test:coverage

# Testes específicos
vendor/bin/phpunit tests/Unit/
vendor/bin/phpunit tests/Integration/
```

### **Análise de Código**
```bash
# PHPStan (análise estática)
C:\xampp\php\php.exe vendor/bin/phpstan analyse

# PHP CS Fixer (formatação)
vendor/bin/php-cs-fixer fix

# Verificar sem corrigir
vendor/bin/php-cs-fixer fix --dry-run --diff
```

### **Scripts Composer**
```bash
composer test          # PHPUnit
composer analyse        # PHPStan
composer fix           # PHP CS Fixer
composer check         # Verificar formatação
composer dev:setup     # Setup completo
```

---

## 📁 Estrutura do Projeto

```
sugoigame/
├── public/              # Código principal (document root)
│   ├── Classes/        # Classes PHP
│   │   └── Modern/    # Classes migradas para PHP 8.x
│   ├── Constantes/    # Configurações
│   ├── Scripts/       # Scripts de funcionalidades  
│   ├── dev-status.php # Status do ambiente
│   └── index.php      # Página principal
├── database/           # Schema e migrations
├── vendor/            # Dependências Composer
├── tests/             # Testes PHPUnit
├── docs/              # Documentação
└── composer.json      # Configuração do projeto
```

---

## 🔍 Páginas de Desenvolvimento

### **Status do Ambiente**
`http://localhost:8000/dev-status.php`
- Status completo do ambiente
- Verificação de extensões PHP
- Teste de conexão com banco
- Informações de configuração

### **Teste de Banco**
`http://localhost:8000/../test-db.php`
- Teste direto de conexão MySQL
- Criação de tabelas de teste
- Verificação de charset

### **phpinfo()**
`http://localhost:8000/dev-status.php?action=phpinfo`
- Informações completas do PHP
- Extensões carregadas
- Configurações ativas

---

## 🚨 Troubleshooting

### **Erro "Class mysqli not found"**
```bash
# Verificar se está usando PHP do XAMPP:
which php  # Deve mostrar C:\xampp\php\php.exe

# Configurar PATH:
$env:PATH = "C:\xampp\php;" + $env:PATH
```

### **MySQL não conecta**
```bash
# Verificar se MySQL está rodando:
tasklist | findstr mysqld

# Reiniciar MySQL:
.\start-mysql.bat
```

### **Composer não funciona**
```bash
# Verificar instalação:
composer --version

# Limpar cache:
composer clear-cache

# Reinstalar dependências:
composer install --no-cache
```

### **Servidor não inicia**
```bash
# Verificar se porta está ocupada:
netstat -an | findstr :8000

# Usar porta alternativa:
php -S localhost:8080 -t public
```

---

## 🎯 Próximos Passos

### **Para Desenvolvimento Completo**
1. **Importar Schema Completo**
   ```bash
   mysql -u root sugoi_v2 < database/schema.sql
   ```

2. **Configurar Serviços Opcionais**
   - Chat Server (Node.js) em `servers/chat/`
   - Map Server (PHP WebSockets) em `servers/map/`

3. **Configurar SMTP (Opcional)**
   - Configurar servidor SMTP local
   - Ou usar serviços como Mailtrap

### **Para Produção**
1. Configurar servidor web (Apache/Nginx)
2. Configurar banco de dados MySQL
3. Configurar tokens de API reais
4. Configurar SSL/HTTPS

---

## 📞 Informações de Suporte

### **Versões Instaladas**
- **PHP**: 8.2.12 (XAMPP)
- **MySQL**: 10.4.32-MariaDB
- **Composer**: 2.8.11
- **Node.js**: (opcional para chat)

### **Portas Utilizadas**
- **Web Server**: 8000
- **MySQL**: 3306
- **Chat Server**: 9000 (opcional)

### **Logs Importantes**
- Servidor PHP: console onde rodou `php -S`
- MySQL: `C:\xampp\mysql\data\*.err`
- Aplicação: `public/Logs/`

---

## 🎉 Ambiente Configurado com Sucesso!

O ambiente de desenvolvimento está **100% funcional** e pronto para desenvolvimento.

**URLs principais:**
- **Jogo**: http://localhost:8000/
- **Status**: http://localhost:8000/dev-status.php
- **Teste DB**: http://localhost:8000/../test-db.php

**Comandos essenciais:**
```bash
# Iniciar ambiente
.\start-mysql.bat
C:\xampp\php\php.exe -S localhost:8000 -t public

# Executar testes
C:\xampp\php\php.exe vendor/bin/phpunit

# Análise de código
C:\xampp\php\php.exe vendor/bin/phpstan analyse
```

Happy coding! 🚀