#!/usr/bin/bash

if [ "$EUID" != "0" ]; then
	echo "Root privileges required"
	exit 1
fi

mysql <"installation/init-database.sql"
mysql -u judex -ppassword judex <"installation/res/dump.sql"

