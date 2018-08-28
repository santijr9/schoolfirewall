#!/bin/bash


## Limpiamos las reglas del firewall.
/sbin/iptables -F
echo "inicia"
## activamos enrutamiento NAT
/sbin/iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE

## ASIGNAMOS UNA CLASE C para cada aula:
#
# Aula 2CF --->  192.168.60.0/24
# Aula 1CF --->  192.168.70.0/24
#


## activamos las reglas de salida a dns y moodle en las aulas:

## Aula de 2º
/sbin/iptables -A FORWARD -s 192.168.60.0/24 -d 8.8.8.8 -j ACCEPT
/sbin/iptables -A FORWARD -s 192.168.60.0/24 -d 213.176.163.212 -j ACCEPT
/sbin/iptables -A FORWARD -s 192.168.60.0/24 -j DROP >> /var/log/mifirewall.log

## Aula de 1º
/sbin/iptables -A FORWARD -s 192.168.70.0/24 -d 8.8.8.8 -j ACCEPT
/sbin/iptables -A FORWARD -s 192.168.70.0/24 -d 213.176.163.212 -j ACCEPT
/sbin/iptables -A FORWARD -s 192.168.70.0/24 -j DROP >> /var/log/mifirewall.log



# UTILIZANDO IPSET SERIA ASI:

## definimos las Listas de ips de las aulas:
#/sbin/ipset -! -N Aula1 iphash
#/sbin/ipset -N Aula2 iphash
#/sbin/ipset -N AulaPetita iphash
#/sbin/ipset -N AulaBlava iphash
#/sbin/ipset -N AulaGroga iphash

# AULA 2º cicles formatius
#ipset -A Aula2 192.168.60.1 -!
#ipset -A Aula2 192.168.60.2 -!
#ipset -A Aula2 192.168.60.3 -!
#ipset -A Aula2 192.168.60.4 -!
#ipset -A Aula2 192.168.60.5 -!
#...

## activamos las reglas de salida a dns y moodle en las aulas
#/sbin/iptables -A FORWARD -m set --match-set Aula1 src -d 8.8.8.8 -j ACCEPT
#/sbin/iptables -A FORWARD -m set --match-set Aula1 src -d 81.61.138.24 -j ACCEPT
#/sbin/iptables -A FORWARD -m set --match-set Aula1 src -j DROP >> /var/log/mifirewall.log





