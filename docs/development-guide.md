# 🚀 Guia de Desenvolvimento - Sugoi Game

## 🏗️ Estrutura do Projeto

```
sugoigame/
├── 📁 public/                 # Código principal da aplicação
│   ├── 📁 Classes/           # Classes PHP principais
│   ├── 📁 Constantes/        # Constantes e configurações
│   ├── 📁 CSS/              # Estilos da aplicação
│   ├── 📁 Funcoes/          # Funções auxiliares
│   ├── 📁 Includes/         # Arquivos de inclusão
│   ├── 📁 JS/               # JavaScript frontend
│   ├── 📁 Regras/           # Lógica de negócio
│   ├── 📁 Scripts/          # Endpoints da API
│   ├── 📁 Sessoes/          # Páginas da aplicação
│   └── 📄 index.php         # Ponto de entrada
├── 📁 servers/               # Servidores auxiliares
│   ├── 📁 chat/             # Servidor de chat (Node.js)
│   └── 📁 map/              # Servidor de mapa (WebSocket)
├── 📁 database/             # Scripts de banco de dados
├── 📁 docs/                 # Documentação
└── 📄 README.md
```

## 🛠️ Ambiente de Desenvolvimento

### Configuração Local
```bash
# 1. Configurar PHP
php -v  # Verificar versão 7.4+

# 2. Configurar MySQL
mysql --version  # Verificar versão 8.0+

# 3. Instalar dependências Node.js
cd servers/chat
npm install

# 4. Instalar dependências PHP
cd servers/map
composer install
```

### Configuração do Banco de Dados
```sql
-- Criar banco de dados
CREATE DATABASE sugoigame CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Criar usuário (opcional)
CREATE USER 'sugoi_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON sugoigame.* TO 'sugoi_user'@'localhost';
```

### Configuração do PHP
```php
// public/Includes/conectdb.php
$host = "localhost";
$user = "sugoi_user";
$pass = "password";
$dbname = "sugoigame";

// Configurações de desenvolvimento
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## 🔧 Fluxo de Desenvolvimento

### 1. Criando uma Nova Feature

```bash
# Criar branch para feature
git checkout -b feature/nova-funcionalidade

# Fazer alterações...
# Commit das mudanças
git add .
git commit -m "feat: adicionar nova funcionalidade"

# Push da branch
git push origin feature/nova-funcionalidade
```

### 2. Estrutura de Commits
```
feat: nova funcionalidade
fix: correção de bug
docs: atualização de documentação
style: mudanças de formatação
refactor: refatoração de código
test: adição de testes
chore: tarefas de manutenção
```

### 3. Testando Alterações
```bash
# Testar funcionalidades principais
1. Login/Logout
2. Criação de personagem
3. Sistema de combate
4. Navegação
5. Chat em tempo real

# Verificar logs de erro
tail -f /var/log/php/errors.log
```

## 📋 Padrões de Código

### PHP
```php
<?php
// Sempre usar tags completas
// Seguir PSR-12

class ExemploClasse 
{
    private $propriedade;
    
    public function metodoExemplo($parametro): string
    {
        // Usar type hints quando possível
        return $this->processarDados($parametro);
    }
    
    private function processarDados($dados): string 
    {
        // Validar dados de entrada
        if (empty($dados)) {
            throw new InvalidArgumentException('Dados não podem estar vazios');
        }
        
        return $dados;
    }
}
```

### JavaScript
```javascript
// Usar const/let em vez de var
const CONFIG = {
    apiUrl: '/Scripts/',
    timeout: 5000
};

// Funções com arrow syntax quando apropriado
const processarDados = (dados) => {
    if (!dados) {
        throw new Error('Dados obrigatórios');
    }
    
    return dados.map(item => item.toLowerCase());
};

// Usar async/await para requisições
async function buscarDados() {
    try {
        const response = await fetch(CONFIG.apiUrl + 'endpoint.php');
        return await response.json();
    } catch (error) {
        console.error('Erro ao buscar dados:', error);
        throw error;
    }
}
```

### SQL
```sql
-- Usar snake_case para tabelas e colunas
CREATE TABLE tb_exemplo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_usuario VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nome_usuario (nome_usuario)
);

-- Usar prepared statements sempre
$stmt = $connection->prepare("SELECT * FROM tb_usuarios WHERE id = ?");
$stmt->bind_param("i", $userId);
```

## 🧪 Sistema de Testes

### Testes Funcionais
```php
// tests/CombateTest.php
class CombateTest extends PHPUnit\Framework\TestCase
{
    public function testIniciarCombatePvP()
    {
        $combate = new Combate($jogador1, $jogador2);
        $this->assertTrue($combate->iniciar());
        $this->assertEquals('ativo', $combate->getStatus());
    }
    
    public function testCalcularDano()
    {
        $personagem = new Personagem(['atk' => 100]);
        $dano = $personagem->calcularDano(50);
        $this->assertGreaterThan(0, $dano);
    }
}
```

### Testes de API
```javascript
// tests/api.test.js
describe('API de Combate', () => {
    test('deve iniciar combate com parâmetros válidos', async () => {
        const response = await fetch('/Scripts/Batalha/batalha_atacar.php', {
            method: 'POST',
            body: new FormData()
        });
        
        expect(response.ok).toBe(true);
    });
});
```

## 🔍 Debugging

### PHP Debugging
```php
// Usar error_log para debug
error_log("Debug: valor da variável = " . print_r($variavel, true));

// Usar try-catch para capturar erros
try {
    $resultado = operacaoRiscosa();
} catch (Exception $e) {
    error_log("Erro: " . $e->getMessage());
    // Resposta de erro para o cliente
    echo "#Erro interno do servidor";
}
```

### JavaScript Debugging
```javascript
// Console debugging
console.log('Estado atual:', estado);
console.error('Erro detectado:', erro);

// Debugger browser
debugger; // Breakpoint no navegador

// Performance monitoring
console.time('operacao');
operacaoComplexe();
console.timeEnd('operacao');
```

## 📊 Monitoramento

### Logs da Aplicação
```php
// Configurar logging
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/sugoigame/php_errors.log');

// Log customizado
function logAcao($usuario, $acao, $detalhes = '') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] User: $usuario, Action: $acao, Details: $detalhes\n";
    file_put_contents('/var/log/sugoigame/actions.log', $logMessage, FILE_APPEND);
}
```

### Métricas de Performance
```javascript
// Monitorar tempo de resposta das requisições
function monitorarRequisicao(url, dados) {
    const inicio = performance.now();
    
    return fetch(url, dados)
        .then(response => {
            const tempo = performance.now() - inicio;
            console.log(`Requisição para ${url} levou ${tempo}ms`);
            return response;
        });
}
```

## 🚀 Deploy

### Ambiente de Produção
```bash
# 1. Preparar arquivos
rsync -av --exclude='tests/' --exclude='.git/' . user@server:/var/www/sugoigame/

# 2. Configurar permissões
chmod -R 755 /var/www/sugoigame/
chown -R www-data:www-data /var/www/sugoigame/

# 3. Configurar banco de produção
mysql -u root -p < database/production.sql

# 4. Iniciar serviços
pm2 start servers/chat/index.js --name "sugoi-chat"
supervisorctl start sugoi-map-server
```

### Checklist de Deploy
- [ ] Testar todas as funcionalidades principais
- [ ] Verificar configurações de banco
- [ ] Confirmar URLs de produção
- [ ] Testar sistema de pagamento
- [ ] Verificar logs de erro
- [ ] Validar performance
- [ ] Backup do banco antes do deploy

## 🛡️ Segurança

### Validação de Entrada
```php
// Sempre validar dados de entrada
function validarEntrada($dados) {
    $dados = trim($dados);
    $dados = stripslashes($dados);
    $dados = htmlspecialchars($dados);
    return $dados;
}

// Usar prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

### Autenticação
```php
// Verificar sessão em todas as páginas protegidas
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Hash de senhas
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$verified = password_verify($password, $hashedPassword);
```

## 📚 Recursos Úteis

### Documentação
- [PHP Manual](https://www.php.net/manual/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Node.js Documentation](https://nodejs.org/docs/)

### Ferramentas Recomendadas
- **IDE**: VS Code com extensões PHP/JavaScript
- **Database**: phpMyAdmin ou MySQL Workbench
- **API Testing**: Postman ou Insomnia
- **Version Control**: Git com GitHub/GitLab

### Extensões VS Code
```json
{
    "recommendations": [
        "bmewburn.vscode-intelephense-client",
        "bradlc.vscode-tailwindcss",
        "ms-vscode.vscode-json",
        "formulahendry.auto-rename-tag",
        "xdebug.php-debug"
    ]
}
```