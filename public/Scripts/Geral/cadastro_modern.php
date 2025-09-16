<?php

declare(strict_types=1);

require "../../Includes/conectdb.php";
require_once "../../Classes/Modern/EmailService.php";
require_once "../../Classes/Modern/ConfigManager.php";

use SugoiGame\Modern\EmailService;
use SugoiGame\Modern\ConfigManager;

try {
    $protector->reject_conta();

    // Validar dados de entrada
    $nome = $protector->get_string_or_exit('nome');
    $email = $protector->get_string_or_exit('email');
    $senha = $protector->get_string_or_exit('senha');
    $confirmarSenha = $protector->get_string_or_exit('confirmarSenha');
    $padrinho = $protector->get_string_or_exit('padrinho', false);

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../?ses=cadastro&msg=" . urlencode('Email inválido!'));
        exit;
    }

    // Validar senhas
    if ($senha !== $confirmarSenha) {
        header("Location: ../../?ses=cadastro&msg=" . urlencode('As senhas não coincidem!'));
        exit;
    }

    if (strlen($senha) < 6) {
        header("Location: ../../?ses=cadastro&msg=" . urlencode('A senha deve ter pelo menos 6 caracteres!'));
        exit;
    }

    // Verificar se email já existe
    $result = $connection->run("SELECT COUNT(*) as count FROM tb_conta WHERE email = ?", "s", [$email]);
    if ($result->fetch()['count'] > 0) {
        header("Location: ../../?ses=cadastro&msg=" . urlencode('Este email já está em uso!'));
        exit;
    }

    // Gerar dados seguros
    $senhaHash = password_hash($senha, PASSWORD_ARGON2ID);
    $ativacao = bin2hex(random_bytes(16)); // 32 caracteres hex
    $u_id = uniqid('user_', true);

    // Inserir conta no banco
    $connection->run(
        "INSERT INTO tb_conta (nome, email, senha, id_encrip, ativacao, ativo) VALUES (?, ?, ?, ?, ?, 0)",
        "sssss",
        [$nome, $email, $senhaHash, $u_id, $ativacao]
    );

    $contaId = $connection->insertID();

    // Processar padrinho se informado
    if ($padrinho) {
        $result = $connection->run("SELECT conta_id FROM tb_conta WHERE id_encrip = ?", "s", [$padrinho]);
        if ($result->count() > 0) {
            $padrinhoInfo = $result->fetch();
            $connection->run(
                "INSERT INTO tb_afilhados (id, afilhado) VALUES (?, ?)",
                "ii",
                [$padrinhoInfo["conta_id"], $contaId]
            );
        }
    }

    // Configurar serviço de email moderno
    $config = new ConfigManager();
    $emailService = new EmailService([
        'host' => $config->get('email.smtp.host'),
        'username' => $config->get('email.smtp.username'),
        'password' => $config->get('email.smtp.password'),
        'port' => $config->get('email.smtp.port'),
        'encryption' => $config->get('email.smtp.encryption')
    ]);

    // Preparar email de ativação
    $activationUrl = "https://www.sugoigame.com.br/Scripts/Geral/ativar_id.php?i=$u_id&cod=$ativacao";
    
    $emailTemplate = '
    <div style="margin: 0 auto; background: #F5F5F5; border-radius: 5px; width: 520px; border: 1px dotted #D8D8D8; border-left: 4px solid #CE3233; border-right: 4px solid #CE3233;">
        <table width="100%" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <div style="padding: 20px; font-size: 14px; font-family: Arial, Helvetica, sans-serif; line-height: 1.6;">
                        <h2 style="color: #CE3233; margin-top: 0;">Bem-vindo ao Sugoi Game!</h2>
                        
                        <p>Olá <strong>' . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . '</strong>,</p>
                        
                        <p>Sua conta foi criada com sucesso! Para começar a jogar, você precisa ativar sua conta.</p>
                        
                        <div style="background: #fff; border: 1px solid #ddd; padding: 15px; margin: 20px 0; border-radius: 5px;">
                            <p><strong>Código de ativação:</strong></p>
                            <p style="font-size: 18px; font-weight: bold; color: #CE3233; letter-spacing: 2px;">' . $ativacao . '</p>
                        </div>
                        
                        <p>Ou clique no link abaixo para ativar automaticamente:</p>
                        <p><a href="' . $activationUrl . '" style="color: #CE3233; text-decoration: none; font-weight: bold;" target="_blank">Ativar minha conta</a></p>
                        
                        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
                        
                        <p style="font-size: 12px; color: #666;">
                            Se você não criou esta conta, pode ignorar este email com segurança.
                        </p>
                    </div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <div style="background: rgba(0, 0, 0, .5); margin-top: 10px; padding: 15px; font-size: 12px; font-family: Arial, Helvetica, sans-serif;">
                        <strong style="color: #FFF;">&copy; 2017 - ' . date("Y") . ' - Sugoi Game | Todos os direitos reservados.</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>';

    // Enviar email
    $emailResult = $emailService->sendEmail(
        $email,
        $nome,
        'Sugoi Game - Ativação de Conta',
        $emailTemplate,
        'cadastro@sugoigame.com.br',
        'Sugoi Game'
    );

    if ($emailResult['success']) {
        header("Location: ../../?ses=cadastrosucess&msg=" . urlencode('Conta criada com sucesso! Verifique seu email.'));
    } else {
        // Log do erro mas não falha o cadastro
        error_log("Erro ao enviar email de ativação: " . $emailResult['error']);
        header("Location: ../../?ses=cadastrosucess&msg=" . urlencode('Conta criada! Código de ativação: ' . $ativacao));
    }

} catch (Exception $e) {
    error_log("Erro no cadastro moderno: " . $e->getMessage());
    
    // Fallback para o processo antigo em caso de erro crítico
    header("Location: ../../?ses=cadastro&msg=" . urlencode('Erro interno. Tente novamente.'));
    exit;
}