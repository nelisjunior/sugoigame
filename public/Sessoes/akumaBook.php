<div class="panel-heading">
    Akuma Book
    <?= ajuda_tooltip("Aqui estão as informações sobre todas as Akuma no Mi disponíveis.
    Akumas Ofensivas causam mais dano contra Akumas Táticas.
    Akumas Táticas causam mais dano contra Akumas Defensivas.
    Akumas Defensivas causam mais dano contra Akumas Ofensivas.
    Akumas Ancestrais causam mais dano contra Akumas Míticas.
    Akumas Míticas causam mais dano contra Akumas Ancestrais.
    Algumas Akumas possuem excessões que fogem a essas regras.") ?>
</div>

<div class="panel-body">

    <?php
    $page_size = 6;
    if (! isset($_GET["p"]) || ! validate_number($_GET["p"])) {
        $p = 0;
    } else {
        $p = $_GET["p"];
    }
    $p *= $page_size;

    $akumas = DataLoader::load("akumas");


    $total = count($akumas);

    $page = array_slice($akumas, $p, $page_size);
    ?>

    <div class="row">
        <? foreach ($page as $akuma) : ?>
            <div class="col col-xs-4 h-100">
                <div class="panel panel-default h-100">
                    <div class="panel-body">
                        <?= get_img_item($akuma) ?>
                        <h4 class="m0">
                            <?= $akuma["nome"]; ?>
                        </h4>
                        <div class="mb">
                            <?= $akuma["descricao"]; ?>
                        </div>

                        <div class="mb">
                            <span class="label label-<?= label_tipo_akuma($akuma["tipo"]) ?>">
                                <?= nome_tipo_akuma($akuma["tipo"]) ?>
                            </span>
                            &nbsp;
                            <span class="label label-<?= label_categoria_akuma($akuma["categoria"]) ?>">
                                <?= nome_categoria_akuma($akuma["categoria"]) ?>
                            </span>
                        </div>

                        <?php render_vantagens_akuma($akuma); ?>
                    </div>
                    <div class="panel-footer">

                        <?php $habilidades = \Regras\Habilidades::get_todas_habilidades_akuma($akuma["cod_akuma"]); ?>
                        <?php foreach ($habilidades as $habilidade) : ?>
                            <div class="d-inline-block">
                                <div>
                                    <?= \Componentes::render("Habilidades.Icone", ["habilidade" => $habilidade]); ?>
                                </div>
                                <div>
                                    <?= $habilidade["requisito_lvl"] ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php $p /= 10; ?>
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <?php if ($p > 0) : ?>
                <li>
                    <a href="./?ses=akumaBook&p=<?= $p - 1 ?>" class="link_content">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if ($total) : ?>
                <?php for ($i = 0; $i < ceil($total / $page_size); $i++) : ?>
                    <?php if ($i >= 0) : ?>
                        <li>
                            <a href="./?ses=akumaBook&p=<?= $i ?>" aria-label="link_content"
                                class="<?= $i == $p ? "active" : "" ?> link_content">
                                <?= $i + 1 ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php endif; ?>
            <?php if ($p < floor($total / $page_size)) : ?>
                <li>
                    <a href="./?ses=akumaBook&p=<?= $p + 1 ?>" class="link_content">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
