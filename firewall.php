
<?php

include 'functions.php';

cabecera();

//Definimos variables para cada Aula    - $Aula1  $Aula2....
// 1 para internet activado.
// 0 pàra internet cerrado. (solo moodle).

// chdir('/root');

	$reglas = shell_exec('iptables -nL | grep DROP');
	print $reglas;

	print "<h1>Botó d'Internet</h1>";

	//En funcion de las reglas, rellenamos las variables de las aulas


	// AULA 1 CF
	if(strpos($reglas, '192.168.70.0/24') !== false) $Aula1 = 0;
	else $Aula1 = 1;

	// AULA 2 CF
	if(strpos($reglas, '192.168.60.0/24') !== false) $Aula2 = 0;
	else $Aula2 = 1;

	//AQUI FALTA RELLENAR EL RESTO DE VARIABLES DE LAS AULAS.

	//MOSTRAMOS LA TABLA

	print "<table border=0>";
	print "<tr><td width='50'>Aula 1</td></td>";

	if($Aula1==1){
		print "<td width='80'><img width='40' height='40' src='verde.png'></td>";
		echo "<td><button onClick=\"window.location.href='desactiva_aula1.php'\">Desactivar</button></td>";

	}
	else{	print "<td width='80'><img width='40px' height='40px' src='rojo.png'></td>";
		echo "<td><button onClick=\"window.location.href='activa_aula1.php'\">Activar</button></td>";

	}
	print "</tr>";

	print "<tr><td width='50'>Aula 2</td></td>";

	if($Aula2==1){
		print "<td width='80'><img width='40' height='40' src='verde.png'></td>";
		echo "<td><button onClick=\"window.location.href='desactiva_aula2.php'\">Desactivar</button></td>";

	}
	else{	print "<td width='80'><img width='40px' height='40px' src='rojo.png'></td>";
		echo "<td><button onClick=\"window.location.href='activa_aula2.php'\">Activar</button></td>";

	}
	print "</tr>";

/*
	print "<tr><td width='50'>Aula 3</td></td>";



	if($Aula3==1){
		print "<td width='80'><img width='40' height='40' src='verde.png'></td>";
		echo "<td><button onClick=\"window.location.href='desactiva_aula.php'\">Desactivar</button></td>";

	}
	else{	print "<td width='80'><img width='40px' height='40px' src='rojo.png'></td>";
		echo "<td><button onClick=\"window.location.href='activa_aula.php'\">Activar</button></td>";

	}
	print "</tr>";

	print "<tr><td width='50'>Aula 4</td></td>";

	if($Aula4==1){
		print "<td width='80'><img width='40' height='40' src='verde.png'></td>";
		echo "<td><button onClick=\"window.location.href='desactiva_aula.php'\">Desactivar</button></td>";

	}
	else{	print "<td width='80'><img width='40px' height='40px' src='rojo.png'></td>";
		echo "<td><button onClick=\"window.location.href='activa_aula.php'\">Activar</button></td>";

	}
	print "</tr>";

	*/

	print "</table>";

fin();

?>
