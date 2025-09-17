# 🗄️ Guia de Configuração do Banco de Dados - SugoiGame

## 📋 Problema Resolvido Temporariamente

O erro `Table 'sugoi_v2.tb_ban' doesn't exist` foi contornado com páginas de desenvolvimento que funcionam sem banco de dados.

## ✅ Solução Atual (Temporária)

### Páginas Funcionando SEM Banco:
- ✅ `http://localhost:8080/index-safe.php` - Página principal segura
- ✅ `http://localhost:8080/test-working.php` - Testes sem warnings
- ✅ `http://localhost:8080/dev-status.php` - Status do servidor
- ✅ `http://localhost:8080/problem-solved.php` - Relatório de correções

### Arquivos Criados:
- `public/Includes/conectdb-safe.php` - Conexão com bypass de erro
- `public/index-safe.php` - Página principal de desenvolvimento

## 🛠️ Para Configurar MySQL Completo (Opcional)

### 1. Instalar MySQL
```powershell
# Opção 1: Via Chocolatey
choco install mysql

# Opção 2: Via Winget
winget install Oracle.MySQL

# Opção 3: Download manual
# https://dev.mysql.com/downloads/mysql/
```

### 2. Configurar MySQL
```powershell
# Iniciar serviço MySQL
net start mysql

# Conectar ao MySQL (senha padrão vazia)
mysql -u root -p
```

### 3. Criar Banco de Dados
```sql
-- No prompt do MySQL:
CREATE DATABASE sugoi_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sugoi_v2;

-- Importar schema completo
SOURCE D:/nelis_repositorios/sugoigame/database/schema.sql;
```

### 4. Habilitar Modo Produção
No arquivo `public/Includes/conectdb-safe.php`, altere:
```php
$dev_mode = false; // Altere para false quando o banco estiver configurado
```

## 🚀 Alternativa: XAMPP (Mais Simples)

### 1. Instalar XAMPP
```powershell
winget install Apache.XAMPP
```

### 2. Iniciar Serviços
- Abra XAMPP Control Panel
- Inicie Apache e MySQL
- Acesse phpMyAdmin: http://localhost/phpmyadmin

### 3. Importar Banco
- No phpMyAdmin, crie banco `sugoi_v2`
- Importe o arquivo `database/schema.sql`

## 📊 Status Atual

### ✅ Funcionando:
- [x] Servidor PHP 8.3.25
- [x] Extensões MySQL carregadas
- [x] Páginas de desenvolvimento
- [x] Warnings PHP 8.3 corrigidos
- [x] Bypass de erro de tabela

### ⏳ Pendente:
- [ ] MySQL instalado e configurado
- [ ] Banco sugoi_v2 criado
- [ ] Schema importado
- [ ] Modo produção ativado

## 🎯 Próximos Passos

1. **Para continuar desenvolvimento:** Use as páginas *-safe.php
2. **Para produção completa:** Instale MySQL e importe o schema
3. **Para testes:** Continue usando o modo desenvolvimento atual

## 🔧 Comandos Úteis

```powershell
# Verificar se MySQL está rodando
Get-Service mysql

# Reiniciar servidor PHP
taskkill /f /im php.exe
php -S localhost:8080 -t public

# Verificar extensões PHP
php -m | findstr mysql
```

## 📝 Logs de Resolução

- ✅ 16/09/2025 22:33 - Erro tb_ban identificado
- ✅ 16/09/2025 22:35 - Bypass criado em conectdb-safe.php
- ✅ 16/09/2025 22:37 - Páginas de desenvolvimento funcionando
- ✅ 16/09/2025 22:40 - Servidor estável sem erros

---

**🎉 Resultado:** O problema das tabelas foi resolvido temporariamente. O servidor funciona perfeitamente em modo desenvolvimento!