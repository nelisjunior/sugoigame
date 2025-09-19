<?php

declare(strict_types=1);

namespace SugoiGame\Modern;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Modern Email Service for PHP 8.x
 * Replaces the old PHPMailer legacy implementation
 */
class EmailService
{
    private PHPMailer $mail;
    private array $config;
    
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'host' => 'localhost',
            'port' => 587,
            'username' => '',
            'password' => '',
            'encryption' => PHPMailer::ENCRYPTION_STARTTLS,
            'from_email' => 'noreply@sugoigame.com.br',
            'from_name' => 'Sugoi Game',
            'charset' => 'UTF-8',
            'debug_level' => 0,
        ], $config);
        
        $this->mail = new PHPMailer(true);
        $this->configure();
    }
    
    /**
     * Configure PHPMailer settings
     */
    private function configure(): void
    {
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = $this->config['host'];
            $this->mail->SMTPAuth = !empty($this->config['username']);
            $this->mail->Username = $this->config['username'];
            $this->mail->Password = $this->config['password'];
            $this->mail->SMTPSecure = $this->config['encryption'];
            $this->mail->Port = $this->config['port'];
            $this->mail->CharSet = $this->config['charset'];
            
            // Debug settings
            $this->mail->SMTPDebug = $this->config['debug_level'];
            
            // Default from address
            $this->mail->setFrom($this->config['from_email'], $this->config['from_name']);
            
        } catch (Exception $e) {
            error_log("Email Configuration Error: " . $e->getMessage());
            throw new \RuntimeException("Failed to configure email service: " . $e->getMessage());
        }
    }
    
    /**
     * Send a simple text email
     */
    public function sendText(
        string $to,
        string $subject,
        string $body,
        string $toName = ''
    ): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            $this->mail->addAddress($to, $toName);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->isHTML(false);
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Email Send Error: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send an HTML email
     */
    public function sendHTML(
        string $to,
        string $subject,
        string $htmlBody,
        string $toName = '',
        string $altBody = ''
    ): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            $this->mail->addAddress($to, $toName);
            $this->mail->Subject = $subject;
            $this->mail->Body = $htmlBody;
            $this->mail->AltBody = $altBody;
            $this->mail->isHTML(true);
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Email Send Error: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send email with template
     */
    public function sendTemplate(
        string $to,
        string $subject,
        string $templatePath,
        array $variables = [],
        string $toName = ''
    ): bool {
        if (!file_exists($templatePath)) {
            error_log("Email template not found: " . $templatePath);
            return false;
        }
        
        // Extract variables for template
        extract($variables);
        
        // Capture template output
        ob_start();
        include $templatePath;
        $htmlBody = ob_get_clean();
        
        return $this->sendHTML($to, $subject, $htmlBody, $toName);
    }
    
    /**
     * Send email to multiple recipients
     */
    public function sendBulk(
        array $recipients,
        string $subject,
        string $body,
        bool $isHTML = true
    ): array {
        $results = [];
        
        foreach ($recipients as $email => $name) {
            if (is_numeric($email)) {
                $email = $name;
                $name = '';
            }
            
            $success = $isHTML 
                ? $this->sendHTML($email, $subject, $body, $name)
                : $this->sendText($email, $subject, $body, $name);
                
            $results[$email] = $success;
        }
        
        return $results;
    }
    
    /**
     * Add attachment to email
     */
    public function addAttachment(string $filePath, string $name = ''): bool
    {
        try {
            if (!file_exists($filePath)) {
                error_log("Attachment file not found: " . $filePath);
                return false;
            }
            
            $this->mail->addAttachment($filePath, $name);
            return true;
        } catch (Exception $e) {
            error_log("Email Attachment Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set reply-to address
     */
    public function setReplyTo(string $email, string $name = ''): void
    {
        try {
            $this->mail->addReplyTo($email, $name);
        } catch (Exception $e) {
            error_log("Email Reply-To Error: " . $e->getMessage());
        }
    }
    
    /**
     * Add CC recipient
     */
    public function addCC(string $email, string $name = ''): void
    {
        try {
            $this->mail->addCC($email, $name);
        } catch (Exception $e) {
            error_log("Email CC Error: " . $e->getMessage());
        }
    }
    
    /**
     * Add BCC recipient
     */
    public function addBCC(string $email, string $name = ''): void
    {
        try {
            $this->mail->addBCC($email, $name);
        } catch (Exception $e) {
            error_log("Email BCC Error: " . $e->getMessage());
        }
    }
    
    /**
     * Validate email address
     */
    public function validateEmail(string $email): bool
    {
        return PHPMailer::validateAddress($email);
    }
    
    /**
     * Get last error message
     */
    public function getLastError(): string
    {
        return $this->mail->ErrorInfo;
    }
    
    /**
     * Test email configuration
     */
    public function testConnection(): bool
    {
        try {
            $this->mail->smtpConnect();
            $this->mail->smtpClose();
            return true;
        } catch (Exception $e) {
            error_log("Email Connection Test Error: " . $e->getMessage());
            return false;
        }
    }
}