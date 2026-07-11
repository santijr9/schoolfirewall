<?php

include 'functions.php';

cabecera();

$aula = $_GET['aula'] ?? '';
$accion = $_GET['accion'] ?? '';

$config = json_decode(file_get_contents('aulas.json'), true);
$aulas = [];
foreach ($config['aulas'] as $a) {
    $aulas[$a['id']] = ['chain' => $a['chain'], 'red' => $a['red']];
}

if (!isset($aulas[$aula])) {
    echo "<h2 style='text-align:center'>Aula inválida</h2>";
    fin();
    exit;
}

$chain = $aulas[$aula]['chain'];
$red = $aulas[$aula]['red'];
$ip_origen = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';

switch ($accion) {
    case 'activar':
        shell_exec("iptables -R $chain 1 -j ACCEPT");
        $msg = 'Internet activado';
        break;
    case 'aules':
        shell_exec("iptables -R $chain 1 -j RETURN");
        $msg = 'Modo solo Aules';
        break;
    case 'desactivar':
        shell_exec("iptables -R $chain 1 -j DROP");
        $msg = 'Internet desactivado';
        break;
    default:
        echo "<h2 style='text-align:center'>Acción inválida</h2>";
        fin();
        exit;
}

if ($accion !== 'activar') {
    shell_exec("conntrack -D -s $red 2>/dev/null");
}

log_evento("$chain | $msg | desde $ip_origen");

?>
    <h2 style="text-align:center"><?= $msg ?></h2>
    <h3 style="text-align:center; color:white" id="counter">3</h3>
    <script>
        setInterval(function() {
            var div = document.querySelector("#counter");
            var count = div.textContent * 1 - 1;
            div.textContent = count;
            if (count <= 0) {
                location.href="http://firewall.test/firewall.php";
            }
        }, 1000);
    </script>

<?php
fin();
?>
