<?php

declare(strict_types=1);

namespace SugoiGame;

use SugoiGame\BuffTripulacao;
use SugoiGame\Equipamentos;
use SugoiGame\Alerts;

/**
 * Class UserDetails - Modernized for PHP 8.x
 * 
 * Handles user session management, data loading, and core game mechanics
 * 
 * @package SugoiGame
 */
class UserDetailsModern
{
    // Core properties with proper typing
    private array $tripulacao = [];
    private array $personagens = [];
    private array $rotas = [];
    private array $vip = [];
    private array $conta = [];
    private array $tripulacoes = [];
    private array $capitao = [];
    private int $lvl_mais_forte = 0;
    private int $fa_mais_alta = 0;
    private array $lvl_medico = [];
    private array $medicos = [];
    private array $navegadores = [];
    private int $lvl_navegador = 0;
    private array $carpinteiros = [];
    private int $lvl_carpinteiro = 0;
    private array $artesoes = [];
    private array $ferreiros = [];
    private array $mergulhadores = [];
    private int $lvl_mergulhador = 0;
    private array $cartografos = [];
    private int $lvl_cartografo = 0;
    private array $arqueologos = [];
    private int $lvl_arqueologo = 0;
    private bool $in_ilha = false;
    private array $ilha = [];
    private array $navio = [];
    private array $ally = [];
    private array $combate_pvp = [];
    private array $tripulacoes_pvp = [];
    private array $combate_pve = [];
    private array $combate_bot = [];
    private bool $in_combate = false;
    private array $missao = [];
    private array $missao_r = [];
    private bool $is_visivel = true;
    private bool $has_ilha_envolta_me = false;
    private bool $has_ilha_or_terra_envolta_me = false;
    private bool $tripulacao_alive = true;
    private array $fila_coliseu = [];
    private int $lvl_coliseu = 0;
    private array $alerts_data = [];
    private array $super_alerts_data = [];

    // Cached properties to track what's been loaded
    private array $loaded_properties = [];

    public function __construct(
        private readonly \mywrap_con $connection,
        public readonly BuffTripulacao $buffs,
        public readonly Equipamentos $equipamentos,
        public readonly Alerts $alerts
    ) {
        $this->_update_last_logon();
        $this->_update_vip();
    }

    /**
     * Factory method to create UserDetails with dependencies
     */
    public static function create(\mywrap_con $connection): self
    {
        $instance = new self(
            $connection,
            new BuffTripulacao($connection),
            new Equipamentos($connection),
            new Alerts($connection)
        );
        
        return $instance;
    }

    /**
     * Magic getter with proper type safety and caching
     */
    public function __get(string $property): mixed
    {
        // Check if property exists as private property
        if (property_exists($this, $property)) {
            // Check if it's already loaded or is a simple property
            if (isset($this->loaded_properties[$property]) || !$this->needsLoading($property)) {
                return $this->$property;
            }
        }

        // Try to load the property using a loader method
        $load_method = "_load_$property";
        if (method_exists($this, $load_method)) {
            $this->$load_method();
            $this->loaded_properties[$property] = true;
            return $this->$property ?? null;
        }

        return null;
    }

    /**
     * Check if property needs loading from database
     */
    private function needsLoading(string $property): bool
    {
        $properties_that_need_loading = [
            'tripulacao', 'personagens', 'rotas', 'vip', 'conta', 
            'tripulacoes', 'capitao', 'ilha', 'navio', 'ally',
            'combate_pvp', 'combate_pve', 'combate_bot', 'missao'
        ];
        
        return in_array($property, $properties_that_need_loading, true);
    }

    /**
     * Get current timestamp
     */
    public function get_time_now(): int
    {
        return time();
    }

    /**
     * Get user IP address with proper validation
     */
    public function get_user_ip(): string
    {
        $headers = function_exists('apache_request_headers') 
            ? apache_request_headers() 
            : $_SERVER;

        // Check forwarded headers
        $forwarded_headers = ['X-Forwarded-For', 'HTTP_X_FORWARDED_FOR'];
        foreach ($forwarded_headers as $header) {
            if (isset($headers[$header])) {
                $ip = filter_var($headers[$header], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
                if ($ip !== false) {
                    return $ip;
                }
            }
        }

        // Fallback to remote address
        $ip = filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        return $ip !== false ? $ip : '127.0.0.1';
    }

    /**
     * Update last logon information
     */
    protected function _update_last_logon(): void
    {
        if (empty($this->tripulacao)) {
            return;
        }

        $uri = mb_strimwidth($_SERVER["REQUEST_URI"] ?? '', 0, 255);
        
        $this->connection->run(
            "UPDATE tb_usuarios SET ip = ?, ultimo_logon = ?, ultima_pagina = ? WHERE id = ?",
            "sssi", 
            [$this->get_user_ip(), $this->get_time_now(), $uri, $this->tripulacao["id"]]
        );
    }

    /**
     * Update VIP status and expiration
     */
    private function _update_vip(): void
    {
        if (empty($this->vip)) {
            return;
        }

        $tempo = $this->get_time_now();
        $vip_features = [
            'luneta' => 'luneta_duracao',
            'sense' => 'sense_duracao', 
            'tatic' => 'tatic_duracao',
            'conhecimento' => 'conhecimento_duracao',
            'coup_de_burst' => 'coup_de_burst_duracao'
        ];

        foreach ($vip_features as $feature => $duration_field) {
            if (($this->vip[$duration_field] ?? 0) < $tempo && ($this->vip[$duration_field] ?? 0) !== 0) {
                $this->connection->run(
                    "UPDATE tb_vip SET {$feature} = '0', {$duration_field} = '0' WHERE id = ?",
                    "i", 
                    [$this->tripulacao["id"]]
                );
            }
        }
    }

    /**
     * Start session if not already active
     */
    public function start_session(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Get authentication token from session/cookies
     */
    protected function _get_token(): array|false
    {
        $this->start_session();

        // Helper function to validate alphanumeric strings
        $validate_alphanumeric = function(string $value): bool {
            return preg_match('/^[a-zA-Z0-9]+$/', $value) === 1;
        };

        // Try to get from cookies first
        if (isset($_COOKIE["sg_c"], $_COOKIE["sg_k"])) {
            if (!$validate_alphanumeric($_COOKIE["sg_c"]) || !$validate_alphanumeric($_COOKIE["sg_k"])) {
                return false;
            }
            
            $_SESSION["sg_c"] = $_COOKIE["sg_c"];
            $_SESSION["sg_k"] = $_COOKIE["sg_k"];
            
            return [
                'id_encrip' => $_COOKIE["sg_c"],
                'cookie' => $_COOKIE["sg_k"]
            ];
        }

        // Fallback to session
        if (isset($_SESSION["sg_c"], $_SESSION["sg_k"])) {
            if (!$validate_alphanumeric($_SESSION["sg_c"]) || !$validate_alphanumeric($_SESSION["sg_k"])) {
                return false;
            }
            
            return [
                'id_encrip' => $_SESSION["sg_c"],
                'cookie' => $_SESSION["sg_k"]
            ];
        }

        return false;
    }

    // Getter methods for properties (type-safe access)
    public function getTripulacao(): array
    {
        return $this->tripulacao;
    }

    public function getPersonagens(): array
    {
        return $this->personagens;
    }

    public function isInCombate(): bool
    {
        return $this->in_combate;
    }

    public function isInIlha(): bool
    {
        return $this->in_ilha;
    }

    public function getTripulacaoAlive(): bool
    {
        return $this->tripulacao_alive;
    }

    // Setter methods for controlled property updates
    public function setInCombate(bool $in_combate): void
    {
        $this->in_combate = $in_combate;
    }

    public function setInIlha(bool $in_ilha): void
    {
        $this->in_ilha = $in_ilha;
    }
}