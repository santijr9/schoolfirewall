README.txt

SANTI GIMENO.  Agosto 2018. 	gimeno.profe@gmail.com

Después de la positiva experiencia durante el curso 17-18 implantando el firewall en el centro, y haber obtenido un 100% de disponibilidad, he decidido compartir este proyecto.

El proyecto se comparte mediante licencia Creative Commons
Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA 4.0)

School Firewall 1.0
-------------------

Se trata de un acceso web para lanzar reglas sencillas IPtables de manera que un profesor puede abrir o cerrar Internet en el aula. Cuando inician las clases, internet está cerrado en todas las aulas. Cuando el profesor termina de explicar, si la tarea requiere internet, puede activarlo a toda el aula.

Una web en concreto, puede quedar siempre activa. En nuestro caso tenemos el Moodle del centro siempre accesible.

La máquina que haga de firewall y salida del tráfico del centro, deberá tener instalado un servidor web con PHP. Se recomienda utilizar algún tipo de autenticación para que solo los profesores puedan acceder a abrir o cerrar internet en las aulas.


INSTRUCCIONES DE IMPLANTACIÓN
-----------------------------


1. setuidar IPTABLES:

	Tendremos que setuidar el ejecutable /sbin/iptables

		`chmod u+s /sbin/iptables`

		(En nuestro caso el fichero /sbin/xtables-multi)



2. Activar enrutamiento

	`echo 1 > /proc/sys/net/ipv4/ip_forward`

Esta activación se borra cuando se apaga el equipo, ya que el directorio /proc está en memoria. Para que dicha activación permanezca lo habitual es definirla en el fichero /etc/sysctl.conf, asegurándonos de que exista una línea como:

	net.ipv4.ip_forward=1



3. Inicio / Reinicio Firewall:

Lanzamos en el rc.local un script en cada inicio/reinicio de la máquina, que activa el enrutamiento NAT, y prepara las reglas de salida al moodle, dns y demás cosas que queramos tener siempre permitidas.:

nano /etc/rc.local
	#!/bin/sh -e
	/root/inicia.sh  
	exit 0


4. inicia.sh

En este fichero definimos que va a realizar nuestro firewall al iniciar el dia. Cada centro deberá adaptar sus rangos de red y reglas. El contenido de dicho script debe tener todas las rutas absolutas, para evitar problemas de ejecución desde el CRON. Por ejemplo:

Borrar reglas anteriores

	/sbin/iptables -F

Regla fundamental para hacer NAT.:

	iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE

Aceptamos enrutar el trafico de origen 192... al destino 172... (por ejemplo acceso al MOODLE)

	iptables -A FORWARD -s 192.168.150.0/24 -d 172.18.0.35 -j ACCEPT

La ultima regla drop, para denegar todo el trafico de un aula. (Cada aula tiene asignado un rango de clase C)

	iptables -A FORWARD -s 192.168.150.0/24 -j DROP
 


5. CRON

Añadimos en el CRON la ejecución del script inicia.sh para que a las horas que deseemos se cierre el internet de las aulas automáticamente. En nuestro caso, lo ejecutamos a las 8.00 que inician las clases, y a las 11.00 justo al finalizar el descanso.

0 8,11 * * * /ruta/script.sh



6. APLICATIVO PHP EN DETALLE

Firewall.php
	Esta página lee las reglas actuales y permite al professor activar o desactivar el internet del aula.

Activa_aula.php
	Permite la salida de toda el aula a internet
	
Desactiva_aula.php
	Deniega la salida de toda el aula a internet.

Se deberá adaptar estos ficheros al número de aulas del centro, y a los rangos de IPs.


