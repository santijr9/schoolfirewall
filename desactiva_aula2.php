<?php

include 'functions.php';

cabecera();

//chdir('/root');

// Añadimos la regla que hace drop de el aula.:
$output = shell_exec('iptables -A FORWARD -s 192.168.60.0/24 -j DROP');
?>
    <h1>Aula  desactivada correctamente. 3 seg.</h1>
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
