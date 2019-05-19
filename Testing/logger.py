import configparser
import os

class Log:
    def __init__(self, who):
        cfg = configparser.ConfigParser()
        cfg.read(os.path.join(os.environ.get('JUDEX_HOME'), 'conf.d/judex.conf'))
        self.path = cfg['log']['path']
        self.who = who

    def write(self, message):
        with open(self.path, 'a') as file:
            file.write(self.who + ' ' + message + '\n')
