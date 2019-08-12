#!/bin/bash

cd "$(dirname $0)/.."
# We are at the project root

if [ "$EUID" != "0" ]; then
    echo "Installation requires root privileges."
    exit 1
fi

/bin/sh -c ./init-linux-user.sh
/bin/sh -c ./install-dependencies.sh
/bin/sh -c ./init-filesystem.sh
/bin/sh -c ./init-config.sh
/bin/sh -c ./init-database.sh
/bin/sh -c install-site.sh "judex.tech" "/opt/judex/www" "src/judex.tech"
/bin/sh -c ./init-languages.sh
/bin/sh -c ./init-testing-source.sh

tar -xf "res/problems.tar" -C "/var/lib/judex/Problems"

# Control script
cp "utils/judex" "/usr/bin/judex"

