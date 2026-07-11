# Instalación y despliegue

## Requisitos del sistema

- **SO:** Debian 13 (o cualquier distro con iptables v1.8+ y nf_tables)
- **Arquitectura:** x86_64
- **Espacio en disco:** ~50 MB para el proyecto y logs
- **Interfaces de red:** mínimo 2 (LAN interna + WAN/salida)

## Topologías soportadas

### Modo puerta de enlace (gateway)

El servidor actúa como gateway por defecto de las aulas. El tráfico de los clientes
pasa a través de `FORWARD`:

```
     WAN <──> [eth0: servidor] <──> [eth1: LAN aulas 192.168.88.0/24, 172.29.x.x/25 ...]
                                         ↑
                                   clientes usan
                                   esta IP como gateway
```

- Se necesita `ip_forward = 1` y MASQUERADE (ya incluido en `inicia.sh`)
- DHCP/DNS deben apuntar a este servidor como gateway desde cada subnet

### Modo bridge firewall (transparente)

El servidor se coloca entre el router de salida y el switch de aulas sin cambiar
direcciones IP:

```
     WAN <──> [router existente] <──> [puerto A - puerto B: servidor en bridge] <──> switch aulas
```

- `inicia.sh` usa las mismas reglas en `FORWARD`
- **No es necesario** `ip_forward` ni MASQUERADE (el router existente hace NAT)
- Solo deben cambiarse las interfaces de bridge por las reales (no aplica en este script)

---

## Dependencias

```bash
apt update
apt install -y apache2 php iptables conntrack-tools jq
```

| Paquete | Versión mínima | Motivo |
|---|---|---|
| `apache2` | 2.4.x | Servidor web para el panel |
| `php` | 8.x | `shell_exec()` para ejecutar iptables |
| `iptables` | 1.8+ (nf_tables) | Reglas de filtrado |
| `conntrack-tools` | 1.4.x | Flush de conexiones establecidas al cambiar estado |
| `jq` | 1.6+ | Parseo de `aulas.json` desde bash |

---

## Instalación

### 1. Copiar los ficheros

```bash
mkdir -p /opt/schoolfirewall
cp -r schoolfirewallv2/* /opt/schoolfirewall/
# o clonar desde git si corresponde
```

### 2. Configurar Apache

```bash
cat > /etc/apache2/sites-available/schoolfirewall.conf << 'EOF'
<VirtualHost *:80>
    ServerName firewall.aulas
    DocumentRoot /opt/schoolfirewall

    <Directory /opt/schoolfirewall>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /var/log/apache2/schoolfirewall-error.log
    CustomLog /var/log/apache2/schoolfirewall-access.log combined
</VirtualHost>
EOF

a2ensite schoolfirewall
systemctl reload apache2
```

> Si prefieres autenticación Digest, añade dentro del `<Directory>`:
> ```apache
> AuthType Digest
> AuthName "Firewall aulas"
> AuthUserFile /opt/schoolfirewall/.htdigest
> Require valid-user
> ```
> Y genera el fichero con `htdigest -c /opt/schoolfirewall/.htdigest "Firewall aulas" admin`

### 3. Dar permisos a iptables y conntrack

El panel web ejecuta `iptables` y `conntrack` desde PHP. iptables ya incluye
`cap_net_admin` de serie en su binario (`xtables-nft-multi`), pero `conntrack`
necesita el permiso explícitamente:

```bash
setcap cap_net_admin+ep /usr/sbin/conntrack
```

### 4. Ajustar rutas en los scripts

Edita `/opt/schoolfirewall/inicia.sh` y `/opt/schoolfirewall/actualiza_aules.sh`
si cambiaste la ruta de instalación:

```bash
# inicia.sh — líneas 3-4
CONFIG="/opt/schoolfirewall/aulas.json"
LOGFILE="/opt/schoolfirewall/aulas.log"

# actualiza_aules.sh si es necesario
```

Asegúrate de que `aulas.log` tenga permisos de escritura para www-data:

```bash
touch /opt/schoolfirewall/aulas.log
chmod 666 /opt/schoolfirewall/aulas.log
```

### 5. Configurar las aulas

Edita `aulas.json` con tus rangos de red. Formato:

```json
{
  "aulas": [
    {"id": 1,  "chain": "AULA1_CTRL",  "red": "192.168.88.0/24",    "nombre": "Aula 1 - LAN"},
    {"id": 3,  "chain": "AULA3_CTRL",  "red": "172.29.232.0/25",   "nombre": "Aula 3"}
  ]
}
```

- `id`: número de aula (se pasa como `?aula=N` en la URL)
- `chain`: nombre de la cadena iptables (debe ser único, máximo 28 caracteres)
- `red`: subnet en notación CIDR
- `nombre`: texto visible en el panel

### 6. Ejecutar el firewall por primera vez

```bash
bash /opt/schoolfirewall/inicia.sh
```

Comprueba que las reglas están activas:

```bash
iptables -nL FORWARD
```

### 7. Programar el refresco DNS

AULES_DEST resuelve los dominios educativos en el momento de crear las reglas.
Para mantener las IPs actualizadas, añade un cron:

```bash
crontab -e
```

Añade la línea:

```
*/15 * * * * /opt/schoolfirewall/actualiza_aules.sh
```

### 8. Ejecutar inicia.sh al arranque del servidor

Para que el firewall se active automáticamente al encender el servidor, crea un
servicio systemd:

```bash
cat > /etc/systemd/system/schoolfirewall.service << 'EOF'
[Unit]
Description=Firewall de aulas
After=network.target

[Service]
Type=oneshot
ExecStart=/bin/bash /opt/schoolfirewall/inicia.sh
RemainAfterExit=yes

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable schoolfirewall
systemctl start schoolfirewall
```

> También puedes usar `/etc/rc.local` si prefieres, pero systemd es más fiable
> porque espera a que la red esté lista.

### 9. Cierre automático tras los descansos (opcional)

Si quieres que **todas las aulas vuelvan al estado desactivado** automáticamente
después de los patios/recreos, programa `inicia.sh` en el cron del sistema:

```bash
crontab -e
```

Ejemplo para un horario de centro con dos recreos (11:30 y 13:00) y fin de
jornada (15:30):

```
# Reiniciar el firewall (todo desactivado) tras cada descanso
30 11 * * 1-5 /opt/schoolfirewall/inicia.sh
0 13 * * 1-5 /opt/schoolfirewall/inicia.sh
30 15 * * 1-5 /opt/schoolfirewall/inicia.sh
```

Esto ejecuta `inicia.sh`, que pone todas las aulas en `DROP` (desactivado) y
elimina las conexiones establecidas. Los profesores tendrán que volver a activar
el internet desde el panel si lo necesitan.

---

## Modo bridge (firewall transparente)

Si despliegas en modo bridge, necesitas:

1. **No usar MASQUERADE** — Comenta la línea 22 de `inicia.sh`
2. **Adaptar `ip_forward`** — Según tu configuración de bridge puede ser necesario o no
3. **Añadir reglas para tráfico local** — Si el bridge no enruta, el tráfico DHCP, DNS,
   etc. puede necesitar `ACCEPT` explícito en `FORWARD`

---

## Registro de eventos

Todas las acciones se registran en `aulas.log`:

```
2026-07-11 20:00:37 | inicia.sh | ejecutado
2026-07-11 20:00:46 | actualiza_aules.sh | ejecutado
2026-07-11 20:01:00 | AULA1_CTRL | Internet activado | desde 192.168.88.10
```

Monitorizar en vivo:

```bash
tail -f /opt/schoolfirewall/aulas.log
```

---

## Notas de seguridad

- El panel no tiene autenticación por defecto (salvo que añadas Digest como se indica arriba).
  Cualquier cliente en la red de un aula puede cambiar el estado si conoce las URLs.
- El log `aulas.log` es world-writable (`666`) para que Apache pueda escribirlo.
  Ajusta permisos si el entorno lo requiere.
- Las reglas de redes sociales se insertan con `-d dominio` (resolución DNS en el
  momento de añadir la regla). Si el dominio cambia de IP, la regla queda obsoleta.
  Para un bloqueo fiable habría que usar un enfoque similar a `actualiza_aules.sh`.
