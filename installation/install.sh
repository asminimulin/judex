#!/bin/bash

cd "$(dirname $0)/.."
# We are at the project root

if [ "$EUID" != "0" ]; then
    echo "Installation requires root privileges."
    exit 1
fi

/usr/bin/sh -c ./init-linux-user.sh
/usr/bin/sh -c ./install-dependencies.sh
/usr/bin/sh -c ./init-filesystem.sh
/usr/bin/sh -c ./init-config.sh
/usr/bin/sh -c ./init-database.sh
/usr/bin/sh -c install-site.sh "judex.tech" "/opt/judex/www" "src/judex.tech"
/usr/bin/sh -c ./init-languages.sh
/usr/bin/sh -c ./init-testing-source.sh

tar -xf "res/problems.tar" -C "/var/lib/judex/Problems"

# Control script
cp "utils/judex" "/usr/bin/judex"

