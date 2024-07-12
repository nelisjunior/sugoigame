<?php
$my_id = $userDetails->combate_pvp["id_1"] == $userDetails->tripulacao["id"] ? "1" : "2";
$ini_id = $userDetails->combate_pvp["id_1"] == $userDetails->tripulacao["id"] ? "2" : "1";

$personagens_combate["1"] = get_pers_in_combate($userDetails->combate_pvp["id_1"]);
$personagens_combate["2"] = get_pers_in_combate($userDetails->combate_pvp["id_2"]);

$perdeu = TRUE;
$venceu = TRUE;

$tabuleiro = [];

$obstaculos = $connection->run("SELECT * FROM tb_obstaculos WHERE tripulacao_id = ? AND tipo = 1",
    "i", array($userDetails->combate_pvp["id_1"]))->fetch_all_array();
foreach ($obstaculos as $obstaculo) {
    $tabuleiro[$obstaculo["x"]][$obstaculo["y"]] = obstaculo_para_tabuleiro($obstaculo);
}

$obstaculos = $connection->run("SELECT * FROM tb_obstaculos WHERE tripulacao_id = ? AND tipo = 2",
    "i", array($userDetails->combate_pvp["id_2"]))->fetch_all_array();
foreach ($obstaculos as $obstaculo) {
    $tabuleiro[$obstaculo["x"]][$obstaculo["y"]] = obstaculo_para_tabuleiro($obstaculo);
}

foreach ($personagens_combate[$my_id] as $pers) {
    if ($pers["hp"]) {
        $perdeu = FALSE;
        $tabuleiro[$pers["quadro_x"]][$pers["quadro_y"]] = $pers;
    }
}
foreach ($personagens_combate[$ini_id] as $pers) {
    if ($pers["hp"]) {
        $venceu = FALSE;
        $tabuleiro[$pers["quadro_x"]][$pers["quadro_y"]] = $pers;
    }
}

$special_effects = get_special_effects($userDetails->combate_pvp["id_1"], $userDetails->combate_pvp["id_2"]);

?>
<?php render_battle_heading(); ?>
<div id="navio_batalha">
    <?php if ($venceu) : ?>
        <div id="fim_batalha">
            <h2>Você Venceu.</h2>
            <p>
                Você derrotou seu inimigo. Agora você nao poderá ser atacado enquanto não mudar de coordenada,
                aproveite para recuperar sua tripulação
            </p>
            <p>
                OBS: Se você fizer parte de uma aliança/frota que estiver em guerra contra outra aliança/frota você poderá
                ser atacado normalmente.
            </p>
            <div>
                <button href="link_Batalha/batalha_fim.php" class="link_send btn btn-success">
                    Finalizar
                </button>
            </div>
        </div>
    <?php elseif ($perdeu) : ?>
        <div id="fim_batalha">
            <h2>Você foi derrotado.</h2>
            <div>
                <button href="link_Batalha/batalha_fim.php" class="link_send btn btn-info">
                    Finalizar
                </button>
            </div>
        </div>
    <?php else : ?>
        <div id="batalha_background" <?php if ($userDetails->combate_pvp["battle_back"]) : ?>
                style="background: url(Imagens/Batalha/BattleBacks/<?= $userDetails->combate_pvp["battle_back"] ?>.jpg) no-repeat top center; -webkit-background-size: cover; background-size: cover;"
            <?php endif; ?>>
            <div class="fight-zone">
                <div class="navio navio-player" <?php if (! $userDetails->combate_pvp["battle_back"]) : ?>
                        style="background: url(Imagens/Bandeiras/Navios/<?= $userDetails->tripulacoes_pvp["2"]["faccao"] ?>/<?= $userDetails->tripulacoes_pvp["2"]["skin_tabuleiro_navio"] ?>/batalha.png) no-repeat center"
                    <?php endif; ?>>
                    <?php render_tabuleiro($tabuleiro, 0, 5, null, null, $special_effects); ?>
                </div>
                <div class="navio navio-player" <?php if (! $userDetails->combate_pvp["battle_back"]) : ?>
                        style="background: url(Imagens/Bandeiras/Navios/<?= $userDetails->tripulacoes_pvp["1"]["faccao"] ?>/<?= $userDetails->tripulacoes_pvp["1"]["skin_tabuleiro_navio"] ?>/batalha.png) no-repeat center"
                    <?php endif; ?>>
                    <?php render_tabuleiro($tabuleiro, 5, 10, null, null, $special_effects); ?>
                </div>
            </div>
            <div class="personagens-info">
                <?php render_personagens_info(
                    array_merge($personagens_combate["1"], $personagens_combate["2"]),
                    get_buffs_combate($userDetails->combate_pvp["id_1"], $userDetails->combate_pvp["id_2"]),
                    null,
                    $special_effects
                ); ?>
            </div>
        </div>
        <div id="menu_batalha">
            <div class="tempo-combate mb2">
                <img src="Imagens/Batalha/Menu/Tempo.png" />
                <span id="tempo_batalha">
                    <?= $userDetails->combate_pvp["vez_tempo"] - atual_segundo(); ?>
                </span>
            </div>
            <?php if ($userDetails->combate_pvp["vez"] == $my_id) : ?>
                <div>
                    <button id="botao_atacar" onclick="atacar()" class="btn btn-sm btn-danger">
                        <i class="fa fa-dot-circle-o"></i><br />
                        Atacar
                    </button>
                </div>
                <input type="hidden" id="moves_remain" value="<?= $userDetails->combate_pvp["move_$my_id"] ?>">
                <?php if ($userDetails->combate_pvp["move_$my_id"]) : ?>
                    <button id="botao_mover" data-move="<?= $userDetails->combate_pvp["move_$my_id"] ?> ?>" onclick="mover()"
                        class="btn btn-sm btn-info">
                        <i class="fa fa-arrows-alt"></i>
                        <?= $userDetails->combate_pvp["move_$my_id"] ?><br />
                        Mover
                    </button>
                <?php endif; ?>
                <div>
                    <button id="botao_passar" onclick="passar_vez()" class="btn btn-sm btn-primary">
                        <i class="fa fa-step-forward"></i><br />
                        Passar
                    </button>
                </div>
                <div>
                    <input type="hidden" id="moves_remain" value="<?= $userDetails->combate_bot["move"] ?>">
                </div>
            <?php endif; ?>
            <div>
                <button id="botao_desistir" onclick="desistir()" class="btn btn-sm btn-primary">
                    <i class="fa fa-flag"></i><br />
                    Desistir
                </button>
            </div>
        </div>

        <button id="botao_relatorio" onclick="relatorioCombate()" class="btn btn-sm btn-primary">
            <i class="fa fa-file-text"></i><br />
            Relatório
        </button>

        <div id="relatorio-combate-content">
            <?php if ($userDetails->combate_pvp["permite_apostas_1"] && $userDetails->combate_pvp["permite_apostas_2"]) : ?>
                <h3>
                    Valor acumulado das apostas:
                    <img src="Imagens/Icones/Berries.png" />
                    <?= mascara_berries($userDetails->combate_pvp["premio_apostas"]) ?>
                </h3>
            <?php else : ?>
                <div class="row">
                    <div class="col-md-6">
                        <?php if ($userDetails->combate_pvp["permite_apostas_$my_id"]) : ?>
                            <p class="text-success">
                                <i class="fa fa-thumbs-up"></i>
                                Você permitiu que outros jogadores assistam e apostem na sua batalha
                            </p>
                        <?php else : ?>
                            <button href="Batalha/permite_aposta.php" class="btn btn-success link_confirm"
                                data-question="Você permite que outros jogadores possam assitir sua batalha através da Home do jogo e assim fazer apostas sobre o resultado da partida?">
                                <i class="fa fa-thumbs-up"></i>
                                Permitir que assitam e apostem na minha batalha
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <?php if ($userDetails->combate_pvp["permite_apostas_$ini_id"]) : ?>
                            <p class="text-success">
                                <i class="fa fa-thumbs-up"></i>
                                O seu adversário permitiu que outros jogadores assistam e apostem na sua batalha
                            </p>
                        <?php else : ?>
                            <p class="text-danger">
                                <i class="fa fa-thumbs-down"></i>
                                O seu adversário ainda não permitiu que outros jogadores assistam e apostem na sua
                                batalha
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (! $userDetails->combate_pvp["permite_dados_1"] || ! $userDetails->combate_pvp["permite_dados_2"]) : ?>
                <div class="row">
                    <div class="col-md-6">
                        <?php if ($userDetails->combate_pvp["permite_dados_$my_id"]) : ?>
                            <p class="text-success">
                                <i class="fa fa-thumbs-up"></i>
                                Você permitiu o relatório avançado de combate
                            </p>
                        <?php else : ?>
                            <button href="Batalha/permite_dados.php" class="btn btn-success link_confirm"
                                data-question="O relatório avançado permite visualizar as porcentagens de esquiva, bloqueio e crítico assim como os valores sorteados em cada jogada. Esse recurso não ficará visível para quem estiver assistindo a luta. Deseja permitir o relatório avançado de combate?">
                                <i class="fa fa-thumbs-up"></i>
                                Permitir o relatório avançado de combate
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <?php if ($userDetails->combate_pvp["permite_dados_$ini_id"]) : ?>
                            <p class="text-success">
                                <i class="fa fa-thumbs-up"></i>
                                O seu adversário permitiu o relatório avançado de combate
                            </p>
                        <?php else : ?>
                            <p class="text-danger">
                                <i class="fa fa-thumbs-down"></i>
                                O seu adversário ainda não permitiu o relatório avançado de combate
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <br />
            <br />
            <?php $combate_logger = new CombateLogger($connection, $userDetails); ?>
            <?= Componentes::render("Combate.Relatorio", [
                "relatorio" => $combate_logger->get_relatorio_combate_pvp($userDetails->combate_pvp["combate"]),
                "id_azul" => $userDetails->tripulacao["id"],
                "avancado" => $userDetails->combate_pvp["permite_dados_1"] && $userDetails->combate_pvp["permite_dados_2"]
            ]); ?>
        </div>
            <?php if ($userDetails->conta) : ?>
            <div style="position: absolute; top: 3%; left: 5%; z-index: 100;">
                <button class="btn btn-primary btn-blocks" id="audio-toggle">
                <script>
                        var content = audioEnable
                        ? '<i class="fa fa-volume-up" aria-hidden="true"></i> Som Ligado'
                        : '<i class="fa fa-volume-off" aria-hidden="true"></i> Som Desligado';
                    $("#audio-toggle").html(content);
                </script>
                </button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
