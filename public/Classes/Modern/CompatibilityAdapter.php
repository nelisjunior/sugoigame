<?php

declare(strict_types=1);

namespace SugoiGame\Modern;

/**
 * Compatibility Adapter for gradual migration
 * Allows old code to work with new services while maintaining backward compatibility
 */
class CompatibilityAdapter
{
    private static ?StripeService $stripeService = null;
    private static ?EmailService $emailService = null;
    private static ?PagSeguroService $pagSeguroService = null;
    
    /**
     * Initialize services based on existing configuration
     */
    public static function initialize(): void
    {
        // Initialize Stripe service
        if (defined('STRIPE_TOKEN_SECRET')) {
            $webhookSecret = defined('STRIPE_CLI_WEBHOOK') ? STRIPE_CLI_WEBHOOK : '';
            self::$stripeService = new StripeService(STRIPE_TOKEN_SECRET, $webhookSecret);
        }
        
        // Initialize Email service
        self::$emailService = new EmailService([
            'host' => defined('SMTP_HOST') ? SMTP_HOST : 'localhost',
            'port' => defined('SMTP_PORT') ? SMTP_PORT : 587,
            'username' => defined('SMTP_USER') ? SMTP_USER : '',
            'password' => defined('SMTP_PASS') ? SMTP_PASS : '',
            'from_email' => 'noreply@sugoigame.com.br',
            'from_name' => 'Sugoi Game',
        ]);
        
        // Initialize PagSeguro service
        if (defined('PS_EMAIL') && defined('PS_TOKEN_SANDBOX')) {
            $token = defined('PS_ENV') && PS_ENV === 'production' && defined('PS_TOKEN_PRODUCTION') 
                ? PS_TOKEN_PRODUCTION 
                : PS_TOKEN_SANDBOX;
            $env = defined('PS_ENV') ? PS_ENV : 'sandbox';
            
            self::$pagSeguroService = new PagSeguroService(PS_EMAIL, $token, $env);
        }
    }
    
    /**
     * Get Stripe service instance
     */
    public static function getStripe(): ?StripeService
    {
        if (self::$stripeService === null) {
            self::initialize();
        }
        return self::$stripeService;
    }
    
    /**
     * Get Email service instance
     */
    public static function getEmail(): ?EmailService
    {
        if (self::$emailService === null) {
            self::initialize();
        }
        return self::$emailService;
    }
    
    /**
     * Get PagSeguro service instance
     */
    public static function getPagSeguro(): ?PagSeguroService
    {
        if (self::$pagSeguroService === null) {
            self::initialize();
        }
        return self::$pagSeguroService;
    }
}

/**
 * Legacy PHPMailer compatibility function
 * Allows old code to continue working while using new EmailService
 */
function send_legacy_email(string $to, string $subject, string $body, bool $isHTML = true): bool
{
    $emailService = CompatibilityAdapter::getEmail();
    if (!$emailService) {
        error_log("Email service not available");
        return false;
    }
    
    return $isHTML 
        ? $emailService->sendHTML($to, $subject, $body)
        : $emailService->sendText($to, $subject, $body);
}

/**
 * Legacy Stripe compatibility function
 * Allows old Stripe code to work with new StripeService
 */
function create_legacy_stripe_payment(int $amount, string $currency = 'brl', array $metadata = []): ?array
{
    $stripeService = CompatibilityAdapter::getStripe();
    if (!$stripeService) {
        error_log("Stripe service not available");
        return null;
    }
    
    return $stripeService->createPaymentIntent($amount, $currency, $metadata);
}

/**
 * Legacy PagSeguro compatibility function
 */
function create_legacy_pagseguro_payment(array $paymentData): ?array
{
    $pagSeguroService = CompatibilityAdapter::getPagSeguro();
    if (!$pagSeguroService) {
        error_log("PagSeguro service not available");
        return null;
    }
    
    return $pagSeguroService->createPayment($paymentData);
}