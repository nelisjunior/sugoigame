<?php

declare(strict_types=1);

require "../../Includes/conectdb.php";
require_once "../../Classes/Modern/PagSeguroService.php";
require_once "../../Classes/Modern/ConfigManager.php";

use SugoiGame\Modern\PagSeguroService;
use SugoiGame\Modern\ConfigManager;

try {
    $protector->need_conta();

    $idPlano = (int)base64_decode($_GET['plano'] ?? '');
    
    if (!$idPlano) {
        header("Location: ../../?ses=vipComprar&msg=" . urlencode('Plano inválido!'));
        exit;
    }

    // Buscar dados do plano
    $result = $connection->run("SELECT * FROM tb_vip_planos WHERE id = ?", 'i', [$idPlano]);
    if ($result->count() < 1) {
        header("Location: ../../?ses=vipComprar&msg=" . urlencode('Escolha um plano válido!'));
        exit;
    }

    $plano = $result->fetch();

    // Configurar serviço PagSeguro moderno
    $config = new ConfigManager();
    $pagSeguroService = new PagSeguroService([
        'email' => $config->get('pagseguro.email'),
        'token' => $config->get('pagseguro.token'),
        'sandbox' => $config->get('pagseguro.sandbox', false)
    ]);

    // Inserir log de compra
    $connection->run(
        "INSERT INTO tb_vip_compras (conta_id, plano_id, gateway, valor, moeda) VALUES (?, ?, ?, ?, ?)",
        "iisds",
        [
            $userDetails->conta['conta_id'],
            $plano['id'],
            'PagSeguro_Modern',
            $plano['valor_brl'],
            'BRL'
        ]
    );

    $reference = $connection->insertID();

    // Criar pedido no PagSeguro
    $orderData = [
        'reference_id' => (string)$reference,
        'items' => [
            [
                'name' => 'Sugoi Game - ' . $plano['nome'],
                'quantity' => 1,
                'unit_amount' => (int)($plano['valor_brl'] * 100) // Centavos
            ]
        ],
        'notification_urls' => [
            'https://' . $_SERVER['HTTP_HOST'] . '/Scripts/PagSeguro/retorno_modern.php'
        ],
        'redirect_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/?ses=vipComprar&complete=1&reference=' . $reference,
        'customer' => [
            'name' => $userDetails->conta['nome'] ?? 'Cliente',
            'email' => $userDetails->conta['email'],
            'tax_id' => '', // CPF se disponível
            'phones' => [
                [
                    'country' => '55',
                    'area' => '11',
                    'number' => '999999999'
                ]
            ]
        ]
    ];

    // Criar ordem no PagSeguro
    $orderResult = $pagSeguroService->createOrder($orderData);

    if ($orderResult['success']) {
        $order = $orderResult['data'];
        
        // Salvar order ID para tracking
        $connection->run(
            "UPDATE tb_vip_compras SET pagseguro_order_id = ? WHERE id = ?",
            "si",
            [$order['id'], $reference]
        );

        // Redirecionar para checkout
        foreach ($order['links'] as $link) {
            if ($link['rel'] === 'PAYMENT_PAGE') {
                header("Location: " . $link['href']);
                exit;
            }
        }
        
        throw new Exception('Link de pagamento não encontrado na resposta do PagSeguro');
        
    } else {
        throw new Exception('Erro ao criar pedido no PagSeguro: ' . $orderResult['error']);
    }

} catch (Exception $e) {
    error_log("Erro no PagSeguro moderno: " . $e->getMessage());
    
    // Fallback para método antigo em caso de erro
    try {
        require_once('../../Includes/PagSeguro/PagSeguroLibrary.php');
        
        $paymentRequest = new PagSeguroPaymentRequest();
        $paymentRequest->addItem($plano['id'], 'Sugoi Game - ' . $plano['nome'], 1, $plano['valor_brl']);
        $paymentRequest->setCurrency('BRL');
        $paymentRequest->setRedirectURL('https://' . $_SERVER['HTTP_HOST'] . '/?ses=vipComprar&complete');
        $paymentRequest->addParameter('notificationURL', 'https://' . $_SERVER['HTTP_HOST'] . '/Scripts/PagSeguro/retorno.php');
        $paymentRequest->setReference($reference ?? $connection->insertID());
        
        $credentials = PagSeguroConfig::getAccountCredentials();
        $checkoutUrl = $paymentRequest->register($credentials);
        
        header("Location: " . $checkoutUrl);
        exit;
        
    } catch (Exception $fallbackError) {
        error_log("Erro no fallback PagSeguro: " . $fallbackError->getMessage());
        header("Location: ../../?ses=vipComprar&msg=" . urlencode('Erro no processamento do pagamento. Tente novamente.'));
        exit;
    }
}