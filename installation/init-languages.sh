#!/bin/bash

if [ "$EUID" != "0" ]; then
    echo "Root privileges required"
    exit 1
fi

if [ ! -d "/opt/judex/languages" ]; then
    mkdir -p "/opt/judex/languages"
fi

# TODO: it is not good, refactor it in further
# We can either
#    1) create special file with list of compiler
#    or
#     2) create special script wich manages with determining what compilers are necessary.
apt -qqqy install g++ python3 fpc
cp -r languages/* "/opt/judex/languages"
