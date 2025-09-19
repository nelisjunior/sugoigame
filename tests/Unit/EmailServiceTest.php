<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SugoiGame\Modern\EmailService;

/**
 * Test suite for EmailService
 */
class EmailServiceTest extends TestCase
{
    private EmailService $emailService;
    
    protected function setUp(): void
    {
        // Create service with test configuration
        $this->emailService = new EmailService([
            'host' => 'localhost',
            'port' => 25,
            'username' => '',
            'password' => '',
            'from_email' => 'test@example.com',
            'from_name' => 'Test Sender',
            'debug_level' => 0,
        ]);
    }
    
    public function testCanInstantiateEmailService(): void
    {
        $this->assertInstanceOf(EmailService::class, $this->emailService);
    }
    
    public function testValidateEmailWithValidAddress(): void
    {
        $this->assertTrue($this->emailService->validateEmail('test@example.com'));
        $this->assertTrue($this->emailService->validateEmail('user+tag@domain.co.uk'));
    }
    
    public function testValidateEmailWithInvalidAddress(): void
    {
        $this->assertFalse($this->emailService->validateEmail('invalid-email'));
        $this->assertFalse($this->emailService->validateEmail(''));
        $this->assertFalse($this->emailService->validateEmail('@domain.com'));
        $this->assertFalse($this->emailService->validateEmail('user@'));
    }
    
    public function testSendTextEmailStructure(): void
    {
        // This won't actually send without proper SMTP setup, but tests structure
        $result = $this->emailService->sendText(
            'test@example.com',
            'Test Subject',
            'Test body content'
        );
        
        // Will likely fail without real SMTP, but method should return boolean
        $this->assertIsBool($result);
    }
    
    public function testSendHTMLEmailStructure(): void
    {
        $result = $this->emailService->sendHTML(
            'test@example.com',
            'Test Subject',
            '<h1>Test HTML Content</h1>',
            'Test User'
        );
        
        // Will likely fail without real SMTP, but method should return boolean
        $this->assertIsBool($result);
    }
    
    public function testSendBulkEmailStructure(): void
    {
        $recipients = [
            'user1@example.com' => 'User One',
            'user2@example.com' => 'User Two',
            'user3@example.com' // Without name
        ];
        
        $results = $this->emailService->sendBulk(
            $recipients,
            'Bulk Test Subject',
            'Bulk test content'
        );
        
        $this->assertIsArray($results);
        $this->assertCount(3, $results);
        
        // Each result should be a boolean
        foreach ($results as $email => $result) {
            $this->assertIsString($email);
            $this->assertIsBool($result);
        }
    }
    
    public function testAddAttachmentWithNonExistentFile(): void
    {
        $result = $this->emailService->addAttachment('/path/to/nonexistent/file.txt');
        
        // Should return false for non-existent file
        $this->assertFalse($result);
    }
    
    public function testAddAttachmentWithExistentFile(): void
    {
        // Create a temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'email_test');
        file_put_contents($tempFile, 'Test attachment content');
        
        $result = $this->emailService->addAttachment($tempFile, 'test.txt');
        
        // Should return true for existing file
        $this->assertTrue($result);
        
        // Clean up
        unlink($tempFile);
    }
    
    public function testSetReplyToDoesNotThrow(): void
    {
        // Should not throw exceptions
        $this->expectNotToPerformAssertions();
        
        $this->emailService->setReplyTo('reply@example.com', 'Reply Name');
    }
    
    public function testAddCCDoesNotThrow(): void
    {
        // Should not throw exceptions
        $this->expectNotToPerformAssertions();
        
        $this->emailService->addCC('cc@example.com', 'CC Name');
    }
    
    public function testAddBCCDoesNotThrow(): void
    {
        // Should not throw exceptions
        $this->expectNotToPerformAssertions();
        
        $this->emailService->addBCC('bcc@example.com', 'BCC Name');
    }
    
    public function testGetLastErrorReturnsString(): void
    {
        $error = $this->emailService->getLastError();
        
        $this->assertIsString($error);
    }
    
    public function testTestConnectionStructure(): void
    {
        // Will likely fail without real SMTP, but should return boolean
        $result = $this->emailService->testConnection();
        
        $this->assertIsBool($result);
    }
}