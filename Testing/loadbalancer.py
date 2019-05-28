import pymysql
import configparser
import os
import sys
import logger
import subprocess
import random
import connector
from common import *
 
class LoadBalancer:

    def __init_testers(self, testers_count):
        assert(testers_count > 0)
        self.testers = []
        for id in range(testers_count):
            pass

    def __init_config(self):
        self.config = configparser.ConfigParser()
        self.config.read(os.path.join(JUDEX_HOME, 'conf.d/judex.conf'))

    def __init__(self, testers_count=1):
        self.__init_config()
        self.logger = logger.Log('LoadBalancer')
        self.db_connector = pymysql.connect(self.config['mysql']['host'], self.config['mysql']['user'], self.config['mysql']['password'], self.config['mysql']['dbname'])
        self.__init_testers(testers_count)
        self.connector = connector.ChildConnector(self.config['load_balancer']['in_pipe'], self.config['load_balancer']['out_pipe'])
        self.logger.write('LoadBalancer created with {} testers'.format(testers_count))

    def has_submission(self):
        cursor = self.db_connector.cursor()
        sql = 'SELECT COUNT(*) FROM queue'
        cursor.execute(sql)
        result = cursor.fetchone()
        count = int(result[0])
        return count > 0

    def get_next_submission(self):
        cursor = self.db_connector.cursor()
        sql = 'SELECT * FROM queue LIMIT 1'
        cursor.execute(sql)
        result = cursor.fetchone()
        sql = 'DELETE FROM queue WHERE id=%d' % result[0]
        cursor.execute(sql)
        self.db_connector.commit()
        self.logger.write('Got new submission: ' + str(result))
        return result

    def check_next_submission(self):
        submission = self.get_next_submission()
        if len(self.testers):
            random.choice(self.testers).send_message("check")

    def run(self):
        self.logger.write('LoadBalancer is now working')
        while True:
            if self.connector.has_message():
                self.process_command(self.connector.get_message())
            elif self.has_submission():
                self.check_next_submission()

    def process_command(self, command):
        if command == 'add-tester':
            pass
        else:
            self.logger.write('Unrecognized command: ' + command)

    def add_tester(self, id):
        # TODO fix that function
        tester_dir = os.path.join(self.config['load_balancer']['dir'], 'testers', str(id))
        tester_in = os.path.join(tester_dir, 'in.pipe')
        tester_out = os.path.join(tester_dir, 'out.pipe')
        tester_path = os.path.join(JUDEX_HOME, 'Testing', 'loop.py')
        subprocess.Popen(['python3', tester_path, id], close_fds=True)
        print('process created')
        # conn = connector.Connector(tester_out, tester_in)
        # self.testers.append(conn)

if __name__ == "__main__":
    lb = LoadBalancer()
    lb.run()
    # print('loadbalancer.py must not be called manually')
