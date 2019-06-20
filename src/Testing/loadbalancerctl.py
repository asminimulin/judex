#!/usr/bin/python3

# Builtin imports
import os
import sys
import subprocess
import shutil
from configparser import ConfigParser
import time

# Package imports
import connector
from logger import Logger
from common import *

config = ConfigParser()
config_path = os.path.join(JUDEX_HOME, 'conf.d', 'judex.conf')
config.read(config_path)

def is_running():
    # We guess that system is running while we know it's Process ID
    return os.path.exists(config['loadbalancer']['pid_file'])

def create_file(path, directory=False, fifo=False, text_file=False):
    if directory + fifo + text_file != 1:
        raise Exception('create_file expects that exactly one option is True')
    if not os.path.exists(path):
        if directory:
            os.mkdir(path)
        elif fifo:
            os.mkfifo(path)
        else:
            open(path, 'w').close()

def start():

    if is_running():
        print('LoadBalancer is already running')
        return

    def init_fs():
        create_file(config['loadbalancer']['dir'], directory=True)
        create_file(config['loadbalancer']['pid_file'], text_file=True)
        create_file(config['log']['path'], text_file=True)

    init_fs()

    proc = subprocess.Popen(['python3', os.path.join(JUDEX_HOME, 'Testing', 'loadbalancer.py')])
    
    time.sleep(2)

    # We must create connector. If we dont then loadbalancer.py would wait for opening fifos in it's __init__ function.
    # Fix that behaviour is nice issue
    conn = connector.ParentConnector(config['loadbalancer']['out_pipe'], config['loadbalancer']['in_pipe'])

    with open(config['loadbalancer']['pid_file'], 'w') as pid_file:
        pid_file.write(str(proc.pid))
    print('LoadBalancer started up')
    return proc.pid

def stop():
    if not is_running():
        print('LoadBalancer is stopped')
        return
    if os.path.exists(config['loadbalancer']['dir']):
        with open(os.path.join(config['loadbalancer']['dir'], 'in.pipe'), 'w') as f:
            f.write('stop')
    print('LoadBalancer Stopped')

def restart():
    stop()
    start()

def on_command(argv):
    if len(argv) < 2:
        print('Usage: loadbalancer.py <start|stop|status>')
        exit(1)
    if argv[1] == 'restart':
      restart()
    elif argv[1] == 'start':
        start()
    elif argv[1] == 'stop':
        stop()
    elif argv[1] == 'status':
        if is_running():
            print('LoadBalancer is running')
        else:
            print('LoadBalancer is stopped')
    elif argv[1] == 'send_depricated':
        if len(argv) < 3:
            print('No message specified')
            return
        open(config['in_pipe'], 'w').write(argv[2])
    else:
        print('Unknow command. Usage: loadbalancerctl.py <start|stop|restart|status>')

if __name__ == "__main__":
    if os.getuid() != 0:
        print('This command needs super user privileges')
        exit(1)
    else:
        on_command(sys.argv)
