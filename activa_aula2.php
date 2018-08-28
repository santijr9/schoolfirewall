<?php

include 'functions.php';

cabecera();


// Eliminamos la regla que hace drop de el aula, y asi pueden navegar por todo internet.
$output = shell_exec('iptables -D FORWARD -s 192.168.60.0/24 -j DROP');

?>
    <h1>Aula activada correctamente. 3 seg.</h1>
    <div id="counter">3</div>
    <script>
        setInterval(function() {
            var div = document.querySelector("#counter");
            var count = div.textContent * 1 - 1;
            div.textContent = count;
            if (count <= 0) {
                location.href="http://info/firewall.php";
            }
        }, 1000);
    </script>

<?php
fin();
?>
