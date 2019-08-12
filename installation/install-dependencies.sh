#!/bin/bash
# Installing Dependencies
echo "Installing dependencies"
apt -qq update
xargs --arg-file="installation/dependencies/distro" apt -qq install -y
pip3 -q install -r "installation/dependencies/python3"
