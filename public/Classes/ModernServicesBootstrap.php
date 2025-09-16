<?php

declare(strict_types=1);

/**
 * Modern Services Bootstrap
 * Este arquivo pode ser incluído nos scripts existentes para habilitar gradualmente os novos serviços
 */

// Só carrega se PHP 8.0+
if (PHP_VERSION_ID >= 80000) {
    
    // Autoload dos serviços modernos
    spl_autoload_register(function ($class) {
        if (strpos($class, 'SugoiGame\\Modern\\') === 0) {
            $classFile = str_replace('SugoiGame\\Modern\\', '', $class);
            $path = __DIR__ . '/Modern/' . $classFile . '.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }
    });

    // Flag para indicar que os serviços modernos estão disponíveis
    if (!defined('MODERN_SERVICES_AVAILABLE')) {
        define('MODERN_SERVICES_AVAILABLE', true);
    }

    // Função helper para verificar se deve usar serviços modernos
    function useModernServices(): bool {
        return defined('MODERN_SERVICES_AVAILABLE') && MODERN_SERVICES_AVAILABLE;
    }

    // Wrapper functions para compatibilidade gradual
    if (!function_exists('sendModernEmail')) {
        function sendModernEmail(string $to, string $toName, string $subject, string $body, string $fromEmail = null, string $fromName = null): array {
            if (!useModernServices()) {
                return ['success' => false, 'error' => 'Modern services not available'];
            }
            
            try {
                $config = new \SugoiGame\Modern\ConfigManager();
                $emailService = new \SugoiGame\Modern\EmailService([
                    'host' => $config->get('email.smtp.host', 'localhost'),
                    'port' => $config->get('email.smtp.port', 587),
                    'username' => $config->get('email.smtp.username', ''),
                    'password' => $config->get('email.smtp.password', ''),
                    'encryption' => $config->get('email.smtp.encryption', 'tls')
                ]);
                
                return $emailService->sendEmail(
                    $to, 
                    $toName, 
                    $subject, 
                    $body, 
                    $fromEmail ?? 'noreply@sugoigame.com.br',
                    $fromName ?? 'Sugoi Game'
                );
            } catch (Exception $e) {
                error_log("Error in sendModernEmail: " . $e->getMessage());
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }
    }

    if (!function_exists('createModernStripePayment')) {
        function createModernStripePayment(int $amount, string $currency, array $metadata = []): array {
            if (!useModernServices()) {
                return ['success' => false, 'error' => 'Modern services not available'];
            }
            
            try {
                $config = new \SugoiGame\Modern\ConfigManager();
                $stripeService = new \SugoiGame\Modern\StripeService($config->get('stripe.secret_key'));
                
                return $stripeService->createPaymentIntent($amount, $currency, $metadata);
            } catch (Exception $e) {
                error_log("Error in createModernStripePayment: " . $e->getMessage());
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }
    }

    if (!function_exists('createModernPagSeguroPayment')) {
        function createModernPagSeguroPayment(array $orderData): array {
            if (!useModernServices()) {
                return ['success' => false, 'error' => 'Modern services not available'];
            }
            
            try {
                $config = new \SugoiGame\Modern\ConfigManager();
                $pagSeguroService = new \SugoiGame\Modern\PagSeguroService([
                    'email' => $config->get('pagseguro.email'),
                    'token' => $config->get('pagseguro.token'),
                    'sandbox' => $config->get('pagseguro.sandbox', true)
                ]);
                
                return $pagSeguroService->createOrder($orderData);
            } catch (Exception $e) {
                error_log("Error in createModernPagSeguroPayment: " . $e->getMessage());
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }
    }

    // Helper para migração gradual de classes
    if (!function_exists('getModernUserDetails')) {
        function getModernUserDetails($connection) {
            if (!useModernServices()) {
                return null;
            }
            
            try {
                return \SugoiGame\UserDetailsModern::create($connection);
            } catch (Exception $e) {
                error_log("Error creating modern UserDetails: " . $e->getMessage());
                return null;
            }
        }
    }

    if (!function_exists('getModernProtector')) {
        function getModernProtector($userDetails, $response) {
            if (!useModernServices()) {
                return null;
            }
            
            try {
                return new \SugoiGame\ProtectorModern($userDetails, $response);
            } catch (Exception $e) {
                error_log("Error creating modern Protector: " . $e->getMessage());
                return null;
            }
        }
    }

    if (!function_exists('getModernDB')) {
        function getModernDB(): ?\SugoiGame\DBModern {
            if (!useModernServices()) {
                return null;
            }
            
            try {
                return new \SugoiGame\DBModern();
            } catch (Exception $e) {
                error_log("Error creating modern DB: " . $e->getMessage());
                return null;
            }
        }
    }

} else {
    // PHP < 8.0 - define flag como false
    if (!defined('MODERN_SERVICES_AVAILABLE')) {
        define('MODERN_SERVICES_AVAILABLE', false);
    }
    
    function useModernServices(): bool {
        return false;
    }
}