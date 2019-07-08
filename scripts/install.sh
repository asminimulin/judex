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
    echo \
"<VirtualHost *:80>
    ServerName $servername
    DocumentRoot $site_path
    <Directory $site_path>
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/error.log
	CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>" >"$path"
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
    local version="$(cat '$INSTALLATION_DIR/version')"
    echo "System Judex v$version has already installed."
    if ! getConfirm; then
      echo "Installation aborted"
      exit 1
    fi
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

# Initializing necessary filesystem
JUDEX_HOME="/home/$USER/.judex"
create-system-dir "$JUDEX_HOME"
echo "\$JUDEX_HOME set to $JUDEX_HOME"

JUDEX_CONFIG="/etc/judex"
create-system-dir "$JUDEX_CONFIG"
echo "\$JUDEX_CONFIG set to $JUDEX_CONFIG"

JUDEX_DATA="$JUDEX_HOME/data"
create-system-dir "$JUDEX_DATA"
echo "\$JUDEX_DATA set to $JUDEX_DATA"

JUDEX_SRC="$INSTALLATION_DIR/src"
create-system-dir "$JUDEX_SRC"
echo "\$JUDEX_SRC set to $JUDEX_SRC"

JUDEX_RUN="/run/judex"
echo "\$JUDEX_RUN set to $JUDEX_RUN"

JUDEX_SUBMISSIONS="$JUDEX_DATA/Submissions"
create-system-dir "$JUDEX_SUBMISSIONS"
echo "\$JUDEX_SUBMISSIONS set to $JUDEX_SUBMISSIONS"

JUDEX_PROBLEMS="$JUDEX_DATA/Problems"
create-system-dir "$JUDEX_PROBLEMS"
echo "\$JUDEX_PROBLEMS set to $JUDEX_PROBLEMS"

JUDEX_ARCHIVE="$JUDEX_DATA/Archive"
create-system-dir "$JUDEX_ARCHIVE"
echo "\$JUDEX_ARCHIVE set to $JUDEX_ARCHIVE"

echo "$VERSION" >"$INSTALLATION_DIR/version"

chown $USER:$USER -R "$JUDEX_HOME"

# Create config file
echo "Creating judex.conf..."
config="
# Auto-generated config file.
# Created: $(date '+%Y/%m/%d %H:%M:%S').

[global]
JUDEX_HOME=$JUDEX_HOME
JUDEX_DATA=$JUDEX_DATA
JUDEX_SRC=$JUDEX_SRC
JUDEX_RUN=$JUDEX_RUN
JUDEX_SUBMISSIONS=$JUDEX_SUBMISSIONS
JUDEX_PROBLEMS=$JUDEX_PROBLEMS
JUDEX_ARCHIVE=$JUDEX_ARCHIVE
JUDEX_CONFIG=$JUDEX_CONFIG

[judexd]
pid_file=$JUDEX_RUN/pid_file
testers=$JUDEX_RUN/testers

"
filename="$JUDEX_CONFIG/judex.conf"
echo "$config" >"$filename"
echo "Created config file in $filename"
unset filename config

# Installing Dependencies
echo "Installing dependencies"
apt update
apt install tzdata -y # That package needs to be installed separetly. I had trouble while testing script in docker.
xargs --arg-file="$DEPENDENCIES/distro" apt install -y
pip3 install -r "$DEPENDENCIES/python3"

echo "Initializing database"
service mysql start
mysql/init-database.sh init.sql ../../res/mysql-dump.sql judex
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
