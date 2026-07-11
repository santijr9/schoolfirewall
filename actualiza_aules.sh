#!/bin/bash

# Refresca las IPs de los dominios de AULES en la cadena AULES_DEST
# Se ejecuta cada 15 minutos via cron

/sbin/iptables -F AULES_DEST

/sbin/iptables -A AULES_DEST -d aules.edu.gva.es -j ACCEPT
/sbin/iptables -A AULES_DEST -d portal.edu.gva.es -j ACCEPT
/sbin/iptables -A AULES_DEST -d matomo.gva.es -j ACCEPT
/sbin/iptables -A AULES_DEST -d cdn.jsdelivr.net -j ACCEPT
/sbin/iptables -A AULES_DEST -d fonts.gstatic.com -j ACCEPT
/sbin/iptables -A AULES_DEST -d jnn-pa.googleapis.com -j ACCEPT
/sbin/iptables -A AULES_DEST -d googleads.g.doubleclick.net -j ACCEPT
/sbin/iptables -A AULES_DEST -d static.doubleclick.net -j ACCEPT

echo "`date +%d-%m-%Y\ %H:%M` -> AULES_DEST actualizado"
echo "`date '+%Y-%m-%d %H:%M:%S'` | actualiza_aules.sh | ejecutado" >> /home/santi/filtro-nftables/schoolfirewallv2/aulas.log
