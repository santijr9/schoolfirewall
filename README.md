# schoolfirewall
firewall and web front end to open / close Internet surfing in individual classrooms


Cosas a mejorar:

1. Añadir  IPs de profesor de todas aulas como permitidas.

2. BLoquear que solo se pueda acceder al cambio de estados desde las IPs de pcs de profesor.

4. Según si se despliega como bridge firewall o como puerta enlace, adaptar lo que falte por permitir / denegar:
   DHCP, dns, nas, etc.




Mejoras opcionales:

6. iptables → nftables (asumo que por eso el directorio padre se llama filtro-nftables)
El proyecto entero usa iptables, pero nftables es el sucesor en Linux. Si tu distro ya no tiene iptables o quieres modernizarlo, hay que migrar los comandos.

7. Sin CSRF protection
Cualquier sitio web externo podría hacer que un profesor (autenticado) active/desactive aulas sin su consentimiento si hace clic en un enlace malicioso.


