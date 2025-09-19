# API Documentation - Sugoi Game

## Endpoints Principais

### Autenticação
```
POST /Scripts/Geral/logar.php
- Parâmetros: login, senha
- Resposta: Redirecionamento ou erro

POST /Scripts/Geral/cadastro.php  
- Parâmetros: login, senha, email, tripulacao, faccao
- Resposta: Conta criada ou erro

POST /Scripts/Geral/login_facebook.php
- Parâmetros: Facebook OAuth
- Resposta: Login via Facebook
```

### Sistema de Combate
```
POST /Scripts/Batalha/batalha_atacar.php
- Parâmetros: cod_skil, pers, tipo, quadro
- Resposta: Resultado do ataque

GET /Scripts/Batalha/batalha_tabuleiro.php
- Resposta: Estado atual do tabuleiro de combate

POST /Scripts/Batalha/batalha_passar.php
- Resposta: Passa a vez no combate

POST /Scripts/Batalha/batalha_desistir.php
- Resposta: Desiste do combate atual

POST /Scripts/Mapa/mapa_atacar.php
- Parâmetros: id (alvo), tipo
- Resposta: Inicia combate
```

### Personagens e Tripulação
```
POST /Scripts/Personagem/adiciona_atributo.php
- Parâmetros: cod_pers, atributo
- Resposta: Atributo adicionado

POST /Scripts/Personagem/equipar.php
- Parâmetros: cod_item, cod_pers
- Resposta: Item equipado

POST /Scripts/Personagem/ativar.php
- Parâmetros: cod_pers
- Resposta: Personagem ativado

POST /Scripts/Recrutar/recrutamento_iniciar.php
- Parâmetros: duracao
- Resposta: Recrutamento iniciado
```

### Sistema de Alianças
```
POST /Scripts/Alianca/alianca_criar.php
- Parâmetros: nome, tag, descricao
- Resposta: Aliança criada

POST /Scripts/Alianca/alianca_convidar.php
- Parâmetros: usuario_id
- Resposta: Convite enviado

POST /Scripts/Alianca/alianca_guerra_convidar.php
- Parâmetros: alianca_alvo
- Resposta: Guerra declarada

POST /Scripts/Alianca/alianca_deposito.php
- Parâmetros: quantidade
- Resposta: Berries depositados
```

### Navegação e Mapa
```
GET /Scripts/Mapa/verifica_nav.php
- Resposta: Status da navegação atual

POST /Scripts/Geral/navegacao_automatica.php
- Parâmetros: destino_x, destino_y
- Resposta: Navegação iniciada

GET /public/oceano.php
- Resposta: Interface do oceano
```

### Sistema de Missões
```
POST /Scripts/Missoes/missao_iniciar.php
- Parâmetros: cod_missao
- Resposta: Missão iniciada

POST /Scripts/Missoes/missao_finalizar.php
- Resposta: Missão finalizada

POST /Scripts/MissaoCaca/missao_caca_iniciar.php
- Parâmetros: tipo_caca
- Resposta: Caça iniciada
```

### Inventário e Itens
```
POST /Scripts/Inventario/usar_item.php
- Parâmetros: cod_item
- Resposta: Item usado

POST /Scripts/Mercado/mercado_comprar.php
- Parâmetros: cod_item, quantidade
- Resposta: Item comprado

POST /Scripts/Inventario/descartar_item.php
- Parâmetros: cod_item
- Resposta: Item descartado
```

### VIP e Pagamentos
```
POST /Scripts/Vip/adquirirPP.php
- Parâmetros: PayPal payment data
- Resposta: Processamento de pagamento

POST /Scripts/PayPal/retorno.php
- Parâmetros: PayPal callback
- Resposta: Confirmação de pagamento

POST /Scripts/Vip/transporte_berries.php
- Parâmetros: quantidade, destino
- Resposta: Transferência realizada
```

## Estrutura de Resposta

### Sucesso
```php
// Redirecionamento
header("location: página_destino.php");

// Resposta de sucesso
echo "%sucesso";

// Dados em JSON (alguns endpoints)
echo json_encode($dados);
```

### Erro
```php
// Erro com prefixo #
echo "#Mensagem de erro";

// Erro de validação
echo "#Você precisa estar logado!";
echo "#Você está em combate!";
```

## Middleware e Validações

### Verificações Comuns
```php
// Arquivo: Includes/verifica_login.php
$conect = isset($userDetails->conta);

// Arquivo: Includes/verifica_combate.php  
$incombate = $userDetails->combate_pvp || 
             $userDetails->combate_pve || 
             $userDetails->combate_bot;

// Arquivo: Includes/verifica_missao.php
$inmissao = $usuario["recrutando"] != 0;
```

### Protector Class
```php
// Validações de segurança
$protector->need_tripulacao();
$protector->must_be_out_of_combat();
$protector->must_be_in_ilha();
$protector->get_number_or_exit("param");
$protector->post_value_or_exit("param");
```

## WebSocket Endpoints

### Chat Server (Node.js)
```
ws://localhost:3000
- Eventos: message, join, leave
- Autenticação via session
```

### Map Server (PHP WebSocket)
```
ws://localhost:8080
- Eventos: position_update, combat_start
- Sincronização de mapa em tempo real
```

## Estrutura de Dados

### UserDetails Object
```php
$userDetails = {
    "conta": {...},           // Dados da conta
    "tripulacao": {...},      // Dados da tripulação
    "combate_pvp": {...},     // Estado PvP
    "combate_pve": {...},     // Estado PvE
    "combate_bot": {...},     // Estado Bot
    "personagens": [...],     // Lista de personagens
    "inventario": [...]       // Itens do inventário
}
```