#!/usr/bin/python3

import pymysql
import configparser
import os
import subprocess
import random
import time
import socket
import shutil

from common import *
import logger
 
LOAD_BALANCER_SYNC_NON_BLOCKING_DELAY = 0.2

class LoadBalancer:
    def __get_db_connector(self):
        return pymysql.connect(
                    self.config['database']['host'],
                    self.config['database']['user'],
                    self.config['database']['password'],
                    self.config['database']['dbname'])

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
        # Creating socket
        self.uds = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
        self.uds.bind(self.config['judexd']['socket'])
        self.uds.listen()
        # Logging
        self.logger = logger.Logger('judexd')
        # Testers
        self.testers = []

    def stop(self):
        self.logger.log('Stopping testers...')
        for client in self.testers:
            client.send('stop')
        os.remove(self.config['judexd']['pid_file'])
        self.uds.close()
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
        print('has submission {}'.format(submission))
        return
        random.choice(self.testers).send('test {} {} {}'.format(submission['id'], submission['problem_id'], submission['language']))

    def run(self):
        conn, addr = self.uds.accept()
        while True:
            message = conn.recv(1024)
            if message:
                self.__process_message(message)
            elif self.has_submission():
                self.check_next_submission()
            else:
                time.sleep(0.3)

    def __process_message(self, message):
        self.logger.log('Got message <{}>'.format(message))
        if message == b'add-tester':
            pass
        elif message == b'stop':
            self.stop()

    def add_tester(self, tester_id, tester_type='custom_tester.py'):
        tester_dir = os.path.join(self.config['testing']['testers_dir'], '{}'.format(tester_id))
        tester_in = os.path.join(tester_dir, 'in.pipe')
        tester_out = os.path.join(tester_dir, 'out.pipe')
        tester_path = os.path.join(JUDEX_HOME, 'Testing', tester_type)
        subprocess.Popen(['python3', tester_path, str(tester_id)])

        time.sleep(2)

        # We must create connector. If we dont then loadbalancer.py would wait for opening fifos in it's __init__ function.
        # Fix that behaviour is nice issue
        conn = connector.ParentConnector(tester_out, tester_in)
        self.testers.append(conn)

if __name__ == "__main__":
    lb = LoadBalancer()
    lb.run()