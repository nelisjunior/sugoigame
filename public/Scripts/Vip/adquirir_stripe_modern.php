<?php

declare(strict_types=1);

require "../../Includes/conectdb.php";
require_once "../../Classes/Modern/StripeService.php";
require_once "../../Classes/Modern/ConfigManager.php";

use SugoiGame\Modern\StripeService;
use SugoiGame\Modern\ConfigManager;

try {
    $protector->need_conta();

    $idPlano = $protector->get_number_or_exit('plano');
    $currency = $protector->get_enum_or_exit('currency', ["brl", "usd", "eur"]);

    // Buscar dados do plano
    $result = $connection->run("SELECT * FROM tb_vip_planos WHERE id = ?", 'i', [$idPlano]);
    if ($result->count() < 1) {
        header("Location: ../../?ses=vipComprar&msg=" . urlencode('Escolha um plano válido!') . "&");
        exit;
    }

    $plano = $result->fetch();
    
    // Configurar serviço Stripe moderno
    $config = new ConfigManager();
    $stripeService = new StripeService($config->get('stripe.secret_key'));

    // Determinar valor baseado na moeda
    $valorCampo = match($currency) {
        'brl' => 'valor_brl',
        'usd' => 'valor_usd', 
        'eur' => 'valor_eur',
        default => 'valor_brl'
    };

    $valor = (int)($plano[$valorCampo] * 100); // Stripe usa centavos

    // Inserir log de compra
    $connection->run("INSERT INTO tb_vip_compras (conta_id, plano_id, gateway, valor, moeda) VALUES (?, ?, ?, ?, ?)", 
        "iisds", [
            $userDetails->conta['conta_id'],
            $plano['id'],
            "Stripe_Modern_$currency",
            $plano[$valorCampo],
            $currency
        ]
    );

    $reference = $connection->last_id();

    // Criar Payment Intent com novo serviço
    $paymentResult = $stripeService->createPaymentIntent(
        $valor,
        $currency,
        [
            'account_id' => $userDetails->conta['conta_id'],
            'plano_id' => $plano['id'],
            'reference' => $reference,
            'email' => $userDetails->conta["email"]
        ]
    );

    if ($paymentResult['success']) {
        // Armazenar Payment Intent ID para confirmação posterior
        $connection->run("UPDATE tb_vip_compras SET stripe_payment_intent_id = ? WHERE id = ?",
            "si", [$paymentResult['data']->id, $reference]
        );

        // Redirecionar para checkout do Stripe (usando Hosted Checkout)
        $checkoutSession = $stripeService->createCheckoutSession(
            $plano['nome'],
            $valor,
            $currency,
            "https://" . $_SERVER['HTTP_HOST'] . "/?ses=vipComprar&success=1&reference=" . $reference,
            "https://" . $_SERVER['HTTP_HOST'] . "/?ses=vipComprar&cancelled=1",
            [
                'reference' => (string)$reference,
                'account_id' => (string)$userDetails->conta['conta_id']
            ]
        );

        if ($checkoutSession['success']) {
            header("Location: " . $checkoutSession['data']->url);
            exit;
        } else {
            throw new Exception('Erro ao criar sessão de checkout: ' . $checkoutSession['error']);
        }
    } else {
        throw new Exception('Erro ao criar Payment Intent: ' . $paymentResult['error']);
    }

} catch (Exception $e) {
    error_log("Erro no Stripe moderno: " . $e->getMessage());
    
    // Fallback para método antigo em caso de erro
    if (isset($plano) && isset($currency) && isset($reference)) {
        header("Location: " . $plano["stripe_checkout_url_" . $currency] . "?client_reference_id=" . $reference . "&prefilled_email=" . $userDetails->conta["email"]);
        exit;
    }
    
    header("Location: ../../?ses=vipComprar&msg=" . urlencode('Erro interno. Tente novamente.') . "&error=stripe");
    exit;
}