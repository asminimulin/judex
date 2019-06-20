import pymysql
import configparser
import os
import subprocess
import random
import time

from common import *
import logger
import connector
 
LOAD_BALANCER_SYNC_NON_BLOCKING_DELAY = 0.2

class LoadBalancer:

    def __get_db_connector(self):
        return pymysql.connect(self.config['mysql']['host'], self.config['mysql']['user'], self.config['mysql']['password'], self.config['mysql']['dbname'])

    def __init_testers(self, testers_count):
        assert(testers_count > 0)
        self.testers = []
        for tester_id in range(testers_count):
            self.add_tester(tester_id)

    def __init_config(self):
        self.config = configparser.ConfigParser(os.environ)
        self.config.read(os.path.join(JUDEX_HOME, 'conf.d', 'judex.conf'))

    def __init_fs(self):
        os.mkdir(self.testers_dir)

    def __init__(self, testers_count=1):
        self.__init_config()
        self.testers_dir = self.config['testing']['testers_dir']
        self.__init_fs()
        self.logger = logger.Logger('LoadBalancer')
        self.connector = connector.ChildConnector(self.config['loadbalancer']['in_pipe'], self.config['loadbalancer']['out_pipe'])
        self.__init_testers(testers_count)
        self.logger.log('Created with {} testers'.format(testers_count))

    def stop(self):
        self.logger.log('stopping testers...')
        for conn in self.testers:
            conn.send_message('stop')
        self.logger.log('stopped')
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
        random.choice(self.testers).send_message('test {} {} {}'.format(submission['id'], submission['problem_id'], submission['language']))

    def run(self):
        self.logger.log('LoadBalancer event loop started')
        while True:
            if self.connector.has_message():
                self.__process_command(self.connector.get_message())
            elif self.has_submission():
                self.check_next_submission()
            else:
                time.sleep(LOAD_BALANCER_SYNC_NON_BLOCKING_DELAY)

    def __process_command(self, message):
        self.logger.log('Got message <{}>'.format(message))
        if message == 'add-tester':
            pass
        elif message == 'stop':
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
    lb = LoadBalancer(1)
    lb.run()
