<?php
require "Includes/conectdb.php";

if (! $userDetails->conta &&
    ! isset($_GET["ses"]) &&
    ! isset($_GET["erro"]) &&
    ! isset($_GET["msg"]) &&
    ! isset($_GET["msg2"])
) {
    header("location: ./login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Sugoi Game - One Piece MMORPG</title>
    <meta charset="utf-8" />
    <meta name="description" content="Um RPG estratégico cheio de PvP feito por fãs de One Piece." />

    <meta property="og:url" content="https://sugoigame.com.br/" />
    <meta property="og:title" content="Pirata ou Marinheiro? Crie sua própria história e viva novas aventuras!" />
    <meta property="og:site_name" content="Sugoi Game - One Piece MMORPG" />
    <meta property="og:description"
        content="Sugoi Game é um MMORPG estratégico gratuito cheio de PvP feito por fãs de One Piece. Jogue agora!" />
    <meta property="og:image" content="https://sugoigame.com.br/Imagens/Banners/banner.jpg" />
    <meta property="og:image:type" content="image/jpeg" />

    <link rel="manifest" href="manifest.json" />
    <link rel="shortcut icon" type="image/png" href="Imagens/favicon.png" />

    <link rel="stylesheet" type="text/css" href="CSS/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="CSS/theme.css?ver=1.0.4" />
    <link rel="stylesheet" type="text/css" href="CSS/bootstrap-select.min.css" />
    <link rel="stylesheet" type="text/css" href="CSS/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="CSS/jquery.bracket.min.css" />
    <link rel="stylesheet" type="text/css" href="CSS/estrutura.css?ver=2.0.16" />

    <?php if ($_SERVER['HTTP_HOST'] == 'sugoigame.com.br') { ?>
        <script data-ad-client="ca-pub-6665062829379662" async
            src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <?php } ?>
</head>

<body>
    <div id="world-map-background"></div>

    <audio id="toque_nova_msg">
        <source src="Sons/nova_msg.ogg" type="audio/ogg" />
        <source src="Sons/nova_msg.mp3" type="audio/mpeg" />
    </audio>

    <div id="tudo">
        <img src="Imagens/carregando.gif" />
        <?php if ($userDetails->tripulacao) : ?>
            <input type="hidden" id="ilha_atual" value="<?= $userDetails->ilha["ilha"]; ?>" />
            <input type="hidden" id="coord_x_navio" value="<?= $userDetails->tripulacao["x"]; ?>" />
            <input type="hidden" id="coord_y_navio" value="<?= $userDetails->tripulacao["y"]; ?>" />
        <?php endif; ?>
    </div>

    <button id="fullscreen" title="Tela cheia" data-toggle="tooltip" data-placement="top" data-trigger="hover">
        <i class="fa fa-arrows-alt"></i>
    </button>

    <!-- Modais globais que precisam permanecer abertas mesmo caso haja mudança de sessao.
        Como a mudanca de sessao desencadeia uma atualizacao do html da pagina, essas
        modals seriam fechadas se ficassem dentro do header.php, por isso elas ficam no contexto global. -->
    <?php include "Includes/Components/Modals/modal_mensagens.php"; ?>

    <?php include "Includes/Components/Modals/modal_chat.php"; ?>

    <?php include "Includes/Components/Modals/modal_inventario.php"; ?>

    <?php include "Includes/Components/Modals/modal_dar_comida.php"; ?>

    <?php include "Includes/Components/Modals/modal_cartografo.php"; ?>

    <?php include "Includes/Components/Modals/modal_daily_gift.php"; ?>

    <?php include "Includes/Components/Modals/modal_send_message.php"; ?>

    <?php include "Includes/Components/Modals/modal_relatorio_combate.php"; ?>

    <div id="icon_carregando">
        <div class="text-center">
            <img src="Imagens/carregando.gif" />
            <div>
                Carregando...
            </div>
        </div>
    </div>

    <script type="text/javascript" src="JS/jquery-2.2.2.min.js"></script>
    <script type="text/javascript" src="JS/bootstrap.min.js"></script>
    <script type="text/javascript" src="JS/bootbox.min.js"></script>
    <script type="text/javascript" src="JS/bootstrap-select.min.js"></script>
    <script type="text/javascript" src="JS/progressbar.min.js"></script>
    <script type="text/javascript" src="JS/reconnecting-websocket.min.js"></script>
    <script type="text/javascript" src="JS/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="JS/phaser.min.js"></script>
    <script type="text/javascript" src="JS/jquery.bracket.min.js"></script>
    <script type="text/javascript" src="JS/jquery.mobile-1.4.5.min.js"></script>
    <script type="text/javascript" src="JS/easystar-0.4.4.min.js"></script>
    <script type="text/javascript" src="JS/library.js?ver=1.0.3"></script>
    <script type="text/javascript" src="JS/main.js?ver=1.0.3"></script>

    <script src="https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v2.9"></script>
</body>

</html>
