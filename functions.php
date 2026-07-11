<?php

function cabecera(){
echo "<!DOCTYPE html>
<html >
<head>
  <meta charset=\"UTF-8\">
  <title>Firewall de aulas</title>
  <link rel=\"icon\" href=\"logo-cheste.png\">
  <!-- <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css\"> -->
  <link rel=\"stylesheet\" href=\"css/normalize.min.css\">
  <link rel=\"stylesheet\" href=\"css/style.css\">

</head>

<body>
  <div class=\"login\">
";

}

function fin(){

echo"
<!-- <div class=\"utilities\">
    	<input name=\"salir\" type=\"button\" onClick=\"javascript:window.close();\" value=\"Salir\">
    	<a href=\"javascript:window.close();\"> Cerrar </a>
  </div>
</div>
-->

</body>
</html>


";

}

function log_evento($mensaje) {
    $log = "/home/santi/filtro-nftables/schoolfirewallv2/aulas.log";
    $linea = date("Y-m-d H:i:s") . " | $mensaje\n";
    file_put_contents($log, $linea, FILE_APPEND | LOCK_EX);
}

function ip_pertenece_a_red($str_ip, $str_rango){
    // Extraemos la máscara
    list($str_red, $str_mascara) = array_pad(explode('/', $str_rango), 2, NULL);
    if( is_null($str_mascara) ){
        // No se especifica máscara: el rango es una única IP
        $mascara = 0xFFFFFFFF;
    }elseif( (int)$str_mascara==$str_mascara ){
        // La máscara es un entero: es un número de bits
        $mascara = 0xFFFFFFFF << (32 - (int)$str_mascara);
    }else{
        // La máscara está en formato x.x.x.x
        $mascara = ip2long($str_mascara);
    }

    $ip = ip2long($str_ip);
    $red = ip2long($str_red);
    $inf = $red & $mascara;
    $sup = $red | (~$mascara & 0xFFFFFFFF);

    return $ip>=$inf && $ip<=$sup;
}


?>
