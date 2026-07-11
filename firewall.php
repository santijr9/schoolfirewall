<?php

include 'functions.php';

cabecera();

$debug = false;

function estado_aula($chain) {
    $reglas = shell_exec("iptables -nL $chain");
    if (strpos($reglas, 'ACCEPT') !== false) return 'activado';
    if (strpos($reglas, 'DROP') !== false) return 'desactivado';
    return 'solo_moodle';
}

$config = json_decode(file_get_contents('aulas.json'), true);
$aulas = $config['aulas'];

$estados = [];
foreach ($aulas as $a) {
    $estados[$a['id']] = estado_aula($a['chain']);
}

print "<h1 style=\"text-align:center\">Filtro de Internet</h1>";
print "<table class='aulas'>";

foreach ($aulas as $a) {
    $estado = $estados[$a['id']];
    $id = $a['id'];
    $red = $a['red'];

    if (!ip_pertenece_a_red($_SERVER['REMOTE_ADDR'], $red) && !$debug) continue;

    $icono = match($estado) {
        'activado' => 'verde.png',
        'solo_moodle' => 'naranja.png',
        'desactivado' => 'rojo.png',
    };
    $texto_estado = match($estado) {
        'activado' => 'Activado',
        'solo_moodle' => 'Solo Aules',
        'desactivado' => 'Desactivado',
    };
    $boton = match($estado) {
        'activado' => "<button class='btn' onClick=\"window.location.href='accion_aula.php?aula=$id&accion=aules'\">Solo Aules</button>
                       <button class='btn' onClick=\"window.location.href='accion_aula.php?aula=$id&accion=desactivar'\">Desactivar</button>",
        'solo_moodle' => "<button class='btn' onClick=\"window.location.href='accion_aula.php?aula=$id&accion=activar'\">Activar</button>
                          <button class='btn' onClick=\"window.location.href='accion_aula.php?aula=$id&accion=desactivar'\">Desactivar</button>",

        'desactivado' => "<button class='btn' onClick=\"window.location.href='accion_aula.php?aula=$id&accion=activar'\">Activar</button>
                          <button class='btn' onClick=\"window.location.href='accion_aula.php?aula=$id&accion=aules'\">Solo Aules</button>",
    };

    print "<tr>
        <td class='aula-nombre'>{$a['nombre']}</td>
        <td class='aula-icono'><img width='40' height='40' src='$icono'></td>
        <td class='aula-estado'><strong>$texto_estado</strong></td>
        <td class='aula-botones'>$boton</td>
    </tr>";
}

print "</table>";

fin();

?>
