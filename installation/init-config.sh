#!/bin/bash

if [ "$EUID" != "0" ]; then
    echo "Root privileges required"
    exit 1
fi

if [ -d "/etc/judex" ]; then
    rm -rf "/etc/judex"
fi
mkdir "/etc/judex"

sed "s!var_timestamp!$(date '+%y/%m/%d %h:%m:%s')!g" "installation/res/judex.conf"> "/etc/judex/judex.conf"

