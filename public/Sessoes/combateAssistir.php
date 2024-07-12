<?php
if (! validate_number($_GET["combate"])) {
    echo "Batalha não encontrada ou finalizada<br/><a href='ses?=home' class='link_content'>Voltar a página inicial</a>";
    exit();
}
$combate_id = $_GET["combate"];

$result = $connection->run("SELECT * FROM tb_combate WHERE combate = ?", "i", $combate_id);

if (! $result->count()) {
    echo '<div style="modal-dialog modal-lg">';
    echo 'Batalha não encontrada ou já finalizada';
    echo '</div';
    exit();
}

$combate = $result->fetch_array();

$tripulacao["1"] = $connection->run("SELECT * FROM tb_usuarios WHERE id = ?", "i", $combate["id_1"])->fetch_array();
$tripulacao["2"] = $connection->run("SELECT * FROM tb_usuarios WHERE id = ?", "i", $combate["id_2"])->fetch_array();

$pode_assistir = $userDetails->tripulacao["adm"] || ($combate["permite_apostas_1"] && $combate["permite_apostas_2"]);

if (! $pode_assistir) {
    echo "<i class=\"fa fa-thumbs-down\"></i> Os jogadores não permitiram que essa batalha seja assistida<br/><a href='ses?=home' class='link_content'>Voltar a página inicial</a>";
    exit();
}
?>

<style type="text/css">
    <?php include "CSS/combate.css"; ?>
</style>
<script type="text/javascript">
    <?php include "JS/combate.js"; ?>
    <?php include "JS/combate_assistir.js"; ?>
</script>
<div class="batalha-content">
    <?php include "Scripts/Batalha/batalha_tabuleiro_assistir_content.php"; ?>
</div>
