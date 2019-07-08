#!/bin/bash

##########################################################
#
# That script is used in installation process.
# It seems that you can use it separately.
# Just execute it with correct arguments
#
# Args:
# $1 - path to sql script creating user and database
# $2 - path to sql loading previous mysql-dump
# $3 - name of database created in $1 script
#
##########################################################

##########################################################
#
# TODO:
#   - Create oportunity to use script without root priveleges
#
##########################################################

if [ "$(id -u)" -ne "0" ]; then
  echo "Requires root priveleges"
  exit 1
fi

dump=$1
init=$2
database=$3

mysql <"$init"
mysql "$database" <"$dump"
