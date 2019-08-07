#!/usr/bin/python3

# Builtin imports
import os
import configparser
import threading
import json
import socket
import queue
import time

import logger

class BaseTester:
    ''' Do not create instance of this class. It is only available for inheritance and overrides. '''
    def __init__(self):
        self.id = os.getpid()
        self.config = configparser.ConfigParser()
        self.config.read('/etc/judex/judex.conf')
        self.dir = os.path.join(self.config['judexd']['testers'], str(self.id))
        os.mkdir(self.dir)
        self.logger = logger.Logger('BaseTester')
        self.submission = None
        self.testing_queue = queue.Queue(128)
        self.testing_thread = threading.Thread(target=self.__test, daemon=True)
        self.socket = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
        self.socket.connect(self.config['judexd']['socket'])

    def run(self):
        self.logger.log('Started')
        self.testing_thread.start()

        while True:
            message = str(self.socket.recv(1024).decode('utf-8'))
            request = dict(json.loads(message))
            print(request)

            if request['method'] == 'test':
                print('Base tester caught "test"')
                self.testing_queue.put(request['submission'])
            elif request['method'] == 'stop':
                self.logger.log('stopped')
                exit(0)
            else:
                print('Unhandled message')
                self.logger.log('Unhandled message: {}'.format(message))

    def __test(self):
        while True:
            if not self.testing_queue.empty():
                self.test(self.testing_queue.get())
                time.sleep(0.1)

    def test(self, submission):
        ''' Every nested class must implement this function to basically testing  submissions. '''
        raise NotImplementedError('test')
