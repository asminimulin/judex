#!/usr/bin/python3
import os
import sys
import subprocess
from configparser import ConfigParser


config = ConfigParser()
base_dir = os.environ.get('JUDEX_HOME')
config_path = os.path.join(base_dir, 'conf.d/judex.conf')
config.read(config_path)
config = config['load_balancer']

def is_running():
    return os.path.exists(config['pid_file'])

def start():
    try :
        if is_running():
            return
        if not os.path.exists(config['dir']):
            os.mkdir(config['dir'])
        if not os.path.exists(config['main_pipe']):
            os.mkfifo(config['main_pipe'])
        child = subprocess.Popen(['python3', os.path.join(base_dir, 'Testing/lbd.py')])
        print('path = ', config['pid_file'])
        with open(config['pid_file'], 'w') as pid_file:
            pid_file.write(str(child.pid))
        return True
    except:
        return False

def stop():
    if not is_running():
        return False
    if os.path.exists(config['pid_file']):
        pid = int(open(config['pid_file'], 'r').readline())
        os.system("kill -9 {}".format(pid))
        os.remove(config['pid_file'])
        return True
    return False

def on_command(argv):
    if len(argv) < 2:
        print('Usage: loadbalancer.py <start|stop|status>')
        exit(1)
    if argv[1] == 'start':
        if is_running():
            print('LoadBalancer is running')
        else:
            if start():
                print('LoadBalancer started')
            else:
                print('Failed to start LoadBalancer')
    elif argv[1] == 'stop':
        if not is_running():
            print('LoadBalancer is not running')
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
        open(config['main_pipe'], 'w').write(argv[2])
    else:
        print('Unknow command. Usage: {} <start|stop|status>'.format(argv[0]))

if __name__ == "__main__":
    if os.getuid() != 0:
        print('This command needs super user privileges')
        exit(1)
    else:
        on_command(sys.argv)
