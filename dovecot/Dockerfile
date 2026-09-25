FROM dovecot/dovecot:2.3-latest

COPY conf/ /etc/dovecot/
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Runs as root (image default) so it can chown /var/mail before starting
# Dovecot. Dovecot itself still drops to uid=5000/gid=5000 per mailbox,
# per dovecot-sql.conf.ext -- root here is only for the ownership fix.
ENTRYPOINT ["/entrypoint.sh"]