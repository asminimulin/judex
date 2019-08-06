#!/usr/bin/python3

import pymysql
import configparser
import os
import subprocess
import random
import time
import socket
import shutil
import threading
import json
import asyncio

import logger


class LoadBalancer():

    def __init__(self):
        self.pid = os.getpid()
        # Reading config
        self.config = configparser.ConfigParser()
        self.config.read('/etc/judex/judex.conf')
        # Filesystem
        if os.path.exists(self.config['global']['runtime']):
            shutil.rmtree(self.config['global']['runtime'])
        os.mkdir(self.config['global']['runtime'])
        with open(self.config['judexd']['pid_file'], 'w') as pid_file:
            pid_file.write(str(self.pid))
        os.mkdir(self.config['judexd']['testers'])
        # Creating socket
        self.sock = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
        self.sock.bind(self.config['judexd']['socket'])
        self.sock.listen()
        # self.sock.settimeout(0.2)
        # Logging
        self.logger = logger.Logger('judexd')
        # Testers
        self.testers = []
        # Message thread
        self.message_thread = threading.Thread(target=self.receive_message)
        # Thread requirements
        self.lock = threading.Lock()
        self.exit_event = threading.Event()

    def __get_db_connector(self):
        return pymysql.connect(
                    self.config['database']['host'],
                    self.config['database']['user'],
                    self.config['database']['password'],
                    self.config['database']['dbname'])

    def receive_message(self):
        while True:
            client, addr = None, None
            try:
                client, addr = self.sock.accept()
                message = str(client.recv(1024).decode('utf-8'))
                self.handle_message(message)
            except socket.timeout:
                pass

    def handle_message(self, message: str):
        self.logger.log('Got message <{}>'.format(message))
        print('Got message <{}>'.format(message))
        request = dict(json.loads(message))
        if request['method'] == 'add-tester':
            print('Add-tester')
            self.add_tester()
        elif request['method'] == 'stop':
            print('Stop')
            self.stop()
        else:
            print('Unknown message')


    def stop(self):
        print('Stopping')
        with self.lock:
            self.exit_event.set()
            self.sock.close()
        self.logger.log('Stopping testers...')
        stop_msg = json.dumps({'method': 'stop'})
        for client in self.testers:
            client.send(stop_msg.encode('utf-8'))
        shutil.rmtree(self.config['global']['runtime'])
        self.logger.log('Stopped')
        exit(0)

    def has_submission(self):
        db = self.__get_db_connector()
        cursor = db.cursor()
        sql = 'SELECT COUNT(*) FROM testing_queue'
        cursor.execute(sql)
        result = cursor.fetchone()
        db.close()
        count = int(result[0])
        return count > 0

    def get_next_submission(self):
        db = self.__get_db_connector()
        cursor = db.cursor(pymysql.cursors.DictCursor)
        sql = 'SELECT * FROM testing_queue LIMIT 1'
        cursor.execute(sql)
        result = cursor.fetchone()
        sql = 'DELETE FROM testing_queue WHERE id={}'.format(result['id'])
        cursor.execute(sql)
        db.commit()
        db.close()
        self.logger.log('Got new submission: {}'.format(result))
        return result

    def check_next_submission(self):
        submission = self.get_next_submission()
        query = json.dumps({
            'method': 'test',
            'submission': submission
        })
        if (len(self.testers) == 0):
            print('No available tetsters for this submission: ', submission)
        else:
            random.choice(self.testers).send(query.encode('utf-8'))

    def run(self):
        self.logger.log('Started')
        self.message_thread.start()
        while True:
            with self.lock:
                if self.exit_event.is_set():
                    break
            if self.has_submission():
                self.check_next_submission()
        self.message_thread.join()

    def add_tester(self, tester_type='custom_tester.py'):
        executable = os.path.join(self.config['global']['src'], 'Testing/custom_tester.py')
        proc = subprocess.Popen(executable)
        print('subprocess created')
        tester, addr  = self.sock.accept()
        print('Tester created')
        self.testers.append(tester)

if __name__ == "__main__":
    thread = LoadBalancer()
    thread.run()
