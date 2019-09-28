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
	chmod +x /data/www/smartcan/bin/server_udp
	chmod +x /data/www/smartcan/bin/domocan-bridge
	chmod +x /data/www/smartcan/bin/domocan-bridge-and-web
	chmod +x /data/www/smartcan/bin/rx-DOMOCAN3.php
	chmod 777 /data/www/smartcan/www/conf/*
	chmod 777 /data/www/smartcan/www/conf/config.php
	mkdir /data/www/backups
	chmod 777 /data/www/backups
fi

# Insert Hardware Info towards GPIO
#rm /etc/wiringpi/cpuinfo
mkdir /etc/wiringpi
touch /etc/wiringpi/cpuinfo
echo "Hardware        : ${WiringPiHardware}" >> /etc/wiringpi/cpuinfo
echo "Revision        : ${WiringPiRevision}" >> /etc/wiringpi/cpuinfo
echo "" >> /etc/wiringpi/cpuinfo

# Update Restart Script
#rm /data/sys-files/balena-restart.sh
touch /data/sys-files/balena-restart.sh
echo "#! /bin/bash" >> /data/sys-files/balena-restart.sh
echo "curl -X POST --header "'"'"Content-Type:application/json"'"'" \
--data '{"'"'"appId"'"'": '${AppID}'}' \
"'"'"${BALENA_SUPERVISOR_ADDRESS}/v1/restart?apikey=${BALENA_SUPERVISOR_API_KEY}"'"'"" \
>> /data/sys-files/balena-restart.sh
chmod u+x /data/sys-files/balena-restart.sh

rm -r /opt/init-DB
chmod 0644 /etc/mysql/mariadb.conf.d/50-client.cnf
chmod 777 /usr/local/nginx/conf/
chmod 777 /usr/local/nginx/conf/nginx.conf
chmod 777 /data/www/smartcan/www/images/plans
chmod 777 /data/www/smartcan/www/js/
chmod 777 /data/www/smartcan/www/js/weather.js
chmod 777 /data/www/smartcan/www/html/nest/nest.php

# Update SmartCAN files (if needed)
cp -r /var/www/smartBACKUP/* /data/www/smartcan/
if [ -f /var/www/smartBACKUP/uploads/domotique-update.sql ]; then
	mysql -uroot -pSmartCAN -h localhost domotique < /var/www/smartBACKUP/uploads/domotique-update.sql
	rm -rf /var/www/smartBACKUP/uploads/domotique-update.sql
fi
rm -rf /data/www/smartcan/generate-smartbackuptar.sh
rm -rf /data/www/smartBACKUP/*.sql
# Remove non-persistent files (mysql DB and www files)
rm -rf /var/www
rm -rf /var/lib/mysql

# Start MySQL Server
service mysql start
#mysql -uroot -pSmartCAN -h localhost -e "SHOW DATABASES";

# Start Samba
service smbd start

# Start PHP
service php7.3-fpm start

# Start NGINX
/usr/local/nginx/sbin/nginx -c  /usr/local/nginx/conf/nginx.conf

# Start DomoCAN Server
t1=$(ifconfig -a | grep -o can0)
t2='can0'
if [ "$t1" = "$t2" ]; then
  chmod +x /etc/init.d/domocan-monitor
  chmod +x /etc/init.d/domocan-init
  /etc/init.d/domocan-init start
fi

# Restore crontab Configuration
crontab /data/sys-files/crontab

# Start cron
service cron start

# Start Dump1090 Server (Airplane Radar)
if [ ! -d /data/sys-files/dump1090 ]; then
  cd /srv/dump1090
  ./dump1090 --quiet --no-fix --net-ro-size 500 --net-ro-rate 5 --net-heartbeat 60 --net-http-port 90 --lat $LAT --lon $LONG --gain $GAIN
  # --interactive
fi

# To prevent Docker from exiting
bash
