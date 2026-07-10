<?php

function cabecera() {
echo '<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Internet Door</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="login">
';
}

function fin() {
echo '
  <div class="utilities">
    <input name="salir" type="button" onClick="javascript:window.close();" value="Salir">
    <a href="javascript:window.close();">Cerrar</a>
  </div>
</div>
</body>
</html>
';
}

function ejecutar_nft($comando) {
    $cmd = 'sudo /usr/sbin/nft ' . $comando . ' 2>&1';
    $output = shell_exec($cmd);
    return $output;
}

function estado_aula($subred) {
    $sets = ['abiertas', 'cerradas', 'solo_moodle'];
    $mapa = [
        'abiertas'    => 'abierto',
        'cerradas'    => 'cerrado',
        'solo_moodle' => 'moodle',
    ];

    foreach ($sets as $set) {
        $salida = ejecutar_nft("list set ip filter $set");
        if (strpos($salida, $subred) !== false) {
            return $mapa[$set];
        }
    }
    return 'cerrado';
}
