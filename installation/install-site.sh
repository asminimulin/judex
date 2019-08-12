#!/bin/bash

# Args:
# $1 - servername
# $2 - siteroot
# $3 - sitesrc

echo "$#"

if [ "$EUID" != "0" ]; then
	echo "Root privileges required"
	exit 1
fi

cd "$(dirname $0)"

if [ "$#" -lt "3" ]; then
	echo "Usage: install.sh <servername> <siteroot> <sitesrc>"
	exit 1
fi

servername="$1"

if [ ! -d "$(dirname "$2")" ]; then
	echo "Cannot use $2 as siteroot"
	exit 1
else
	siteroot="$2"
	if [ -d "$siteroot" ] && [ "$siteroot" != "/" ]; then
		rm -rf "$siteroot"
	fi
fi

if [ ! -d "$3" ]; then
	echo "No site located in directory $3"
else
	sitesrc="$3"
fi

destination="/etc/apache2/sites-available/$servername.conf"

echo "Initializing site"
sed "s!var_servername!$servername!g; s!var_siteroot!$siteroot!g" "installation/res/default.apache2.conf" >"$destination"
if [ -d "$siteroot" ]; then
	rm -rf "$siteroot"
fi
cp -r "$sitesrc" "$siteroot"
echo "Created site $servername in $siteroot sourced from $sitesrc"

