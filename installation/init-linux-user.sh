#!/bin/bash

if [ "$EUID" != "0" ]; then
    echo "Root privileges requiered"
    exit 1
fi

PASSWORD="$(</dev/urandom tr -dc _A-Z-a-z-0-9 | head -c16)"
USER="judex"

# Creating linux user if necessary
if ! id -u $USER &>/dev/null; then
    useradd -M -G judex-data $USER
    echo "User <$USER> successfully created"
fi

groupadd judex-data 2>/dev/null
echo "Created group judex-data"
echo "$USER:$PASSWORD" | chpasswd
echo "Password for user <$USER> updated to <$PASSWORD>"
