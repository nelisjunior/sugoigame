<?php
function get_patente_marks()
{
    return array(
        5,
        10,
        15,
        20,
        30,
        40,
        50,
        60,
        80,
        100
    );
}

function get_max_patente_level()
{
    $patentes = get_patente_marks();
    return $patentes[count($patentes) - 1] + 10;
}

function get_patente_id($battle_level)
{
    $marks = get_patente_marks();

    for ($i = 0; $i < count($marks); $i++) {
        if ($battle_level < $marks[$i]) {
            return $i;
        }
    }
    return count($marks);
}

function get_patente_nome($faccao, $battle_level)
{
    $id = get_patente_id($battle_level);

    $patentes = array(
        FACCAO_MARINHA => array(
            0 => "Recruta",
            1 => "Cabo",
            2 => "Sargento",
            3 => "Sargento-Mor",
            4 => "Tenente",
            5 => "Tenente-Comandante",
            6 => "Comandante",
            7 => "Capitão",
            8 => "Comodoro",
            9 => "Contra-Almirante",
            10 => "Vice-Almirante"
        ),
        FACCAO_PIRATA => array(
            0 => "Recruta",
            1 => "Novato",
            2 => "Bucaneiro",
            3 => "Aventureiro",
            4 => "Explorador",
            5 => "Mestre de Navio",
            6 => "Pirata em Ascensão",
            7 => "Supernova",
            8 => "Veterano",
            9 => "Comandante",
            10 => "General",
        )
    );

    return $patentes[$faccao][$id];
}
?>
<?php function render_painel_grandes_poderes($titulo, $titulo_plural, $id1, $id2, $id3, $id4)
{ ?>
    <?php global $connection; ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <?= $titulo_plural ?>
        </div>
        <div class="panel-panel-body clearfix">
            <?php $result = $connection->run(
                "SELECT *, IF (pers.sexo = 0, titulo.nome, titulo.nome_f) AS titulo_nome
                  FROM tb_personagens pers
                  INNER JOIN tb_usuarios usr ON usr.cod_personagem = pers.cod
                  LEFT JOIN tb_titulos titulo ON pers.titulo = titulo.cod_titulo
                  WHERE usr.id IN (?, ?, ?, ?)
                  ORDER BY usr.reputacao DESC",
                "iiii", array($id1, $id2, $id3, $id4)
            ); ?>
            <?php while ($famoso = $result->fetch_array()) : ?>
                <div class="col-xs-12 col-sm-6 col-md-3" style="padding: 0">
                    <div class="div-top">
                        <?php render_top_player($famoso, $titulo); ?>
                    </div>
                    <img src="Imagens/Bandeiras/img.php?cod=<?= $famoso["bandeira"]; ?>&f=<?= $famoso["faccao"]; ?>" />
                </div>
            <?php endwhile; ?>
        </div>
        <div class="panel-footer">
            <?= $titulo_plural ?> são eleitos uma vez por mês por terem a maior quantidade de Poneglyphs
        </div>
    </div>
<?php } ?>
<?php function render_top_player($famoso, $titulo)
{ ?>
    <?= big_pers_skin($famoso["img"], $famoso["skin_c"], $famoso["borda"], "", 'style="max-width: 100%"') ?>
    <div class="texto-top">
        <!-- <p class="texto-top-cargo"><?= $titulo ?></p> -->
        <p class="texto-top-nome">
            <?= $famoso["nome"] ?>
        </p>
        <?php if (isset($famoso["titulo_nome"]) && $titulo != $famoso["titulo_nome"]) : ?>
            <p class="texto-top-alcunha">
                <?= $famoso["titulo_nome"] ?>
            </p>
        <?php endif; ?>
        <p class="texto-top-alcunha">
            <?= $famoso["tripulacao"] ?>
        </p>
    </div>
<?php } ?>
<?php function render_painel_yonkou($id1, $id2, $id3, $id4)
{
    render_painel_grandes_poderes("Yonkou", "Yonkou", $id1, $id2, $id3, $id4);
} ?>
<?php function render_painel_almirante($id1, $id2, $id3, $id4)
{
    render_painel_grandes_poderes("Almirante", "Almirantes", $id1, $id2, $id3, $id4);
} ?>
<?php function render_painel_grande_era($titulo, $id)
{ ?>
    <?php global $connection; ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <?= $titulo ?>
        </div>
        <div class="panel-panel-body clearfix">
            <?php $result = $connection->run(
                "SELECT *, IF (pers.sexo = 0, titulo.nome, titulo.nome_f) titulo_nome
                  FROM tb_personagens pers
                  INNER JOIN tb_usuarios usr ON usr.cod_personagem = pers.cod
                  LEFT JOIN tb_titulos titulo ON pers.titulo = titulo.cod_titulo
                  WHERE usr.id = ?",
                "i", array($id)
            );
            if ($result->count() > 0) :
                $famoso = $result->fetch_array(); ?>
                <div class="col-md-12">
                    <?= big_pers_skin($famoso["img"], $famoso["skin_c"], $famoso["borda"], "", 'style="max-width: 100%"') ?>
                    <div class="texto-top">
                        <p class="texto-top-cargo">
                            <?= $titulo ?>
                        </p>
                        <p class="texto-top-nome">
                            <?= $famoso["nome"] ?>
                        </p>
                        <?php if ($titulo != $famoso["titulo_nome"]) : ?>
                            <p class="texto-top-alcunha">
                                <?= $famoso["titulo_nome"] ?>
                            </p>
                        <?php endif; ?>
                        <p class="texto-top-alcunha">
                            <?= $famoso["tripulacao"] ?>
                        </p>
                    </div>
                </div>
                <div class="col-md-12">
                    <img src="Imagens/Bandeiras/img.php?cod=<?= $famoso["bandeira"]; ?>&f=<?= $famoso["faccao"]; ?>"
                        style="margin: 20px" />
                </div>
            <?php endif; ?>
        </div>
        <div class="panel-footer">
            Eleito na Grande Era dos Piratas.
        </div>
    </div>
<?php } ?>
<?php function render_painel_rdp($id)
{
    render_painel_grande_era("Rei dos Piratas", $id);
} ?>
<?php function render_painel_adf($id)
{
    render_painel_grande_era("Almirante de Frota", $id);
} ?>
<?php function render_top_ranking_reputacao($coluna_reputacao, $faccoes, $limit = 6)
{ ?>
    <?php global $connection; ?>
    <?php $rep_mais_forte = $connection->run(
        "SELECT max($coluna_reputacao) AS total FROM tb_usuarios WHERE faccao IN (" . implode(",", $faccoes) . ")"
    )->fetch_array()["total"]; ?>
    <?php $result = $connection->run(
        "SELECT usr.id, usr.$coluna_reputacao AS reputacao, usr.faccao, usr.bandeira, usr.tripulacao, pers.img, pers.nome, pers.skin_r
                FROM tb_usuarios usr
                INNER JOIN tb_personagens pers ON usr.cod_personagem = pers.cod
                WHERE usr.adm='0' AND usr.faccao IN (" . implode(",", $faccoes) . ") ORDER BY usr.$coluna_reputacao DESC LIMIT $limit"
    ); ?>
    <?php render_top_ranking($result, $rep_mais_forte, "reputacao", ($coluna_reputacao == "reputacao" ? "Road " : "") . "Poneglyphs"); ?>
<?php } ?>
<?php function render_campeao_incursao($id, $titulo)
{ ?>
    <?php global $connection; ?>
    <?php $result = $connection->run(
        "SELECT usr.id, usr.faccao, usr.bandeira, usr.tripulacao, pers.img, pers.nome, pers.skin_r, pers.borda, pers.skin_c
                FROM tb_usuarios usr
                INNER JOIN tb_personagens pers ON usr.cod_personagem = pers.cod
                WHERE usr.adm='0' AND usr.id = ?", "i", array($id)
    )->fetch_array();
    if ($result) : ?>
        <div class="col-sm-4 col-md-4">
            <div class="div-top">
                <?php render_top_player($result, $titulo); ?>
            </div>
            <img src="Imagens/Bandeiras/img.php?cod=<?= $result["bandeira"]; ?>&f=<?= $result["faccao"]; ?>" />
        </div>
    <?php endif;
} ?>
<?php function render_top_ranking($result, $mais_forte, $column, $label)
{ ?>
    <?php while ($famoso = $result->fetch_array()) : ?>
        <div class="panel-footer py0">
            <div class="media">
                <div class="media-left">
                    <img src="Imagens/Bandeiras/img.php?cod=<?= $famoso["bandeira"]; ?>&f=<?= $famoso["faccao"]; ?>"
                        width="40" />
                    <img src="Imagens/Personagens/Icons/<?= get_img($famoso, "r") ?>.jpg" width="40" />
                </div>
                <div class="media-body">
                    <p>
                        <?= $famoso["nome"] ?> -
                        <?= $famoso["tripulacao"] ?>
                    </p>
                    <div class="progress" style="margin: 0">
                        <div class="progress-bar progress-bar-success"
                            style="width: <?= $famoso[$column] / $mais_forte * 100 ?>%">
                            <span>
                                <?= mascara_numeros_grandes($famoso[$column]) ?>
                                <?= $label ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php } ?>

