<?php

declare(strict_types=1);

namespace SugoiGame\Modern;

/**
 * Modern Configuration Manager for PHP 8.x
 * Replaces scattered configuration and provides centralized management
 */
class ConfigManager
{
    private static array $config = [];
    private static bool $initialized = false;
    
    /**
     * Initialize configuration from environment and constants
     */
    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }
        
        // Database configuration
        self::$config['database'] = [
            'host' => defined('DB_SERVER') ? DB_SERVER : 'localhost',
            'user' => defined('DB_USER') ? DB_USER : 'root',
            'password' => defined('DB_PASS') ? DB_PASS : '',
            'database' => defined('DB_NAME') ? DB_NAME : 'sugoigame',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];
        
        // Stripe configuration
        self::$config['stripe'] = [
            'public_key' => defined('STRIPE_TOKEN_PUBLIC') ? STRIPE_TOKEN_PUBLIC : '',
            'secret_key' => defined('STRIPE_TOKEN_SECRET') ? STRIPE_TOKEN_SECRET : '',
            'webhook_secret' => defined('STRIPE_CLI_WEBHOOK') ? STRIPE_CLI_WEBHOOK : '',
        ];
        
        // PagSeguro configuration
        self::$config['pagseguro'] = [
            'email' => defined('PS_EMAIL') ? PS_EMAIL : '',
            'token_sandbox' => defined('PS_TOKEN_SANDBOX') ? PS_TOKEN_SANDBOX : '',
            'token_production' => defined('PS_TOKEN_PRODUCTION') ? PS_TOKEN_PRODUCTION : '',
            'environment' => defined('PS_ENV') ? PS_ENV : 'sandbox',
        ];
        
        // Email configuration
        self::$config['email'] = [
            'host' => $_ENV['SMTP_HOST'] ?? 'localhost',
            'port' => (int)($_ENV['SMTP_PORT'] ?? 587),
            'username' => $_ENV['SMTP_USER'] ?? '',
            'password' => $_ENV['SMTP_PASS'] ?? '',
            'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
            'from_email' => $_ENV['FROM_EMAIL'] ?? 'noreply@sugoigame.com.br',
            'from_name' => $_ENV['FROM_NAME'] ?? 'Sugoi Game',
        ];
        
        // Application configuration
        self::$config['app'] = [
            'name' => 'Sugoi Game',
            'version' => '2.0.0',
            'environment' => $_ENV['APP_ENV'] ?? 'development',
            'debug' => filter_var($_ENV['APP_DEBUG'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
            'timezone' => 'America/Sao_Paulo',
            'locale' => 'pt_BR',
        ];
        
        // Security configuration
        self::$config['security'] = [
            'session_lifetime' => 7200, // 2 hours
            'csrf_token_name' => 'sugoi_csrf_token',
            'max_login_attempts' => 5,
            'lockout_duration' => 900, // 15 minutes
        ];
        
        // Game configuration
        self::$config['game'] = [
            'initial_points' => defined('PONTOS_INICIAIS') ? PONTOS_INICIAIS : 69,
            'points_per_level' => defined('PONTOS_POR_NIVEL') ? PONTOS_POR_NIVEL : 4,
            'initial_hp' => defined('HP_INICIAL') ? HP_INICIAL : 5300,
            'hp_per_level' => defined('HP_POR_NIVEL') ? HP_POR_NIVEL : 300,
            'hp_per_vitality' => defined('HP_POR_VITALIDADE') ? HP_POR_VITALIDADE : 50,
            'max_haki_level' => defined('HAKI_LVL_MAX') ? HAKI_LVL_MAX : 50,
        ];
        
        self::$initialized = true;
    }
    
    /**
     * Get configuration value by path (dot notation)
     */
    public static function get(string $path, mixed $default = null): mixed
    {
        self::initialize();
        
        $keys = explode('.', $path);
        $value = self::$config;
        
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }
        
        return $value;
    }
    
    /**
     * Set configuration value by path
     */
    public static function set(string $path, mixed $value): void
    {
        self::initialize();
        
        $keys = explode('.', $path);
        $config = &self::$config;
        
        foreach ($keys as $key) {
            if (!isset($config[$key]) || !is_array($config[$key])) {
                $config[$key] = [];
            }
            $config = &$config[$key];
        }
        
        $config = $value;
    }
    
    /**
     * Check if configuration path exists
     */
    public static function has(string $path): bool
    {
        self::initialize();
        
        $keys = explode('.', $path);
        $value = self::$config;
        
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return false;
            }
            $value = $value[$key];
        }
        
        return true;
    }
    
    /**
     * Get all configuration
     */
    public static function all(): array
    {
        self::initialize();
        return self::$config;
    }
    
    /**
     * Get database configuration
     */
    public static function database(): array
    {
        return self::get('database', []);
    }
    
    /**
     * Get Stripe configuration
     */
    public static function stripe(): array
    {
        return self::get('stripe', []);
    }
    
    /**
     * Get PagSeguro configuration
     */
    public static function pagseguro(): array
    {
        return self::get('pagseguro', []);
    }
    
    /**
     * Get email configuration
     */
    public static function email(): array
    {
        return self::get('email', []);
    }
    
    /**
     * Get application configuration
     */
    public static function app(): array
    {
        return self::get('app', []);
    }
    
    /**
     * Check if we're in production environment
     */
    public static function isProduction(): bool
    {
        return self::get('app.environment') === 'production';
    }
    
    /**
     * Check if debug mode is enabled
     */
    public static function isDebug(): bool
    {
        return self::get('app.debug', false);
    }
    
    /**
     * Validate required configuration
     */
    public static function validate(): array
    {
        $errors = [];
        
        // Database validation
        if (!self::get('database.host')) {
            $errors[] = 'Database host is required';
        }
        
        if (!self::get('database.database')) {
            $errors[] = 'Database name is required';
        }
        
        // Stripe validation (if in production)
        if (self::isProduction()) {
            if (!self::get('stripe.secret_key')) {
                $errors[] = 'Stripe secret key is required in production';
            }
        }
        
        return $errors;
    }
}