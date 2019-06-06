#!/bin/bash

# TODO:
# Configure environment variables JUDEX_HOME JUDEX_DATA
# Load mysqldump 
# 

if [[ $EUID -ne 0 ]]; then
    echo "Installation needs root privileges. Use sudo or run as root."
    exit 1
fi

install_packages() {
    apt update
    apt install -y \
        python3 \
        vim \
        g++ \
        python3-pip \
        php \
        apache2 \
        mysql-server \
        htop \
	git
    pip3 install \
        pymysql \
        psutil
}

init_filesystem() {
    submissions="$JUDEX_DATA/Submissions"
    problems="$JUDEX_DATA/Problems"

    if [ -d "$JUDEX_DATA" ]; then
        echo "Directory $JUDEX_DATA already exists."
        echo "Do you want to continue installation(it will override this directory)?[Y/N]"
        local ans
        read ans
        if [ "$ans" == "N" ]; then
            echo "Installation refused"
            exit 0
        else
            rm -rf "$JUDEX_DATA"
        fi
    fi

    mkdir "$JUDEX_DATA"
    mkdir "$submissions"
    mkdir "$problems"
    chown --recursive $user:$user "$JUDEX_DATA"
    chmod --recursive 774 "$JUDEX_DATA"
}

parse_arguments() {
    while [[ $# -gt 0 ]]; do
        arg="$1"
        case $arg in
            --debug)
                debug=1
                shift
            ;;
            --no-install)
                install=0
                shift
            ;;
            --user)
                shift
                user=$1
                shift
            ;;
            *)
                echo "Unknow option $arg"
                exit 1
            ;;
        esac
    done
}

init_database() {
    echo "INIT DATABASE NOT IMPLEMENTED";
}

init_environment() {
    JUDEX_DATA="/srv/judex"
}

install=1
debug=0
user="NO_USER_CHOSEN"

parse_arguments $@

if [[ $debug -ne 0 ]]; then 
    echo "Debug"
fi

if [[ $install -ne 0 ]]; then
    echo "Install packages"
    install_packages
else
    echo "No install packages"
fi

if [ "$user" == "NO_USER_CHOSEN" ] || [ -z "$user" ]; then
    echo "No user specified"
    exit 1
fi

init_filesystem 
init_database
