#!/bin/bash

if [ "$EUID" != "0" ]; then
    echo "Root privileges required"
    exit 1
fi

USER="judex"

from="src/Testing/*"
to="/opt/judex/src/Testing"

mkdir -p $to 2>/dev/null # stderr to /dev/null for ignoring "directory exists" error

cp -r $from $to
chown -R $USER:$USER $to
chmod 770 -R $to

