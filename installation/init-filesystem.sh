#!/bin/bash

if [ "$EUID" != "0" ]; then
	echo "Root privileges required"
	exit 1
fi

USER="judex"
GROUP="judex-data"

function md() {
	if [ ! -d "$1" ]; then
		mkdir -p "$1"
	fi
}

installation_dir="/opt/judex"
md "$installation_dir"
chown $USER:$USER $installation_dir

data="/var/lib/judex"
md $data

submissions="$data/Submissions"
md "$submissions"

archive="$data/Archive"
md "$archive"

problems="$data/Problems"
md "$problems"

chown -R $USER:$GROUP $data
chmod -R 770 $data

log="/var/log/judex"
md "$log"

chown $USER:$GROUP $log
chmod 770 $log

