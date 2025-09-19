# Arquitetura do Sistema - Sugoi Game

```mermaid
graph TB
    %% Camada de Interface
    subgraph "Frontend Layer"
        UI[Interface Web]
        Login[Login System]
        Game[Game Interface]
        Combat[Combat UI]
    end

    %% Camada de Aplicação
    subgraph "Application Layer"
        PHP[PHP Backend]
        API[Game API]
        Auth[Authentication]
        Session[Session Management]
    end

    %% Camada de Lógica de Negócio
    subgraph "Business Logic"
        GameEngine[Game Engine]
        CombatSystem[Combat System]
        CharacterSystem[Character System]
        GuildSystem[Alliance System]
        QuestSystem[Mission System]
        ItemSystem[Item System]
        PvPSystem[PvP System]
        PvESystem[PvE System]
    end

    %% Camada de Dados
    subgraph "Data Layer"
        MySQL[(MySQL Database)]
        UserData[User Data]
        GameData[Game Data]
        CombatData[Combat Data]
        AllianceData[Alliance Data]
    end

    %% Serviços Externos
    subgraph "External Services"
        ChatServer[Chat Server<br/>Node.js + Socket.io]
        MapServer[Map Server<br/>PHP WebSocket]
        PaymentGateway[Payment Systems<br/>PayPal/PagSeguro]
    end

    %% Conexões Frontend
    UI --> Login
    UI --> Game
    Game --> Combat
    
    %% Conexões Application
    Login --> Auth
    Game --> API
    Combat --> API
    API --> Session
    
    %% Conexões Business Logic
    Auth --> GameEngine
    API --> GameEngine
    GameEngine --> CombatSystem
    GameEngine --> CharacterSystem
    GameEngine --> GuildSystem
    GameEngine --> QuestSystem
    GameEngine --> ItemSystem
    CombatSystem --> PvPSystem
    CombatSystem --> PvESystem
    
    %% Conexões Data
    GameEngine --> MySQL
    CombatSystem --> MySQL
    CharacterSystem --> MySQL
    GuildSystem --> MySQL
    QuestSystem --> MySQL
    ItemSystem --> MySQL
    
    MySQL --> UserData
    MySQL --> GameData
    MySQL --> CombatData
    MySQL --> AllianceData
    
    %% Conexões External Services
    Game --> ChatServer
    Game --> MapServer
    API --> PaymentGateway
    
    %% Estilos
    classDef frontend fill:#e1f5fe
    classDef application fill:#f3e5f5
    classDef business fill:#e8f5e8
    classDef data fill:#fff3e0
    classDef external fill:#fce4ec
    
    class UI,Login,Game,Combat frontend
    class PHP,API,Auth,Session application
    class GameEngine,CombatSystem,CharacterSystem,GuildSystem,QuestSystem,ItemSystem,PvPSystem,PvESystem business
    class MySQL,UserData,GameData,CombatData,AllianceData data
    class ChatServer,MapServer,PaymentGateway external
```

## Componentes Principais

### Frontend Layer
- **Interface Web**: Interface principal do jogo em HTML/CSS/JavaScript
- **Login System**: Sistema de autenticação com suporte a Facebook
- **Game Interface**: Interface principal do jogo
- **Combat UI**: Interface específica para combates

### Application Layer
- **PHP Backend**: Servidor principal em PHP
- **Game API**: APIs REST para comunicação frontend-backend
- **Authentication**: Sistema de autenticação e autorização
- **Session Management**: Gerenciamento de sessões de usuário

### Business Logic
- **Game Engine**: Motor principal do jogo
- **Combat System**: Sistema de combate turn-based
- **Character System**: Gerenciamento de personagens e tripulação
- **Alliance System**: Sistema de alianças entre jogadores
- **Mission System**: Sistema de missões e quests
- **Item System**: Gerenciamento de itens e equipamentos
- **PvP System**: Sistema Player vs Player
- **PvE System**: Sistema Player vs Environment

### Data Layer
- **MySQL Database**: Banco de dados principal
- **User Data**: Dados de usuários e contas
- **Game Data**: Dados de jogo (personagens, itens, etc.)
- **Combat Data**: Dados de combates e logs
- **Alliance Data**: Dados de alianças e guilds

### External Services
- **Chat Server**: Servidor de chat em Node.js com Socket.io
- **Map Server**: Servidor de mapa em PHP WebSocket
- **Payment Systems**: Integração com PayPal e PagSeguro