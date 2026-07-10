<?php

require_once 'config.php';
require_once 'functions.php';

$aula = intval($_GET['aula'] ?? 0);
$estado = $_GET['estado'] ?? '';

if (!isset($AULAS[$aula]) || !isset($ESTADOS[$estado])) {
    die("Parámetros inválidos");
}

$subred = $AULAS[$aula];
$nombre_aula = $NOMBRES_AULAS[$aula];

// Quitar de todos los sets
foreach (['abiertas', 'cerradas', 'solo_moodle'] as $s) {
    ejecutar_nft("delete element ip filter $s { $subred }");
}

// Añadir al set destino (si no es moodle, que se quita de todos y cae en policy drop)
ejecutar_nft("add element ip filter $estado { $subred }");

cabecera();
?>
<h1><?= htmlspecialchars($nombre_aula) ?></h1>
<p style="color:green; font-weight:bold;">
    Estado cambiado a: <?= htmlspecialchars($ESTADOS[$estado]) ?>
</p>
<p>Redirigiendo en <span id="counter">3</span> seg...</p>
<script>
setInterval(function() {
    var div = document.querySelector("#counter");
    var count = div.textContent * 1 - 1;
    div.textContent = count;
    if (count <= 0) {
        location.href = "firewall.php";
    }
}, 1000);
</script>
<?php
fin();
