<?php
include "../../../Includes/conectdb.php";
$protector->need_tripulacao();
$pers_cod = $protector->get_number_or_exit("cod");

$pers = $userDetails->get_pers_by_cod($pers_cod);

if (! $pers) {
    $protector->exit_error("Personagem inválido");
}
?>

<?php $akumas = $connection->run("SELECT cod_item, tipo_item FROM tb_usuario_itens
    WHERE id= ? AND tipo_item=?", "ii", [$userDetails->tripulacao["id"], TIPO_ITEM_AKUMA])
    ->fetch_all_array(); ?>

<?php render_personagem_panel_top($pers, 0) ?>
<?php render_personagem_sub_panel_with_img_top($pers); ?>
<div class="panel-body">
    <?php if (! $pers["akuma"]) : ?>
        <h4>Frutas:</h4>
        <?php if (count($akumas)) : ?>
            <div class="row">
                <?php foreach ($akumas as $item) : ?>
                    <?php $akuma_info = DataLoader::find("akumas", ["cod_akuma" => $item["cod_item"]]); ?>
                    <div class="col-md-4">
                        <?= get_img_item($akuma_info) ?>
                        <div>
                            <?= $akuma_info["nome"]; ?>
                        </div>
                        <button
                            data-question="Essa fruta tem um gosto horrível, quem comer não poderá nadar e nem comer outra fruta depois. Tem certeza que deseja continuar?"
                            href="Akuma/comer_akuma.php?cod=<?= $pers["cod"] ?>&akuma=<?= $item["cod_item"] ?>"
                            class="link_confirm btn btn-success">
                            Comer
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            Você precisa encontrar alguma Akuma no Mi para o personagem comer.<br>
            Há boatos que dizem que se pode encontrar uma Akuma no Mi mergulhando ou fazendo expedições, mas apenas
            mergulhadores
            e arqueólogos podem realizar essas tarefas...<br>
        <?php endif; ?>
    <?php else : ?>
        <?php $akuma = DataLoader::find("akumas", ["cod_akuma" => $pers["akuma"]]); ?>
        <?= get_img_item($akuma) ?>
        <h4>
            <?= $akuma["nome"]; ?>
        </h4>
        <p>
            <?= $akuma["descricao"]; ?>
        </p>
        <ul class="text-left">
            <li>
                Tipo:
                <?= nome_tipo_akuma($akuma["tipo"]); ?>
            <li>
                Categoria:
                <?= nome_categoria_akuma($akuma["categoria"]); ?>
            </li>
            <li>
                <?php render_vantagens_akuma($akuma); ?>
            </li>
        </ul>

        <?php $habilidades = \Regras\Habilidades::get_todas_habilidades_akuma($akuma["cod_akuma"]); ?>

        <?= \Componentes::render('Habilidades.Lista', ["pers" => $pers, "habilidades" => $habilidades]); ?>

        <div>
            <button class="link_confirm btn btn-info" data-question="Deseja remover a Akuma no Mi desse personagem?"
                href="Vip/reset_akuma.php?cod=<?= $pers["cod"] ?>" <?= $userDetails->conta["gold"] < PRECO_GOLD_RESET_AKUMA ? "disabled" : "" ?>>
                <?= PRECO_GOLD_RESET_AKUMA ?> <img src="Imagens/Icones/Gold.png" />
                Remover a Akuma no Mi
            </button>
        </div>
    <?php endif; ?>
</div>
<?php render_personagem_sub_panel_with_img_bottom(); ?>
<?php render_personagem_panel_bottom() ?>

