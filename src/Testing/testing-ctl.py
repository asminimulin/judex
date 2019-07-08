#!/usr/bin/python3

# Builtin imports
import os
import sys
import subprocess
import shutil
from configparser import ConfigParser
import time
import socket

# Judex imports
from logger import Logger
from common import *

config = ConfigParser()
config.read('/etc/judex/judex.conf')

def is_running():
    # We guess that if we do not know process pid then system would not working.
    if not os.path.exists(config['judexd']['pid_file']):
        return False
    # Otherwise we check if process with pid from pidfile is running.
    pid = open(config['judexd']['pid_file'], 'r').readline()
    if not pid:
        return False
    try:
        pid = int(pid)
        # Kill with signal 0 does not stop the process. (See man kill)
        # But it handles "No such process error".
        # So we can use it to understand that process is alive or not.
        os.kill(pid, 0)
        return True
    except OSError as r:
        return False

def start():
    if is_running():
        print('Judexd is already running')
        return 1

    proc = subprocess.Popen('/opt/judex/src/Testing/judexd.py')
    time.sleep(1.5)
    if is_running():
        print('judexd has started up')
        return 0
    else:
        print('judexd has not started up')
        return 1

def stop():
    if not is_running():
        print('judexd is not running')
        return 1
    try:
        client = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
        client.connect(config['judexd']['socket'])
        client.send('stop')
        client.close()
        print('judexd has stopped')
        return 0
    except:
        print('Error occured')
        return 1

def restart():
    stop()
    return start()

def on_command(argv):
    if len(argv) < 2:
        print('Usage: loadbalancer.py <start|stop|status>')
        exit(1)
    if argv[1] == 'restart':
      return restart()
    elif argv[1] == 'start':
        return start()
    elif argv[1] == 'stop':
        return stop()
    elif argv[1] == 'status':
        if is_running():
            print('LoadBalancer is running')
        else:
            print('LoadBalancer is stopped')
        return 0
    else:
        print('Unknow command. Usage: loadbalancerctl.py <start|stop|restart|status>')
        return 1

if __name__ == "__main__":
    if os.getuid() != 0:
        print('This command needs super user privileges')
        exit(1)
    else:
        exit(on_command(sys.argv))
