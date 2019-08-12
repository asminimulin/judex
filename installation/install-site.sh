#!/bin/bash

if [ "$EUID" != "0" ]; then
    echo "Root privileges required"
    exit 1
fi

servername="judex.tech"
siteroot="/opt/judex/src/judex.tech"
sitesrc="src/judex.tech"

destination="/etc/apache2/sites-available/$servername.conf"

echo "Initializing site"
sed "s!var_servername!$servername!g; s!var_siteroot!$siteroot!g" "installation/res/default.apache2.conf" >"$destination"
if [ -d "$siteroot" ]; then
    rm -rf "$siteroot"
fi

mkdir -p $siteroot
cp -r $sitesrc/* "$siteroot"

echo "Created site $servername in $siteroot sourced from $sitesrc"

