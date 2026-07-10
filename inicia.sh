#!/bin/bash

# Carga las reglas nftables (sustituye al antiguo sistema iptables)
nft -f /etc/nftables/inicia.nft

# Actualiza la whitelist con las IPs actuales de los dominios
/usr/local/bin/update-whitelist.sh
