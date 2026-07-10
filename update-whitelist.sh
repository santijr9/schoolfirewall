#!/bin/bash

set -e

TABLE="ip filter"
SET="whitelist"

DOMAINS=(
    "aules.edu.gva.es"
    "portal.edu.gva.es"
    "matomo.gva.es"
    "cdn.jsdelivr.net"
    "fonts.gstatic.com"
    "www.google.com"
    "jnn-pa.googleapis.com"
)

# Flush the current set
nft flush set $TABLE $SET 2>/dev/null || true

for domain in "${DOMAINS[@]}"; do
    IPS=$(dig +short "$domain" | grep -E '^[0-9.]+$')
    for ip in $IPS; do
        nft add element $TABLE $SET { $ip }
    done
done
