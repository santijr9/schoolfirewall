<?php

require_once 'config.php';
require_once 'functions.php';

cabecera();
?>

<h1>Botó d'Internet</h1>

<table border="0">
<tr>
    <th>Aula</th>
    <th>Estado</th>
    <th colspan="3">Acción</th>
</tr>

<?php foreach ($AULAS as $num => $subred):
    $estado_actual = estado_aula($subred);
    $nombre = $NOMBRES_AULAS[$num];

    $icono = match($estado_actual) {
        'abierto' => 'verde.png',
        'moodle'  => 'naranja.png',
        default   => 'rojo.png',
    };

    $texto = match($estado_actual) {
        'abierto' => 'Abierto',
        'moodle'  => 'Solo Moodle',
        default   => 'Cerrado',
    };
?>
<tr>
    <td width="100"><?= htmlspecialchars($nombre) ?></td>
    <td width="80"><img width="32" height="32" src="<?= $icono ?>"> <?= $texto ?></td>
    <td width="90"><button onClick="window.location.href='toggle.php?aula=<?= $num ?>&estado=abierto'">🔓 Abrir</button></td>
    <td width="100"><button onClick="window.location.href='toggle.php?aula=<?= $num ?>&estado=moodle'">🟡 Solo Moodle</button></td>
    <td width="90"><button onClick="window.location.href='toggle.php?aula=<?= $num ?>&estado=cerrado'">🔴 Cerrar</button></td>
</tr>
<?php endforeach; ?>
</table>

<?php
fin();
