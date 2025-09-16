<?php

declare(strict_types=1);

namespace SugoiGame;

use SugoiGame\UserDetailsModern;
use SugoiGame\Response;

/**
 * Class ProtectorModern - Modernized for PHP 8.x
 * 
 * Handles access control, session validation, and game state protection
 * 
 * @package SugoiGame
 */
class ProtectorModern
{
    // Session configuration mapping
    private const SESSION_REQUIREMENTS = [
        'academia' => ['tripulacao', 'in_ilha', 'out_combat', 'out_mission_recruit'],
        'equipShop' => ['tripulacao', 'in_ilha', 'out_combat', 'out_mission_recruit'],
        'estaleiro' => ['tripulacao', 'in_ilha', 'out_combat', 'out_mission_recruit'],
        'mercado' => ['tripulacao', 'in_ilha', 'out_combat', 'out_mission_recruit'],
        'materiais' => ['tripulacao', 'in_ilha', 'out_combat', 'out_mission_recruit'],
        'restaurante' => ['tripulacao', 'in_ilha', 'out_combat', 'out_mission_recruit'],
        'hospital' => ['tripulacao', 'in_ilha', 'out_combat', 'out_mission_recruit'],
        'profissoesAprender' => ['tripulacao', 'in_ilha', 'out_combat', 'out_mission_recruit'],
        'upgrader' => ['tripulacao', 'in_ilha', 'out_combat', 'out_mission_recruit'],
        'tripulantesInativos' => ['tripulacao', 'in_ilha', 'out_combat', 'out_mission_recruit'],
        'politicaIlha' => ['tripulacao', 'in_ilha', 'out_combat', 'out_mission_recruit'],
        'combate' => ['tripulacao', 'in_any_combat'],
        'coliseu' => ['tripulacao', 'out_combat'],
        'localizadorCasual' => ['tripulacao', 'out_combat'],
        'localizadorCompetitivo' => ['tripulacao', 'out_combat'],
        'missoes' => ['tripulacao', 'in_ilha', 'out_combat', 'out_recruit'],
        'missoesConcluidas' => ['tripulacao', 'in_ilha', 'out_combat', 'out_recruit'],
        'incursao' => ['tripulacao', 'in_ilha', 'out_combat', 'out_recruit'],
        'missoesR' => ['tripulacao', 'in_ilha', 'out_combat', 'out_recruit'],
        'recrutar' => ['tripulacao', 'in_ilha', 'out_combat', 'out_mission'],
        'akuma' => ['tripulacao', 'out_combat', 'out_mission_recruit'],
        'akumaComer' => ['tripulacao', 'out_combat', 'out_mission_recruit'],
        'quartos' => ['tripulacao', 'out_combat', 'out_mission_recruit'],
        'forja' => ['tripulacao', 'out_combat', 'out_mission_recruit'],
        'oficina' => ['tripulacao', 'out_combat', 'out_mission_recruit'],
        'statusNavio' => ['tripulacao', 'navio', 'out_combat'],
        'navioSkin' => ['tripulacao', 'navio', 'out_combat'],
        'equipamentos' => ['tripulacao'],
        'status' => ['tripulacao'],
        'tripulacao' => ['tripulacao'],
        'haki' => ['tripulacao'],
        'listaNegra' => ['tripulacao'],
    ];

    // Full-width sessions (no sidebar)
    private const FULL_WIDE_SESSIONS = [
        'combate', 'combateAssistir', 'pvp_waiting', 'pvp_waiting_random', 
        'world_map', 'mapAtualizar', 'chat'
    ];

    // Sessions that should show world map
    private const WORLD_MAP_SESSIONS = [
        'mapa', 'oceano', 'mapAtualizar', 'chat'
    ];

    public function __construct(
        private readonly UserDetailsModern $userDetails,
        private readonly Response $response
    ) {}

    /**
     * Main protection method - validates session requirements
     */
    public function protect(string $session): void
    {
        global $sistemas_por_sessao;
        
        // Check if session requires a specific system
        if (isset($sistemas_por_sessao[$session])) {
            $this->needSistema($sistemas_por_sessao[$session]);
        }

        // Apply session-specific requirements
        $requirements = self::SESSION_REQUIREMENTS[$session] ?? [];
        
        foreach ($requirements as $requirement) {
            match ($requirement) {
                'tripulacao' => $this->needTripulacao(),
                'conta' => $this->needConta(),
                'navio' => $this->needNavio(),
                'in_ilha' => $this->mustBeInIlha(),
                'out_combat' => $this->mustBeOutOfAnyCombat(),
                'in_any_combat' => $this->mustBeInAnyCombat(),
                'out_mission' => $this->mustBeOutOfMission(),
                'out_recruit' => $this->mustBeOutOfRecruit(),
                'out_mission_recruit' => $this->mustBeOutOfMissionAndRecruit(),
                'next_to_land' => $this->mustBeNextToLand(),
                'tripulacao_alive' => $this->needTripulacaoAlive(),
                'navio_alive' => $this->needNavioAlive(),
                default => null // Ignore unknown requirements
            };
        }
    }

    /**
     * Check if session should be displayed in full width
     */
    public function isFullWideSession(string $session): bool
    {
        return in_array($session, self::FULL_WIDE_SESSIONS, true);
    }

    /**
     * Check if session should show world map
     */
    public function shouldShowWorldMap(string $session): bool
    {
        return in_array($session, self::WORLD_MAP_SESSIONS, true);
    }

    /**
     * Require GM/Admin privileges
     */
    public function mustBeGm(): void
    {
        if (!($this->userDetails->getTripulacao()['adm'] ?? false)) {
            $this->exitError("Você não pode acessar isso.");
        }
    }

    /**
     * Require specific system to be unlocked
     */
    public function needSistema(string $sistema): void
    {
        if (!$this->userDetails->is_sistema_desbloqueado($sistema)) {
            $this->exitError("Você ainda não desbloqueou este recurso.");
        }
    }

    /**
     * Require user to be logged in with tripulacao
     */
    public function needTripulacao(): void
    {
        if (empty($this->userDetails->getTripulacao())) {
            $this->exitError("Você precisa estar logado.");
        }
    }

    /**
     * Require Impel Down campaign access
     */
    public function needCampanhaImpelDown(): void
    {
        if (!($this->userDetails->getTripulacao()['campanha_impel_down'] ?? false)) {
            $this->exitError("Você não liberou essa campanha ainda");
        }
    }

    /**
     * Require Enies Lobby campaign access
     */
    public function needCampanhaEniesLobby(): void
    {
        if (!($this->userDetails->getTripulacao()['campanha_enies_lobby'] ?? false)) {
            $this->exitError("Você não liberou essa campanha ainda");
        }
    }

    /**
     * Reject if user is logged in with tripulacao
     */
    public function rejectTripulacao(): void
    {
        if (!empty($this->userDetails->getTripulacao())) {
            $this->exitError("Você não pode estar logado.");
        }
    }

    /**
     * Require user to be logged in with account
     */
    public function needConta(): void
    {
        if (empty($this->userDetails->conta)) {
            $this->exitError("Você precisa estar logado.");
        }
    }

    /**
     * Reject if user is logged in with account
     */
    public function rejectConta(): void
    {
        if (!empty($this->userDetails->conta)) {
            $this->exitError("Você já está logado.");
        }
    }

    /**
     * Require user to have a ship
     */
    public function needNavio(): void
    {
        if (empty($this->userDetails->navio)) {
            $this->exitError("Você precisa de um navio.");
        }
    }

    /**
     * Require ship to be alive (HP > 0)
     */
    public function needNavioAlive(): void
    {
        $navio = $this->userDetails->navio;
        if (($navio['hp'] ?? 0) <= 0) {
            $this->exitError("Seu navio está destruído, procure um estaleiro para repará-lo.");
        }
    }

    /**
     * Require tripulacao to be alive
     */
    public function needTripulacaoAlive(): void
    {
        if (!$this->userDetails->getTripulacaoAlive()) {
            echo $this->userDetails->isInIlha() ? "!hospital" : "!respawn";
            exit();
        }
    }

    /**
     * Require tripulacao to be dead
     */
    public function needTripulacaoDied(): void
    {
        if ($this->userDetails->getTripulacaoAlive()) {
            $this->exitError("Você não deveria estar aqui.");
        }
    }

    /**
     * Require to be next to land
     */
    public function mustBeNextToLand(): void
    {
        if (!($this->userDetails->has_ilha_or_terra_envolta_me ?? false)) {
            $this->exitError("Você precisa estar próximo a uma ilha.");
        }
    }

    /**
     * Require to be out of any kind of combat
     */
    public function mustBeOutOfAnyCombat(): void
    {
        if ($this->userDetails->isInCombate()) {
            $this->exitError("Você não pode fazer isso durante um combate.");
        }
    }

    /**
     * Require to be in any kind of combat
     */
    public function mustBeInAnyCombat(): void
    {
        if (!$this->userDetails->isInCombate()) {
            $this->exitError("Você precisa estar em combate.");
        }
    }

    /**
     * Require to be out of PvP combat
     */
    public function mustBeOutOfCombatPvp(): void
    {
        if (!empty($this->userDetails->combate_pvp)) {
            $this->exitError("Você não pode fazer isso durante um combate PvP.");
        }
    }

    /**
     * Require to be out of PvE combat
     */
    public function mustBeOutOfCombatPve(): void
    {
        if (!empty($this->userDetails->combate_pve)) {
            $this->exitError("Você não pode fazer isso durante um combate PvE.");
        }
    }

    /**
     * Require to be out of bot combat
     */
    public function mustBeOutOfCombatBot(): void
    {
        if (!empty($this->userDetails->combate_bot)) {
            $this->exitError("Você não pode fazer isso durante um combate contra bot.");
        }
    }

    /**
     * Require to be in an island
     */
    public function mustBeInIlha(): void
    {
        if (!$this->userDetails->isInIlha()) {
            $this->exitError("Você precisa estar em uma ilha.");
        }
    }

    /**
     * Require to be out of island
     */
    public function mustBeOutOfIlha(): void
    {
        if ($this->userDetails->isInIlha()) {
            $this->exitError("Você não pode estar em uma ilha.");
        }
    }

    /**
     * Require to be out of mission
     */
    public function mustBeOutOfMission(): void
    {
        if (!empty($this->userDetails->missao)) {
            $this->exitError("Você não pode fazer isso durante uma missão.");
        }
    }

    /**
     * Require to be out of recruitment
     */
    public function mustBeOutOfRecruit(): void
    {
        if (!empty($this->userDetails->missao_r)) {
            $this->exitError("Você não pode fazer isso durante um recrutamento.");
        }
    }

    /**
     * Require to be out of both mission and recruitment
     */
    public function mustBeOutOfMissionAndRecruit(): void
    {
        $this->mustBeOutOfMission();
        $this->mustBeOutOfRecruit();
    }

    /**
     * Require user to be in coliseum queue
     */
    public function mustBeInFilaColiseu(): void
    {
        if (empty($this->userDetails->fila_coliseu)) {
            $this->exitError("Você não está na fila do coliseu.");
        }
    }

    /**
     * Require user to be out of coliseum queue
     */
    public function mustBeOutOfFilaColiseu(): void
    {
        if (!empty($this->userDetails->fila_coliseu)) {
            $this->exitError("Você está na fila do coliseu.");
        }
    }

    /**
     * Exit with error message
     */
    private function exitError(string $message): never
    {
        if ($this->response) {
            $this->response->error($message);
        } else {
            echo json_encode(['error' => $message]);
        }
        exit();
    }
}