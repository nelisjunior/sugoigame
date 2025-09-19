<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SugoiGame\Modern\StripeService;

/**
 * Test suite for StripeService
 */
class StripeServiceTest extends TestCase
{
    private StripeService $stripeService;
    
    protected function setUp(): void
    {
        // Use test API key
        $this->stripeService = new StripeService('sk_test_fake_key_for_testing');
    }
    
    public function testCanInstantiateStripeService(): void
    {
        $this->assertInstanceOf(StripeService::class, $this->stripeService);
    }
    
    public function testCreatePaymentIntentReturnsNullOnInvalidKey(): void
    {
        // This will fail with invalid API key, but tests the method structure
        $result = $this->stripeService->createPaymentIntent(1000, 'brl');
        
        // With invalid key, should return null due to API error
        $this->assertNull($result);
    }
    
    public function testCreateCustomerWithValidData(): void
    {
        // Test method signature and basic validation
        $result = $this->stripeService->createCustomer('test@example.com', 'Test User');
        
        // With invalid key, should return null
        $this->assertNull($result);
    }
    
    public function testGetPaymentIntentWithInvalidId(): void
    {
        $result = $this->stripeService->getPaymentIntent('invalid_payment_intent_id');
        
        // Should return null for invalid ID
        $this->assertNull($result);
    }
    
    public function testConfirmPaymentIntentWithInvalidId(): void
    {
        $result = $this->stripeService->confirmPaymentIntent('invalid_payment_intent_id');
        
        // Should return null for invalid ID
        $this->assertNull($result);
    }
    
    public function testCancelPaymentIntentWithInvalidId(): void
    {
        $result = $this->stripeService->cancelPaymentIntent('invalid_payment_intent_id');
        
        // Should return null for invalid ID
        $this->assertNull($result);
    }
    
    public function testCreateSubscriptionWithInvalidData(): void
    {
        $result = $this->stripeService->createSubscription('invalid_customer_id', 'invalid_price_id');
        
        // Should return null for invalid data
        $this->assertNull($result);
    }
    
    public function testVerifyWebhookSignatureWithInvalidData(): void
    {
        $result = $this->stripeService->verifyWebhookSignature('invalid_payload', 'invalid_signature');
        
        // Should return false for invalid signature
        $this->assertFalse($result);
    }
    
    public function testProcessWebhookEventWithInvalidData(): void
    {
        $result = $this->stripeService->processWebhookEvent('invalid_payload', 'invalid_signature');
        
        // Should return null for invalid event
        $this->assertNull($result);
    }
    
    public function testGetCustomerPaymentMethodsWithInvalidCustomer(): void
    {
        $result = $this->stripeService->getCustomerPaymentMethods('invalid_customer_id');
        
        // Should return empty array for invalid customer
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
    
    public function testCreateRefundWithInvalidPaymentIntent(): void
    {
        $result = $this->stripeService->createRefund('invalid_payment_intent_id');
        
        // Should return null for invalid payment intent
        $this->assertNull($result);
    }
}