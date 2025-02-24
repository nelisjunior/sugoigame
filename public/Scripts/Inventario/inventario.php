<?php
$valida = "EquipeSugoiGame2012";
require "../../Includes/conectdb.php";
include "../../Includes/verifica_login_sem_pers.php";
include "../../Includes/verifica_combate.php";

$protector->need_tripulacao();

$items = get_many_results_joined_mapped_by_type("tb_usuario_itens", "cod_item", "tipo_item", array(
    array("nome" => "tb_item_acessorio", "coluna" => "cod_acessorio", "tipo" => TIPO_ITEM_ACESSORIO),
    array("nome" => "tb_item_mapa", "coluna" => "cod_mapa", "tipo" => TIPO_ITEM_MAPA),
    array("nome" => "tb_item_navio_casco", "coluna" => "cod_casco", "tipo" => TIPO_ITEM_CASCO),
    array("nome" => "tb_item_navio_leme", "coluna" => "cod_leme", "tipo" => TIPO_ITEM_LEME),
    array("nome" => "tb_item_navio_velas", "coluna" => "cod_velas", "tipo" => TIPO_ITEM_VELAS),
    array("nome" => "tb_item_pose", "coluna" => "cod_pose", "tipo" => TIPO_ITEM_POSE),
    array("nome" => "tb_item_navio_canhao", "coluna" => "cod_canhao", "tipo" => TIPO_ITEM_CANHAO),
    array("nome" => "tb_item_equipamentos", "coluna" => "cod_equipamento", "tipo" => TIPO_ITEM_EQUIPAMENTO),
    array("nome" => "tb_item_reagents", "coluna" => "cod_reagent", "tipo" => TIPO_ITEM_REAGENT),
    array("nome" => "tb_item_missao", "coluna" => "id", "tipo" => TIPO_ITEM_MISSAO),
), "WHERE origem.id = ? ORDER BY origem.cod_item", "i", $userDetails->tripulacao["id"]);

// remedios
$result = $connection->run("SELECT * FROM tb_usuario_itens WHERE id=? AND tipo_item=?",
    "ii", [$userDetails->tripulacao["id"], TIPO_ITEM_REMEDIO]);
while ($item = $result->fetch_array()) {
    $items[] = array_merge($item, MapLoader::find("remedios", ["cod_remedio" => $item["cod_item"]]));
}

// comidas
$result = $connection->run("SELECT * FROM tb_usuario_itens WHERE id=? AND tipo_item=?",
    "ii", [$userDetails->tripulacao["id"], TIPO_ITEM_COMIDA]);
while ($item = $result->fetch_array()) {
    $items[] = array_merge($item, MapLoader::find("comidas", ["cod_comida" => $item["cod_item"]]));
}

// akumas
$result = $connection->run("SELECT * FROM tb_usuario_itens WHERE id=? AND tipo_item=?",
    "ii", [$userDetails->tripulacao["id"], TIPO_ITEM_AKUMA]);
while ($item = $result->fetch_array()) {
    $items[] = array_merge($item, DataLoader::find("akumas", ["cod_akuma" => $item["cod_item"]]));
}

// Bala de canhao
$result = $connection->run("SELECT * FROM tb_usuario_itens WHERE quant > 0 AND id = ? AND tipo_item = 13 ORDER BY cod_item",
    "i", $userDetails->tripulacao["id"]);

while ($item = $result->fetch_array()) {
    $items[] = array_merge($item, array(
        "nome" => "Bala de canhão",
        "descricao" => "Usada no canhão do navio para batalhas em alto mar.",
        "img" => "168"
    ));
}

// Isca
$result = $connection->run("SELECT * FROM tb_usuario_itens WHERE quant > 0 AND id = ? AND tipo_item = 16 ORDER BY cod_item",
    "i", $userDetails->tripulacao["id"]);

while ($item = $result->fetch_array()) {
    $items[] = array_merge($item, array(
        "nome" => "Isca",
        "categoria" => 4,
        "descricao" => "Tem 30% de chance de iniciar uma batalha contra uma criatura marítmica quando usada.<br/> Não pode ser usada logo após ser atingido por disparos de canhões.</br> Item consumível, não acumulativo",
        "img" => "370"
    ));
}
// Isca Dourada
$result = $connection->run("SELECT * FROM tb_usuario_itens WHERE quant > 0 AND id = ? AND tipo_item = 17 ORDER BY cod_item",
    "i", $userDetails->tripulacao["id"]);

while ($item = $result->fetch_array()) {
    $items[] = array_merge($item, array(
        "nome" => "Isca Dourada",
        "categoria" => 6,
        "descricao" => "Tem 100% de chance de iniciar uma batalha contra uma criatura marítmica quando usada.<br/>Não pode ser usada logo após ser atingido por disparos de canhões.</br>Item consumível, não acumulativo",
        "img" => "371"
    ));
}

$connection->run("UPDATE tb_usuario_itens SET novo = 0 WHERE id=?", "i", array($userDetails->tripulacao["id"]));
?>
<script type="text/javascript">
    $(function () {
        $('.inventario-item').popover();
    });
</script>
<div class="modal-body">
    <div class="inventario-items">
        <?php foreach ($items as $item) : ?>
            <?php $item["cod"] = $item["cod_item"]; ?>
            <?php if (! isset($item["tipo"]))
                $item["tipo"] = $item["tipo_item"]; ?>
            <button class="inventario-item equipamentos_classe_<?= isset($item["categoria"]) ? $item["categoria"] : "1" ?>"
                data-content="-" data-toggle="popover" data-html="true" data-placement="bottom" data-trigger="focus"
                data-template='<div class="inventario-item-info"><?= info_item($item, $item, TRUE, TRUE, $userDetails->combate_pvp || $userDetails->combate_pve); ?></div>'>

                <?= get_img_item($item) ?>
                <?php if (isset($item["quant"]) && $item["quant"] > 1) : ?>
                    <span class="badge badge-default">
                        <?= $item["quant"] ?>
                    </span>
                <?php endif; ?>
                <?php if (isset($item["novo"]) && $item["novo"]) : ?>
                    <span class="label label-danger">*</span>
                <?php endif; ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>
<div class="modal-footer">
    Capacidade:
    <?= count($items) ?> /
    <?= $userDetails->navio["capacidade_inventario"]; ?>
</div>
