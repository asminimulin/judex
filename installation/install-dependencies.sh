#!/bin/bash
# Installing Dependencies
echo "Installing dependencies"
apt -qqq update
xargs --arg-file="installation/dependencies/distro" apt -qqq install -y
pip3 -q install -r "installation/dependencies/python3"
