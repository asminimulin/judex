#!/bin/bash

cd "$(dirname $0)/.."
# We are at the project root

if [ "$EUID" != "0" ]; then
    echo "Installation requires root privileges."
    exit 1
fi

/bin/sh -c installation/init-linux-user.sh
/bin/sh -c installation/install-dependencies.sh
/bin/sh -c installation/init-filesystem.sh
/bin/sh -c installation/init-config.sh
/bin/sh -c installation/init-database.sh
/bin/sh -c installation/install-site.sh
/bin/sh -c installation/init-languages.sh
/bin/sh -c installation/init-testing-source.sh

tar -xf "installation/res/problems.tar" -C "/var/lib/judex/Problems"

# Control script
cp "utils/judex" "/usr/bin/judex"

