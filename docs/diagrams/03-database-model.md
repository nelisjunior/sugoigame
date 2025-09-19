# Modelo de Dados - Sugoi Game

```mermaid
erDiagram
    %% Usuários e Autenticação
    tb_usuarios {
        int id PK
        string login
        string senha
        string email
        string tripulacao
        int faccao
        int x
        int y
        int berries
        int gold
        int reputacao
        int reputacao_mensal
        datetime ultimo_login
        int bandeira
        int navio
        int cod_personagem
        int protecao_pvp
        int adm
    }

    tb_personagens {
        int cod PK
        int id FK
        string nome
        int img
        int skin_r
        int skin_c
        int lvl
        int xp
        int xp_max
        int hp
        int hp_max
        int mp
        int mp_max
        int atk
        int def
        int agl
        int res
        int pre
        int dex
        int con
        int pts_gastos
        int ativo
        int temporario
        int haki_lvl
        int haki_xp
        int haki_xp_max
        int haki_pts
    }

    %% Sistema de Combate
    tb_combate {
        int combate PK
        int id_1 FK
        int id_2 FK
        int turno
        int tipo
        datetime inicio
        int ilha_x
        int ilha_y
    }

    tb_combate_personagens {
        int id FK
        int cod_pers FK
        int quadro_x
        int quadro_y
        int hp
        int mp
        int morreu
        int vez_perdida
    }

    tb_combate_npc {
        int id PK FK
        string img_npc
        string nome_npc
        int hp_npc
        int hp_max_npc
        int mp_npc
        int mp_max_npc
        int atk_npc
        int def_npc
        int agl_npc
        int res_npc
        int pre_npc
        int dex_npc
        int con_npc
        int dano
        int armadura
        int zona
        int boss_id
        string battle_back
    }

    tb_combate_log {
        int id PK
        int combate FK
        int id_1 FK
        int id_2 FK
        int vencedor FK
        int tipo
        datetime horario
        int reputacao_ganha
        int reputacao_perdida
        int berries_ganhos
        int berries_perdidos
    }

    %% Sistema de Alianças
    tb_aliancas {
        int id PK
        string nome
        string descricao
        int lider FK
        int lvl
        int xp
        int berries
        int max_membros
        datetime criacao
        string tag
    }

    tb_alianca_membros {
        int id PK
        int alianca_id FK
        int usuario_id FK
        int cargo
        datetime entrada
        int contribuicoes
    }

    %% Sistema de Itens
    tb_itens {
        int cod PK
        string nome
        string descricao
        int tipo
        int preco
        int lvl_req
        int atk_bonus
        int def_bonus
        int hp_bonus
        int mp_bonus
        int raridade
    }

    tb_inventario {
        int id PK
        int usuario_id FK
        int item_cod FK
        int quantidade
        int equipado
        int posicao_x
        int posicao_y
    }

    %% Sistema de Missões
    tb_missoes {
        int cod PK
        string nome
        string descricao
        int xp_reward
        int berries_reward
        int lvl_req
        int ilha_req
        int tipo
        json objetivos
    }

    tb_missoes_progresso {
        int id PK
        int usuario_id FK
        int missao_cod FK
        json progresso
        int completada
        datetime inicio
        datetime fim
    }

    %% Sistema de Navegação
    tb_mapa {
        int id PK
        int x
        int y
        int ilha
        int tipo_agua
        int dificuldade
        string nome_ilha
        json recursos
    }

    tb_navegacao {
        int usuario_id PK FK
        int destino_x
        int destino_y
        datetime chegada
        int velocidade
    }

    %% Relacionamentos
    tb_usuarios ||--o{ tb_personagens : possui
    tb_usuarios ||--o{ tb_combate : participa_como_id1
    tb_usuarios ||--o{ tb_combate : participa_como_id2
    tb_usuarios ||--o{ tb_combate_npc : combate_pve
    tb_usuarios ||--o{ tb_inventario : possui_itens
    tb_usuarios ||--o{ tb_missoes_progresso : executa_missoes
    tb_usuarios ||--o{ tb_navegacao : navega
    tb_usuarios ||--o{ tb_alianca_membros : membro_de
    
    tb_personagens ||--o{ tb_combate_personagens : participa_combate
    
    tb_combate ||--o{ tb_combate_personagens : contem_personagens
    tb_combate ||--o{ tb_combate_log : gera_log
    
    tb_aliancas ||--o{ tb_alianca_membros : tem_membros
    tb_aliancas ||--|| tb_usuarios : liderada_por
    
    tb_itens ||--o{ tb_inventario : instancia_item
    tb_missoes ||--o{ tb_missoes_progresso : instancia_missao
```

## Principais Tabelas e Funcionalidades

### Usuários e Personagens
- **tb_usuarios**: Dados principais da conta do jogador
- **tb_personagens**: Personagens da tripulação do jogador

### Sistema de Combate
- **tb_combate**: Combates PvP ativos
- **tb_combate_personagens**: Personagens participando de combates
- **tb_combate_npc**: Combates PvE contra NPCs
- **tb_combate_log**: Histórico de combates

### Sistema de Alianças
- **tb_aliancas**: Dados das alianças/guilds
- **tb_alianca_membros**: Membros das alianças

### Sistema de Itens e Inventário
- **tb_itens**: Catálogo de itens do jogo
- **tb_inventario**: Inventário dos jogadores

### Sistema de Missões
- **tb_missoes**: Catálogo de missões
- **tb_missoes_progresso**: Progresso dos jogadores nas missões

### Sistema de Navegação
- **tb_mapa**: Mapa do mundo do jogo
- **tb_navegacao**: Navegação ativa dos jogadores