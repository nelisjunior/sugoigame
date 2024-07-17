<?php
$valida = "EquipeSugoiGame2012";
require "../../Includes/conectdb.php";

$protector->need_tripulacao();
$protector->must_be_in_any_kind_of_combat();

$combate = Regras\Combate\Combate::build($connection, $userDetails, $protector);

$cod = $protector->get_number_or_exit("cod");

$result = $connection->run(
    "SELECT *
    FROM tb_combate_personagens cbtpers
    INNER JOIN tb_personagens pers ON cbtpers.cod = pers.cod
    WHERE cbtpers.id = ? AND cbtpers.cod = ?",
    "ii", [$userDetails->tripulacao["id"], $cod]
);

if (! $result->count()) {
    $protector->exit_error("Personagem inválido.");
}
$pers = $result->fetch_array();

$habilidades = \Regras\Habilidades::get_todas_habilidades_pers($pers);

$skill_espera = $connection->run(
    "SELECT *
     FROM tb_combate_skil_espera
     WHERE id = ?",
    "i", [$userDetails->tripulacao["id"]]
)->fetch_all_array();

?>
<div class="row">
    <?php foreach ($habilidades as $habilidade) : ?>
        <?php if (\Regras\Habilidades::is_usavel_batalha($habilidade)) : ?>
            <?php
            $filtro = $habilidade["recarga_universal"]
                ? ["recarga_universal" => 1]
                : [
                    "cod_skil" => $habilidade["cod"],
                    "cod" => $pers["cod"]
                ];
            ?>
            <?php $espera = array_find($skill_espera, $filtro) ?: []; ?>
            <?php $espera = isset($espera["espera"]) ? $espera["espera"] + 1 : 0; ?>
            <div class="col-md-2 col-sm-2 col-xs-2 p0 h-100">
                <div class="panel panel-default m0 h-100">
                    <div class="panel-body">
                        <div>
                            <?= Componentes::render('Habilidades.Icone', ["habilidade" => $habilidade, "vontade" => max($habilidade["vontade"], $combate->minhaTripulacao->get_vontade()), "espera" => $espera]) ?>
                        </div>
                        <div>
                            <?php if ($espera) : ?>
                                <p>
                                    <?= $espera ?> turno(s)
                                </p>
                            <?php elseif ($combate->minhaTripulacao->personagens[$pers["cod"]]->get_valor_atributo("ATORDOAMENTO")) : ?>
                                <p>Tripulante artodoado</p>
                            <?php elseif ($combate->minhaTripulacao->get_vontade() < $habilidade["vontade"]) : ?>
                                <p>Vontade insuficiente</p>
                            <?php elseif ($combate->vez_de_quem() == $combate->minhaTripulacao->indice && ! $espera["espera"] && $combate->minhaTripulacao->get_vontade() >= $habilidade["vontade"]) : ?>
                                <button class="btn btn-success" data-dismiss="modal"
                                    onclick="usaSkil('<?= $habilidade["cod"]; ?>','<?= $pers["cod"]; ?>','<?= $habilidade["alcance"]; ?>', '1','<?= $habilidade["area"]; ?>')">
                                    Usar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
