#!/usr/bin/python3

# Builtin imports
import os
import time
import configparser
import sys

# Package imports
from common import *
import connector 

BASE_TESTER_SYNC_NON_BLOCKING_DELAY = 0.2

class BaseTester:

    ''' Do not create instance of this class. It is only available for inheritance and overrides. '''

    def __init_config(self):
        path = os.path.join(JUDEX_HOME, 'conf.d', 'judex.conf')
        self.config = configparser.ConfigParser()
        self.config.read(path)

    def __init_fs(self):
        self.dir = os.path.join(self.config['testing']['testers_dir'], str(self.id))
        os.mkdir(self.dir)

    def __init__(self, tester_id):
        self.id = tester_id
        self.__init_config()
        self.__init_fs()
        self.connector = connector.ChildConnector(
                            os.path.join(self.dir, 'in.pipe'),
                            os.path.join(self.dir, 'out.pipe') )
        self.submission = None

    # Non-blocking, Syncronized
    def run(self):
        while True:
            if self.connector.has_message():
                self.process_message(self.connector.get_message())
            else:
                time.sleep(BASE_TESTER_SYNC_NON_BLOCKING_DELAY)

    def process_message(self, message):
        self.logger.log('Message processing. Message=<' + message + '>')
        argv = message.split()
        if argv[0] == 'test':
            if len(argv) < 4:
                self.logger.log('Error occured. Command test must have at least 3 arguments')
                return
            self.test(argv[1], argv[2], argv[3])
        elif argv[0] == 'stop':
            exit(0)

    def test(self, submission_id, problem_id, language):
        ''' Every nested class must implement this fucntion to basically testing  submissions. '''
        raise NotImplementedError('test')

    def emit(self, message):
        self.connector.send_message(message)
