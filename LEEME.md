# LEEME

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


## 1. setuidar IPTABLES:

	Tendremos que setuidar el ejecutable /sbin/iptables

		chmod u+s /sbin/iptables

		(En nuestro caso el fichero /sbin/xtables-multi)



## 2. Activar enrutamiento

	`echo 1 > /proc/sys/net/ipv4/ip_forward`

Esta activación se borra cuando se apaga el equipo, ya que el directorio /proc está en memoria. Para que dicha activación permanezca lo habitual es definirla en el fichero /etc/sysctl.conf, asegurándonos de que exista una línea como:

	net.ipv4.ip_forward=1



## 3. Inicio / Reinicio Firewall:

Lanzamos en el rc.local un script en cada inicio/reinicio de la máquina, que activa el enrutamiento NAT, y prepara las reglas de salida al moodle, dns y demás cosas que queramos tener siempre permitidas.:

nano /etc/rc.local
	#!/bin/sh -e
	/root/inicia.sh  
	exit 0


## 4. inicia.sh

En este fichero definimos que va a realizar nuestro firewall al iniciar el dia. Cada centro deberá adaptar sus rangos de red y reglas. El contenido de dicho script debe tener todas las rutas absolutas, para evitar problemas de ejecución desde el CRON. Por ejemplo:

Borrar reglas anteriores

	/sbin/iptables -F

Regla fundamental para hacer NAT.:

	iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE

Aceptamos enrutar el trafico de origen 192... al destino 172... (por ejemplo acceso al MOODLE)

	iptables -A FORWARD -s 192.168.150.0/24 -d 172.18.0.35 -j ACCEPT

La ultima regla drop, para denegar todo el trafico de un aula. (Cada aula tiene asignado un rango de clase C)

	iptables -A FORWARD -s 192.168.150.0/24 -j DROP
 


## 5. CRON

Añadimos en el CRON la ejecución del script inicia.sh para que a las horas que deseemos se cierre el internet de las aulas automáticamente. En nuestro caso, lo ejecutamos a las 8.00 que inician las clases, y a las 11.00 justo al finalizar el descanso.

0 8,11 * * * /ruta/script.sh



## 6. APLICATIVO PHP EN DETALLE

Firewall.php
	Esta página lee las reglas actuales y permite al professor activar o desactivar el internet del aula.

Activa_aula.php
	Permite la salida de toda el aula a internet
	
Desactiva_aula.php
	Deniega la salida de toda el aula a internet.

Se deberá adaptar estos ficheros al número de aulas del centro, y a los rangos de IPs.

---


# School Firewall 2.0 — Migración a nftables

A partir de la versión 2.0 se ha migrado de iptables a **nftables**, añadiendo un tercer estado de filtrado: **Solo Moodle**, que permite únicamente los dominios necesarios para el funcionamiento del Moodle (DNS + whitelist de dominios).

Las IPs de los dominios whitelist se actualizan automáticamente vía DNS cada 15 minutos, resolviendo el problema de los rangos IP variables.


## Requisitos del sistema

- Linux con nftables (kernel 3.13+)
- PHP 8.0+
- Servidor web Apache2 o nginx
- `dig` (dnsutils) para resolución DNS
- `sudo` configurado


## 1. Configurar red e IP Forward

```bash
# Activar forwarding
echo 1 > /proc/sys/net/ipv4/ip_forward

# Permanente:
echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf
```


## 2. Ajustar subredes de las aulas

Editar `config.php` y definir las subredes reales de cada aula:

```php
$AULAS = [
    1 => '192.168.70.0/24',
    2 => '192.168.60.0/24',
    3 => '192.168.50.0/24',
    4 => '192.168.40.0/24',
    5 => '192.168.30.0/24',
    6 => '192.168.20.0/24',
];
```

Si hay más o menos aulas, se añaden o quitan elementos del array. El panel se adapta automáticamente.

**Importante:** La interfaz de salida a Internet en `inicia.nft` está configurada como `eth0`. Si tu interfaz tiene otro nombre (ej. `enp1s0`), cámbiala en la línea:

```
oifname "eth0" masquerade
```


## 3. Instalar ruleset nftables

```bash
cp inicia.nft /etc/nftables/inicia.nft

# Probar que la sintaxis es correcta
nft -cf /etc/nftables/inicia.nft
```

Si el test es correcto, cargarlo:

```bash
nft -f /etc/nftables/inicia.nft
```


## 4. Instalar script de actualización dinámica de whitelist

```bash
cp update-whitelist.sh /usr/local/bin/update-whitelist.sh
chmod +x /usr/local/bin/update-whitelist.sh
```

**Probar:**

```bash
/usr/local/bin/update-whitelist.sh
nft list set ip filter whitelist
```

Deberían aparecer las IPs resueltas de los dominios (aules.edu.gva.es, fonts.gstatic.com, etc.).


## 5. Configurar sudo para el panel web

El panel web (PHP) se ejecuta como `www-data` y necesita ejecutar `nft` como root para modificar los sets.

```bash
cp sudoers-www-data /etc/sudoers.d/www-data-nft
chmod 440 /etc/sudoers.d/www-data-nft

# Verificar sintaxis
visudo -c
```


## 6. Inicio automático al arrancar (rc.local o systemd)

### Opción A: rc.local

```bash
echo '#!/bin/sh -e
/root/schoolfirewall/inicia.sh
exit 0' > /etc/rc.local
chmod +x /etc/rc.local
```

### Opción B: systemd

```ini
# /etc/systemd/system/school-firewall.service
[Unit]
Description=School Firewall nftables
After=network.target nftables.service

[Service]
Type=oneshot
ExecStart=/root/schoolfirewall/inicia.sh
RemainAfterExit=true

[Install]
WantedBy=multi-user.target
```

```bash
systemctl enable --now school-firewall.service
```


## 7. Programar actualización automática de IPs (cron)

```bash
# Añadir como root (crontab -e)
*/15 * * * * /usr/local/bin/update-whitelist.sh
```


## 8. Desplegar panel web en Apache2

```bash
cp -r schoolfirewall /var/www/html/schoolfirewall
chown -R www-data:www-data /var/www/html/schoolfirewall
```

Proteger con autenticación (ej. auth-digest):

```bash
htdigest -c /etc/apache2/.htdigest "School Firewall" profesor
```

Y en la configuración del virtualhost de Apache2:

```apache
<Directory /var/www/html/schoolfirewall>
    AuthType Digest
    AuthName "School Firewall"
    AuthDigestDomain /var/www/html/schoolfirewall/firewall.php
    AuthDigestProvider file
    AuthUserFile /etc/apache2/.htdigest
    Require valid-user
</Directory>
```


## 9. Estructura del proyecto

| Archivo | Descripción |
|---|---|
| `inicia.nft` | Ruleset nftables (NAT + filtro con 3 estados) |
| `update-whitelist.sh` | Script cron que resuelve dominios y actualiza el set whitelist |
| `config.php` | Configuración centralizada de aulas, subredes y nombres |
| `firewall.php` | Dashboard web que muestra estado de cada aula |
| `toggle.php` | Handler que cambia el estado de un aula (abierto/cerrado/moodle) |
| `functions.php` | Funciones helper (cabecera, fin, ejecutar_nft, estado_aula) |
| `sudoers-www-data` | Regla sudo para que www-data ejecute nft |


## Estados del firewall

| Estado | Comportamiento |
|---|---|
| 🔓 Abierto | Tráfico libre (todos los puertos) |
| 🟡 Solo Moodle | Solo DNS (53) + HTTPS a dominios whitelist |
| 🔴 Cerrado | Todo bloqueado |


## Dominios whitelist (modo Solo Moodle)

- `aules.edu.gva.es`
- `portal.edu.gva.es`
- `matomo.gva.es`
- `cdn.jsdelivr.net`
- `fonts.gstatic.com`
- `www.google.com`
- `jnn-pa.googleapis.com`

Para añadir o quitar dominios, editar `update-whitelist.sh` y recargar la whitelist.


