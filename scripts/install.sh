#!/bin/bash

VERSION=1.0

if [ "$EUID" != "0" ]; then
    echo "Installation requires root privileges."
    exit 1
fi

if [ "$0" != "./install.sh" ]; then
  cd "$(dirname $0)/.."
else
  cd ..
fi
# Now we are at $JUDEX_HOME

function install-site() {
    # Args:
    # $1 - servername
    # $2 - site-path

    echo "Installing site..."
    local servername=$1
    local path="/etc/apache2/sites-available/$servername.conf"
    local site_path=$2
    cp "res/apache2-site-configuration.conf" "$path"
    sed -i "s#VAR_SERVERNAME#$servername#g" "$path"
    sed -i "s#VAR_SITE_ROOT#$site_path#g" "$path"
    cp -r "./src/judex.tech" "$site_path"
    echo "Created site $servername on $site_path"
}

function error() {
    echo "Installation aborted.\n$1"
}

function read-password {
    if [[ "$#" -lt "1" ]]; then
        echo "Usage $0 <password-variable"
        exit 1
    fi
    local -n link=$1
    local password
    local confirm
    echo "Enter new password:"
    read -s password
    if [ -z "$password" ]; then
        echo "Empty password does not available"
        return 1
    fi
    echo "Repeat password:"
    read -s confirm
    if [ "$password" != "$confirm" ]; then
        echo "Passwords mismatch"
        return 1
    fi
    link="$password"
}

function create-system-dir() {
    if [[ "$#" -lt "1" ]]; then
        echo "Usage: $0 <PATH>"
        exit 1
    fi
    local path=$1
    if [[ ! -d $path ]]; then
        mkdir $path
    else
        echo "Found existing $path"
    fi
}

function getConfirm() {
  echo "Do you want to continue?[y/n]"
  local ack
  read ack
  case "$ack" in
    n)
      return 1
    ;;
    no)
      return 1
    ;;
    N)
      return 1
    ;;
    No)
      return 1
    ;;
    NO)
      return 1
  esac
  return 0
}


DEPENDENCIES="./dependencies"

INSTALLATION_DIR="/opt/judex"

if [ -f "$INSTALLATION_DIR/version" ]; then
    version="$(cat "$INSTALLATION_DIR/version")"
    echo "System Judex v$version has already installed."
    if ! getConfirm; then
      echo "Installation aborted"
      exit 1
    fi
    unset version
fi

rm -rf "$INSTALLATION_DIR"
create-system-dir "$INSTALLATION_DIR"

USER="judex-master"
DEVMODE=0

# Creating linux user if necessary
if ! id -u $USER &>/dev/null; then
    echo "Linux user $USER will be created"

    # Getting new unix user password
    for ((i = 1;i <= 3;++i)); do
        read-password PASSWORD && break
        if [ "$DEVMODE" == "1" ]; then
            echo "read-password FAILED"
        fi
    done
    if [ -z "$PASSWORD" ]; then
        error "Failed to create Linux user. Password is empty."
        exit 1
    fi

    useradd -s /bin/bash -m $USER
    echo "$USER:$PASSWORD" | chpasswd

    if [ $DEVMODE == "1" ]; then
        echo "User <$USER> with password <$PASSWORD> successfully created"
    else
        echo "User <$USER> successfully created"
    fi
else
    echo "User $USER exists"
fi

# Creating group for data
groupadd judex-data

# Initializing necessary filesystem
JUDEX_HOME="/home/$USER/.judex"
create-system-dir "$JUDEX_HOME"

JUDEX_CONFIG="/etc/judex"
create-system-dir "$JUDEX_CONFIG"

JUDEX_SRC="$INSTALLATION_DIR/src"
create-system-dir "$JUDEX_SRC"

JUDEX_DATA="/var/lib/judex"
create-system-dir "$JUDEX_DATA"

JUDEX_RUN="/run/judex"

JUDEX_SUBMISSIONS="$JUDEX_DATA/Submissions"
create-system-dir "$JUDEX_SUBMISSIONS"

JUDEX_PROBLEMS="$JUDEX_DATA/Problems"
create-system-dir "$JUDEX_PROBLEMS"
tar -xf "res/problems.tar" -C "$JUDEX_DATA"

JUDEX_ARCHIVE="$JUDEX_DATA/Archive"
create-system-dir "$JUDEX_ARCHIVE"

JUDEX_LOG="/var/log/judex"
create-system-dir "$JUDEX_LOG"

echo "$VERSION" >"$INSTALLATION_DIR/version"

chown -R "$USER":"judex-data" "$JUDEX_DATA"

# Creating configs
rm -rf "/etc/judex"
cp -r "res/conf/" "/etc/judex"
sed -i "s#VAR_CREATING_TIMESTAMP#$(date '+%y/%m/%d %h:%m:%s')#g" "/etc/judex/judex.conf"

# Installing Dependencies
echo "Installing dependencies"
apt -qq update
xargs --arg-file="$DEPENDENCIES/distro" apt -qq install -y
pip3 -q install -r "$DEPENDENCIES/python3"

echo "Initializing database"
service mysql start
scripts/mysql/init.sh "scripts/mysql/init.sql" "res/mysql-dump.sql" "judex"
echo "Database successfully initialized"

# Copying code
## Control script
cp "scripts/judex" "/usr/bin"

## Site installation
if [[ "$DEVMODE" == "1" ]]; then
    install-site "$USER-dev.judex.tech" "$JUDEX_SRC/judex.tech"
else
    install-site "judex.tech" "$JUDEX_SRC/judex.tech"
fi

## Testing installation
cp -r "./src/Testing" "$JUDEX_SRC/Testing"
