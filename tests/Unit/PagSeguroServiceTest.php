<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SugoiGame\Modern\PagSeguroService;

/**
 * Test suite for PagSeguroService
 */
class PagSeguroServiceTest extends TestCase
{
    private PagSeguroService $pagSeguroService;
    
    protected function setUp(): void
    {
        // Create service with test credentials
        $this->pagSeguroService = new PagSeguroService(
            'test@example.com',
            'test_token',
            'sandbox'
        );
    }
    
    public function testCanInstantiatePagSeguroService(): void
    {
        $this->assertInstanceOf(PagSeguroService::class, $this->pagSeguroService);
    }
    
    public function testCreatePaymentWithValidData(): void
    {
        $paymentData = [
            'items' => [
                [
                    'description' => 'Test Item',
                    'amount' => 100.00,
                    'quantity' => 1
                ]
            ],
            'reference' => 'TEST_ORDER_001',
            'sender' => [
                'name' => 'Test User',
                'email' => 'user@example.com',
                'phone' => '11999999999'
            ],
            'redirectURL' => 'https://example.com/success',
            'notificationURL' => 'https://example.com/notification'
        ];
        
        // This will likely fail without real credentials, but tests structure
        $result = $this->pagSeguroService->createPayment($paymentData);
        
        // Should return null with test credentials
        $this->assertNull($result);
    }
    
    public function testCheckPaymentStatusWithInvalidCode(): void
    {
        $result = $this->pagSeguroService->checkPaymentStatus('invalid_transaction_code');
        
        // Should return null for invalid code
        $this->assertNull($result);
    }
    
    public function testProcessNotificationWithInvalidCode(): void
    {
        $result = $this->pagSeguroService->processNotification('invalid_notification_code');
        
        // Should return null for invalid code
        $this->assertNull($result);
    }
    
    public function testGetTransactionWithInvalidId(): void
    {
        $result = $this->pagSeguroService->getTransaction('invalid_transaction_id');
        
        // Should return null for invalid ID
        $this->assertNull($result);
    }
    
    public function testCancelTransactionWithInvalidCode(): void
    {
        $result = $this->pagSeguroService->cancelTransaction('invalid_transaction_code');
        
        // Should return null for invalid code
        $this->assertNull($result);
    }
    
    public function testValidateNotificationWithValidData(): void
    {
        $validData = [
            'notificationCode' => 'ABC123',
            'notificationType' => 'transaction'
        ];
        
        $result = $this->pagSeguroService->validateNotification($validData);
        
        $this->assertTrue($result);
    }
    
    public function testValidateNotificationWithInvalidData(): void
    {
        $invalidData = [
            'notificationCode' => 'ABC123'
            // Missing notificationType
        ];
        
        $result = $this->pagSeguroService->validateNotification($invalidData);
        
        $this->assertFalse($result);
    }
    
    public function testValidateNotificationWithWrongType(): void
    {
        $invalidData = [
            'notificationCode' => 'ABC123',
            'notificationType' => 'invalid_type'
        ];
        
        $result = $this->pagSeguroService->validateNotification($invalidData);
        
        $this->assertFalse($result);
    }
    
    public function testGetStatusDescriptionForKnownStatuses(): void
    {
        $this->assertEquals('Aguardando pagamento', $this->pagSeguroService->getStatusDescription(1));
        $this->assertEquals('Em análise', $this->pagSeguroService->getStatusDescription(2));
        $this->assertEquals('Paga', $this->pagSeguroService->getStatusDescription(3));
        $this->assertEquals('Disponível', $this->pagSeguroService->getStatusDescription(4));
        $this->assertEquals('Em disputa', $this->pagSeguroService->getStatusDescription(5));
        $this->assertEquals('Devolvida', $this->pagSeguroService->getStatusDescription(6));
        $this->assertEquals('Cancelada', $this->pagSeguroService->getStatusDescription(7));
    }
    
    public function testGetStatusDescriptionForUnknownStatus(): void
    {
        $result = $this->pagSeguroService->getStatusDescription(999);
        
        $this->assertEquals('Status desconhecido', $result);
    }
    
    public function testGetStatusDescriptionReturnsString(): void
    {
        $result = $this->pagSeguroService->getStatusDescription(1);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
}