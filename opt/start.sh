#!/bin/bash

# Activate can0 Interface
ifup can0

if [ ! -d /data/mysql ]; then
	mkdir -p /data/mysql
	cp -r /var/lib/mysql/* /data/mysql
	#sed -i -e "s@^datadir.*@datadir = /data/mysql@" /etc/mysql/my.cnf
	chown -R mysql:mysql /data/mysql
	mv /var/www /data/www
	mv /var/sys-files /data/sys-files
	chmod 777 /data/www/smartcan/www/conf
	service mysql start
	# Install Initial DBs
	mysql -uroot -pSmartCAN -h localhost < /opt/init-DB/domotique.sql
	mysql -uroot -pSmartCAN -h localhost mysql < /opt/init-DB/mysql.sql
	#gcc /data/www/smartcan/bin/server_udp.c -o /data/www/smartcan/bin/server_udp
	#gcc /data/www/smartcan/bin/domocan-bridge.c -o /data/www/smartcan/bin/domocan-bridge
	#gcc /data/www/smartcan/bin/domocan-bridge-and-web.c -o /data/www/smartcan/bin/domocan-bridge-and-web
	#gcc /data/www/smartcan/bin/domocan-bridge-and-web-FULL.c -o /data/www/smartcan/bin/domocan-bridge-and-web-FULL
	chmod +x /data/www/smartcan/bin/server_udp
	chmod +x /data/www/smartcan/bin/domocan-bridge
	chmod +x /data/www/smartcan/bin/domocan-bridge-and-web
	chmod +x /data/www/smartcan/bin/rx-DOMOCAN3.php
	chmod 777 /data/www/smartcan/www/conf/*
	chmod 777 /data/www/smartcan/www/conf/config.php
	mkdir /data/www/backups
	chmod 777 /data/www/backups
	cp /usr/local/nginx/conf/nginx.conf /data/sys-files/nginx.conf
fi

dos2unix /data/sys-files/boot.sh
chmod a+x /data/sys-files/boot.sh
echo "`/bin/sh /data/sys-files/boot.sh`"
echo "Boot Script Executed"

bash
