<table>
    <tr>
        <?php foreach ($userDetails->personagens as $count => $pers) : ?>
            <?php if ($count % 2 == 0) : ?>
            </tr>
            <tr>
            <?php endif; ?>
            <td class="tripulante_quadro_td">
                <div id="tripulante_quadro_<? echo $pers["cod"] ?>" onclick="setQueryParam('cod','<?= $pers["cod"]; ?>');"
                    data-cod="<?= $pers["cod"]; ?>"
                    class="tripulante_quadro <?= $userDetails->tripulacao["faccao"] == FACCAO_MARINHA ? "marine" : "pirate" ?>"
                    data-content="-" data-container="body" data-toggle="popover" data-placement="bottom" data-html="true"
                    data-trigger="focus" tabindex="0" data-template='
                    <div class="container tripulante_quadro_info">
                        <div class="row">
                            <div class="col-xs-6 tripulante_quadro_skin">
                                <?= big_pers_skin($pers["img"], $pers["skin_c"], $pers["borda"], "tripulante_big_img", 'width="100%"') ?>

                                <div class="tripulante_score">
                                    Score: <?php echo ((int) $pers["classe_score"]) ?>
                                </div>

                                <?php render_personagem_haki_bars($pers); ?>
                            </div>
                            <div class="col-xs-6">
                                <?= render_cartaz_procurado($pers, $userDetails->tripulacao["faccao"]) ?>
                                <div>
                                    Nível <?= $pers["lvl"]; ?>
                                </div>

                                <?php render_personagem_status_bars($pers); ?>
                                <?php if ($userDetails->vip["conhecimento_duracao"]) : ?>
                                    <?php render_row_atributo("atk", "Ataque", $pers); ?>
                                    <?php render_row_atributo("def", "Defesa", $pers); ?>
                                    <?php render_row_atributo("pre", "Precisao", $pers); ?>
                                    <?php render_row_atributo("agl", "Agilidade", $pers); ?>
                                    <?php render_row_atributo("res", "Resistencia", $pers); ?>
                                    <?php render_row_atributo("con", "Conviccao", $pers); ?>
                                    <?php render_row_atributo("dex", "Dextreza", $pers); ?>
                                    <?php render_row_atributo("vit", "Vitalidade", $pers); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>'>
                    <img class="tripulante_quadro_img <?= $userDetails->tripulacao["faccao"] == FACCAO_MARINHA ? "marine" : "pirate" ?>"
                        src="Imagens/Personagens/Icons/<?= getImg($pers, "r"); ?>.jpg">

                    <?php if ($pers["xp"] >= $pers["xp_max"] && $pers["lvl"] < 50) : ?>
                        <div class="tripulante-lvl-up" data-toggle="tooltip" data-placement="bottom" data-container="#tudo"
                            title="Este tripulante já pode evoluir. Acesse a visão geral da tripulação!">
                            <a href="./?ses=status&cod=<?= $pers["cod"] ?>" class="link_content">
                                <img src="Imagens/Icones/quest-1.png">
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="tripulante_level">
                        <?= $pers["lvl"] ?>
                    </div>
                    <div class="tripulante_quadro_td_status">
                        <?php render_personagem_status_bars($pers, false); ?>
                    </div>
                </div>
            </td>
        <?php endforeach; ?>
    </tr>
</table>
