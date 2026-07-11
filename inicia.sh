#!/bin/bash

CONFIG="/home/santi/filtro-nftables/schoolfirewallv2/aulas.json"
LOGFILE="/home/santi/filtro-nftables/schoolfirewallv2/aulas.log"

/sbin/iptables -F
/sbin/iptables -X AULES_DEST 2>/dev/null
while IFS=$'\t' read -r chain red; do
  /sbin/iptables -X "$chain" 2>/dev/null
done < <(jq -r '.aulas[] | [.chain, .red] | @tsv' "$CONFIG")

echo "`date +%d-%m-%Y\ %H:%M` -> Reiniciando las reglas"
echo "`date '+%Y-%m-%d %H:%M:%S'` | inicia.sh | ejecutado" >> "$LOGFILE"

# Eliminar conexiones establecidas de todas las aulas
while IFS=$'\t' read -r chain red; do
  /usr/sbin/conntrack -D -s "$red" 2>/dev/null
done < <(jq -r '.aulas[] | [.chain, .red] | @tsv' "$CONFIG")

echo 1 > /proc/sys/net/ipv4/ip_forward

/sbin/iptables -t nat -A POSTROUTING -s 192.168.88.0/24 ! -d 192.168.88.0/24 -j MASQUERADE

while IFS=$'\t' read -r chain red; do
  /sbin/iptables -N "$chain"
  /sbin/iptables -I FORWARD -s "$red" -j "$chain"
done < <(jq -r '.aulas[] | [.chain, .red] | @tsv' "$CONFIG")

/sbin/iptables -N AULES_DEST

/sbin/iptables -A FORWARD -s 192.168.88.10 -j ACCEPT

/sbin/iptables -A FORWARD -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT

/sbin/iptables -A FORWARD -j AULES_DEST

while IFS=$'\t' read -r chain red; do
  /sbin/iptables -A FORWARD -s "$red" -j REJECT --reject-with icmp-port-unreachable
done < <(jq -r '.aulas[] | [.chain, .red] | @tsv' "$CONFIG")

/sbin/iptables -F AULES_DEST
/sbin/iptables -A AULES_DEST -d aules.edu.gva.es -j ACCEPT
/sbin/iptables -A AULES_DEST -d portal.edu.gva.es -j ACCEPT
/sbin/iptables -A AULES_DEST -d matomo.gva.es -j ACCEPT
/sbin/iptables -A AULES_DEST -d cdn.jsdelivr.net -j ACCEPT
/sbin/iptables -A AULES_DEST -d fonts.gstatic.com -j ACCEPT
/sbin/iptables -A AULES_DEST -d jnn-pa.googleapis.com -j ACCEPT
/sbin/iptables -A AULES_DEST -d googleads.g.doubleclick.net -j ACCEPT
/sbin/iptables -A AULES_DEST -d static.doubleclick.net -j ACCEPT

while IFS=$'\t' read -r chain red; do
  /sbin/iptables -A "$chain" -j DROP
done < <(jq -r '.aulas[] | [.chain, .red] | @tsv' "$CONFIG")

# Bloqueo redes sociales (siempre activo, por delante de todo)
/sbin/iptables -I FORWARD 1 -d whatsapp.com -j DROP
/sbin/iptables -I FORWARD 1 -d e3.whatsapp.net -j DROP
/sbin/iptables -I FORWARD 1 -d tiktok.com -j DROP
/sbin/iptables -I FORWARD 1 -d tiktokcdn.com -j DROP
/sbin/iptables -I FORWARD 1 -d instagram.com -j DROP
/sbin/iptables -I FORWARD 1 -d facebook.com -j DROP
/sbin/iptables -I FORWARD 1 -d telegram.org -j DROP
