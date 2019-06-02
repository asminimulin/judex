#!/usr/bin/python3
import os
import sys
import subprocess
import shutil
import logger
from configparser import ConfigParser
from common import *
import time
import connector

config = ConfigParser()
config_path = os.path.join(JUDEX_HOME, 'conf.d', 'judex.conf')
config.read(config_path)
config = config['load_balancer']

logger = logger.Log('LoadBalncerCtl')

def is_running():
    return os.path.exists(config['pid_file'])

def create_file(path, directory=False, fifo=False, text_file=False):
    if directory + fifo + text_file != 1:
        raise Exception('create_file expect that only one option is True')
    if not os.path.exists(path):
        if directory:
            os.mkdir(path)
        elif fifo:
            os.mkfifo(path)
        else:
            open(path, 'w').close()

def start():
    try :
        if is_running():
            return
        create_file(config['dir'], directory=True)
        print('Created ' + config['dir'])
        create_file(config['pid_file'], text_file=True)
        print('Created ' + config['pid_file'])
        child = subprocess.Popen(
                            ['python3', os.path.join(JUDEX_HOME, 'Testing', 'loadbalancer.py')],
                            stderr=open(os.path.join(JUDEX_HOME, 'Testing', 'loadbalancer.error'), 'w'),
                            stdout=open(os.path.join(JUDEX_HOME, 'Testing', 'loadbalancer.output'), 'w'),
                            )
        time.sleep(2)
        conn = connector.ParentConnector(config['out_pipe'], config['in_pipe'])
        with open(config['pid_file'], 'w') as pid_file:
            pid_file.write(str(child.pid))
        return True
    except:
        logger.write('Failed to run LoadBalancer')
        return False

def stop():
    logger.write('Try to stop')
    if not is_running():
        return False
    if os.path.exists(config['dir']):
        with open(os.path.join(config['dir'], 'in.pipe'), 'w') as f:
            f.write('stop')
        shutil.rmtree(config['dir'])
        logger.write('directory cleaned up')
    return True

def restart():
    if stop():
        print('Stopped')
    if start():
        print('Started')

def on_command(argv):
    if len(argv) < 2:
        print('Usage: loadbalancer.py <start|stop|status>')
        exit(1)
    logger.write('have command: ' + argv[1])
    if argv[1] == 'restart':
      restart()
    elif argv[1] == 'start':
        if is_running():
            print('LoadBalancer is already running')
        else:
            if start():
                print('LoadBalancer started')
            else:
                print('Failed to start LoadBalancer')
    elif argv[1] == 'stop':
        if not is_running():
            print('LoadBalancer has already stopped')
        else:
            if stop():
                print('LoadBalancer stopped')
            else:
                print('Failed to stop LoadBalancer')
    elif argv[1] == 'status':
        if is_running():
            print('LoadBalancer is running')
        else:
            print('LoadBalancer is not running')
    elif argv[1] == 'send':
        if len(argv) < 3:
            print('No message specified')
            return
        open(config['in_pipe'], 'w').write(argv[2])
    else:
        print('Unknow command. Usage: {} <start|stop|status>'.format(argv[0]))

if __name__ == "__main__":
    if os.getuid() != 0:
        print('This command needs super user privileges')
        exit(1)
    else:
        on_command(sys.argv)
