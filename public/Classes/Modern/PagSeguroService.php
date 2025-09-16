<?php

declare(strict_types=1);

namespace SugoiGame\Modern;

/**
 * Modern PagSeguro API Integration
 * Replaces the old PagSeguro library with modern REST API implementation
 */
class PagSeguroService
{
    private string $email;
    private string $token;
    private string $environment;
    private string $baseUrl;
    
    public function __construct(string $email, string $token, string $environment = 'sandbox')
    {
        $this->email = $email;
        $this->token = $token;
        $this->environment = $environment;
        $this->baseUrl = $environment === 'production' 
            ? 'https://ws.pagseguro.uol.com.br'
            : 'https://ws.sandbox.pagseguro.uol.com.br';
    }
    
    /**
     * Create a new payment request
     */
    public function createPayment(array $paymentData): ?array
    {
        $xml = $this->buildPaymentXML($paymentData);
        
        $response = $this->makeRequest('/v2/checkout', $xml, 'POST');
        
        if ($response && isset($response['code'])) {
            return [
                'code' => $response['code'],
                'redirect_url' => $this->getRedirectUrl($response['code'])
            ];
        }
        
        return null;
    }
    
    /**
     * Check payment status
     */
    public function checkPaymentStatus(string $transactionCode): ?array
    {
        $url = "/v3/transactions/{$transactionCode}?email={$this->email}&token={$this->token}";
        
        return $this->makeRequest($url, null, 'GET');
    }
    
    /**
     * Process notification
     */
    public function processNotification(string $notificationCode): ?array
    {
        $url = "/v3/transactions/notifications/{$notificationCode}?email={$this->email}&token={$this->token}";
        
        return $this->makeRequest($url, null, 'GET');
    }
    
    /**
     * Get transaction details
     */
    public function getTransaction(string $transactionId): ?array
    {
        $url = "/v2/transactions/{$transactionId}?email={$this->email}&token={$this->token}";
        
        return $this->makeRequest($url, null, 'GET');
    }
    
    /**
     * Cancel a transaction (if possible)
     */
    public function cancelTransaction(string $transactionCode): ?array
    {
        $url = "/v2/transactions/cancels";
        
        $data = [
            'transactionCode' => $transactionCode,
            'email' => $this->email,
            'token' => $this->token
        ];
        
        return $this->makeRequest($url, http_build_query($data), 'POST');
    }
    
    /**
     * Build payment XML
     */
    private function buildPaymentXML(array $data): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<checkout>';
        
        // Currency (sempre BRL)
        $xml .= '<currency>BRL</currency>';
        
        // Items
        $xml .= '<items>';
        foreach ($data['items'] as $i => $item) {
            $xml .= '<item>';
            $xml .= '<id>' . ($i + 1) . '</id>';
            $xml .= '<description>' . htmlspecialchars($item['description']) . '</description>';
            $xml .= '<amount>' . number_format($item['amount'], 2, '.', '') . '</amount>';
            $xml .= '<quantity>' . (int)$item['quantity'] . '</quantity>';
            $xml .= '</item>';
        }
        $xml .= '</items>';
        
        // Reference (internal order ID)
        if (isset($data['reference'])) {
            $xml .= '<reference>' . htmlspecialchars($data['reference']) . '</reference>';
        }
        
        // Sender (customer info)
        if (isset($data['sender'])) {
            $xml .= '<sender>';
            $xml .= '<name>' . htmlspecialchars($data['sender']['name']) . '</name>';
            $xml .= '<email>' . htmlspecialchars($data['sender']['email']) . '</email>';
            
            if (isset($data['sender']['phone'])) {
                $xml .= '<phone>';
                $xml .= '<areaCode>' . substr($data['sender']['phone'], 0, 2) . '</areaCode>';
                $xml .= '<number>' . substr($data['sender']['phone'], 2) . '</number>';
                $xml .= '</phone>';
            }
            $xml .= '</sender>';
        }
        
        // Shipping (if applicable)
        if (isset($data['shipping'])) {
            $xml .= '<shipping>';
            $xml .= '<type>' . (int)$data['shipping']['type'] . '</type>';
            $xml .= '<cost>' . number_format($data['shipping']['cost'], 2, '.', '') . '</cost>';
            
            if (isset($data['shipping']['address'])) {
                $xml .= '<address>';
                $xml .= '<street>' . htmlspecialchars($data['shipping']['address']['street']) . '</street>';
                $xml .= '<number>' . htmlspecialchars($data['shipping']['address']['number']) . '</number>';
                $xml .= '<district>' . htmlspecialchars($data['shipping']['address']['district']) . '</district>';
                $xml .= '<postalCode>' . preg_replace('/\D/', '', $data['shipping']['address']['postalCode']) . '</postalCode>';
                $xml .= '<city>' . htmlspecialchars($data['shipping']['address']['city']) . '</city>';
                $xml .= '<state>' . htmlspecialchars($data['shipping']['address']['state']) . '</state>';
                $xml .= '<country>BRA</country>';
                $xml .= '</address>';
            }
            $xml .= '</shipping>';
        }
        
        // Redirect URLs
        if (isset($data['redirectURL'])) {
            $xml .= '<redirectURL>' . htmlspecialchars($data['redirectURL']) . '</redirectURL>';
        }
        
        if (isset($data['notificationURL'])) {
            $xml .= '<notificationURL>' . htmlspecialchars($data['notificationURL']) . '</notificationURL>';
        }
        
        $xml .= '</checkout>';
        
        return $xml;
    }
    
    /**
     * Make HTTP request to PagSeguro API
     */
    private function makeRequest(string $endpoint, ?string $data = null, string $method = 'GET'): ?array
    {
        $url = $this->baseUrl . $endpoint;
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/xml; charset=UTF-8',
                'Accept: application/vnd.pagseguro.com.br.v3+xml'
            ]
        ]);
        
        if ($method === 'POST' && $data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            error_log("PagSeguro cURL Error: " . $error);
            return null;
        }
        
        if ($httpCode >= 400) {
            error_log("PagSeguro HTTP Error: " . $httpCode . " - " . $response);
            return null;
        }
        
        // Parse XML response
        if ($response) {
            return $this->parseXMLResponse($response);
        }
        
        return null;
    }
    
    /**
     * Parse XML response from PagSeguro
     */
    private function parseXMLResponse(string $xmlString): ?array
    {
        try {
            $xml = simplexml_load_string($xmlString);
            if ($xml === false) {
                return null;
            }
            
            return json_decode(json_encode($xml), true);
        } catch (\Exception $e) {
            error_log("PagSeguro XML Parse Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get redirect URL for payment
     */
    private function getRedirectUrl(string $code): string
    {
        $baseRedirectUrl = $this->environment === 'production'
            ? 'https://pagseguro.uol.com.br/v2/checkout/payment.html'
            : 'https://sandbox.pagseguro.uol.com.br/v2/checkout/payment.html';
            
        return $baseRedirectUrl . '?code=' . $code;
    }
    
    /**
     * Validate notification data
     */
    public function validateNotification(array $postData): bool
    {
        return isset($postData['notificationCode']) && 
               isset($postData['notificationType']) &&
               $postData['notificationType'] === 'transaction';
    }
    
    /**
     * Get payment status description
     */
    public function getStatusDescription(int $status): string
    {
        $statuses = [
            1 => 'Aguardando pagamento',
            2 => 'Em análise',
            3 => 'Paga',
            4 => 'Disponível',
            5 => 'Em disputa',
            6 => 'Devolvida',
            7 => 'Cancelada'
        ];
        
        return $statuses[$status] ?? 'Status desconhecido';
    }
}