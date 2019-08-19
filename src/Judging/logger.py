#!/usr/bin/python3

# Builtin imports
import configparser
import os
import datetime

class Logger:

    def __init__(self, who):
        cfg = configparser.ConfigParser()
        cfg.read('/etc/judex/judex.conf')
        self.path = cfg['log']['file']
        if not os.path.exists(cfg['log']['file']):
            open(cfg['log']['file'], 'w').close()
        self.who = who

    def log(self, message):
        with open(self.path, 'a') as file:
            file.write('[' + str(datetime.datetime.now()) + ']' + '  ' + self.who + ' ' + message + '\n')
