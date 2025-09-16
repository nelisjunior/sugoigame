<?php

declare(strict_types=1);

namespace SugoiGame\Modern;

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

/**
 * Modern Stripe Integration for PHP 8.x
 * Replaces the old Stripe 6.x implementation
 */
class StripeService
{
    private StripeClient $stripe;
    private string $webhookSecret;
    
    public function __construct(string $secretKey, string $webhookSecret = '')
    {
        $this->stripe = new StripeClient($secretKey);
        $this->webhookSecret = $webhookSecret;
    }
    
    /**
     * Create a payment intent for the given amount
     */
    public function createPaymentIntent(
        int $amount,
        string $currency = 'brl',
        array $metadata = []
    ): ?array {
        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => $metadata,
            ]);
            
            return $paymentIntent->toArray();
        } catch (ApiErrorException $e) {
            error_log("Stripe Payment Intent Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a customer
     */
    public function createCustomer(string $email, string $name = '', array $metadata = []): ?array
    {
        try {
            $customer = $this->stripe->customers->create([
                'email' => $email,
                'name' => $name,
                'metadata' => $metadata,
            ]);
            
            return $customer->toArray();
        } catch (ApiErrorException $e) {
            error_log("Stripe Customer Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Retrieve a payment intent
     */
    public function getPaymentIntent(string $paymentIntentId): ?array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            return $paymentIntent->toArray();
        } catch (ApiErrorException $e) {
            error_log("Stripe Retrieve Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Confirm a payment intent
     */
    public function confirmPaymentIntent(string $paymentIntentId, array $params = []): ?array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->confirm($paymentIntentId, $params);
            return $paymentIntent->toArray();
        } catch (ApiErrorException $e) {
            error_log("Stripe Confirm Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cancel a payment intent
     */
    public function cancelPaymentIntent(string $paymentIntentId): ?array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->cancel($paymentIntentId);
            return $paymentIntent->toArray();
        } catch (ApiErrorException $e) {
            error_log("Stripe Cancel Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a subscription
     */
    public function createSubscription(string $customerId, string $priceId, array $metadata = []): ?array
    {
        try {
            $subscription = $this->stripe->subscriptions->create([
                'customer' => $customerId,
                'items' => [['price' => $priceId]],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => $metadata,
            ]);
            
            return $subscription->toArray();
        } catch (ApiErrorException $e) {
            error_log("Stripe Subscription Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            \Stripe\Webhook::constructEvent($payload, $signature, $this->webhookSecret);
            return true;
        } catch (\Exception $e) {
            error_log("Stripe Webhook Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process webhook event
     */
    public function processWebhookEvent(string $payload, string $signature): ?array
    {
        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $this->webhookSecret);
            return $event->toArray();
        } catch (\Exception $e) {
            error_log("Stripe Webhook Processing Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * List all payment methods for a customer
     */
    public function getCustomerPaymentMethods(string $customerId, string $type = 'card'): array
    {
        try {
            $paymentMethods = $this->stripe->paymentMethods->all([
                'customer' => $customerId,
                'type' => $type,
            ]);
            
            return $paymentMethods->toArray();
        } catch (ApiErrorException $e) {
            error_log("Stripe Payment Methods Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create a refund
     */
    public function createRefund(string $paymentIntentId, ?int $amount = null): ?array
    {
        try {
            $params = ['payment_intent' => $paymentIntentId];
            if ($amount !== null) {
                $params['amount'] = $amount;
            }
            
            $refund = $this->stripe->refunds->create($params);
            return $refund->toArray();
        } catch (ApiErrorException $e) {
            error_log("Stripe Refund Error: " . $e->getMessage());
            return null;
        }
    }
}