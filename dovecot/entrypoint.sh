#!/bin/sh
set -e
mkdir -p /var/mail /var/mail-indexes
chown -R 5000:5000 /var/mail /var/mail-indexes

exec /usr/sbin/dovecot -F