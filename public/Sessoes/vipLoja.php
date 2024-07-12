<?php function render_vantagem($img, $titulo, $descricao, $duracao, $preco_gold, $link_gold)
{ ?>
    <?php global $userDetails; ?>
    <li class="col-xs-12 col-md-4 panel panel-default" style="margin: 0.1em 0.1em;">
        <style>
                @media (min-width: 992px) {
                    #colunaloja {
                        display: flex;
                        flex-direction: column;
                    }
                    .col-md-4 {
                        max-width: 24vw;
                    }
                }
        </style>
        <div class="row" style="justify-content: center; align-items: center;"  id="colunaloja">
            <div class="col-xs-2 col-md-2">
                <img src="Imagens/Vip/<?= $img ?>" height="60px" />
            </div>
            <div class="col-xs-7 col-md-7">
                <h4>
                    <?= $titulo ?>
                </h4>
                <p>
                    <?= $descricao ?>
                </p>
                <?php if ($duracao === FALSE) : ?>
                    <p>
                        Instantâneo
                    </p>
                <?php else : ?>
                    <?php if ($duracao == 0 or $duracao < atual_segundo()) : ?>
                        <p>
                            Duração: 30 dias
                        </p>
                    <?php else : ?>
                        <p class="text-success">
                            <i class="fa fa-check"></i> <span>Você já possui essa vantagem!</span>
                        </p>
                        <p>
                            Tempo Restante:
                            <?= transforma_tempo_min($duracao - atual_segundo()) ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="col-xs-3 col-md-3">
                <p>
                    <button href="<?= $link_gold ?>" class="link_confirm btn btn-success"
                        data-question="Deseja adquirir essa vantagem?" <?= $userDetails->conta["gold"] < $preco_gold ? "disabled" : "" ?>>
                        <?= $preco_gold ?> <img src="Imagens/Icones/Gold.png" />
                        <?= $duracao !== FALSE && ($duracao >= atual_segundo()) ? "" : "" ?>
                    </button>
                </p>
                <p>

                </p>
            </div>
        </div>
    </li>
<?php } ?>

<div class="panel-heading">
    Gold Shop
    <?= ajuda("O que é o Gold SHop", "Adquira vantagens exclusivas com suas moedas de ouro.") ?>
</div>
<script type="text/javascript">
    $(function () {
        $("#renomeia_trip").click(function () {
            bootbox.prompt('Escreva um novo nome para sua tripulação:', function (input) {
                if (input) {
                    sendGet('Vip/reset_tripulacao.php?nome=' + input);
                }
            });
        });

    });
</script>
<div class="panel-body">

    <ul class="row" style="list-style-type: none; padding: 0 0.1rem; justify-content: center;">
        <?php render_vantagem(
            "tatics.png",
            "Táticas",
            "Defina uma posição fixa para cada tripulante antes de combates.",
            $userDetails->vip["tatic_duracao"],
            PRECO_GOLD_TATICAS,
            "Vip/tatics_comprar.php"
        ); ?>

        <?php render_vantagem(
            "luneta.png",
            "Luneta",
            "Aumenta o campo de visão no oceano em um quadro em cada direção.",
            $userDetails->vip["luneta_duracao"],
            PRECO_GOLD_LUNETA,
            "Vip/luneta_comprar.php"
        ); ?>

        <?php render_vantagem(
            "img.png",
            "Formações de tripulantes",
            "Permite criar e ativar formações de tripulantes fora do barco.",
            $userDetails->vip["formacoes_duracao"],
            PRECO_GOLD_USAR_FORMACOES,
            "Vip/formacao_comprar.php?tipo=gold"
        ); ?>

        <?php render_vantagem(
            "coup-de-burst.gif",
            "Pacote de Coup De Burst diário",
            "Reduz em 10 segundos o tempo necessário para navegar 1 quadro da rota traçada. Pode ser usado 5 vezes por dia. Não pode ser usado se você estiver invisível. Não pode ser usado duas vezes no mesmo quadro.",
            $userDetails->vip["coup_de_burst_duracao"],
            PRECO_GOLD_COUP_DE_BURST,
            "Vip/coup_de_burst_comprar.php?tipo=gold"
        ); ?>

        <li class="col-xs-12 col-md-4 panel panel-default">
            <div class="row" style="display: flex; justify-content: center; align-items: center;" id="colunaloja">
                <div class="col-xs-2 col-md-2">
                    <img src="Imagens/Vip/renomear.png"  />
                </div>
                <div class="col-xs-7 col-md-7">
                    <h4>Renomear tripulação</h4>
                    <p>Mude o nome da sua tripulação.</p>
                    <p>
                        Instantâneo
                    </p>
                </div>
                <div class="col-xs-3 col-md-3">
                    <p>
                        <button id="renomeia_trip" class="btn btn-success" <?= $userDetails->conta["gold"] < PRECO_GOLD_RENOMEAR_TRIPULACAO ? "disabled" : "" ?>>
                            <?= PRECO_GOLD_RENOMEAR_TRIPULACAO ?> <img src="Imagens/Icones/Gold.png" />
                        </button>
                    </p>

                </div>
            </div>
        </li>
        <li class="col-xs-12 col-md-4 panel panel-default">
            <div class="row" style="justify-content: center; align-items: center;" id="colunaloja">
                <div class="col-xs-2 col-md-2">
                    <img src="Imagens/Vip/faccao.png" />
                </div>
                <div class="col-xs-7 col-md-7">
                    <h4>Mudar de facção</h4>
                    <p>Piratas se tornam marinheiros e Marinheiros se tornam piratas.</p>
                    <p>Não é possível trocar de facção se você fizer parte de uma Aliança ou Frota.</p>
                    <p>ATENÇÃO: Ao trocar de facção seus pontos de reputação serão resetados.</p>
                    <p>
                        Instantâneo
                    </p>
                </div>
                <div class="col-xs-3 col-md-3">
                    <p>
                        <button href="Vip/faccao_trocar.php" data-question="Deseja trocar de facção?"
                            class="link_confirm btn btn-success" <?= $userDetails->ally
                                || $userDetails->conta["gold"] < PRECO_GOLD_TROCAR_FACCAO ? "disabled" : "" ?>>
                            <?= PRECO_GOLD_TROCAR_FACCAO ?> <img src="Imagens/Icones/Gold.png" />
                        </button>
                    </p>

                </div>
            </div>
        </li>
    </ul>
</div>
