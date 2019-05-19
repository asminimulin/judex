import pymysql
import configparser
import os
import sys
import logger

class LoadBalancer:
    def __init__(self):
        self.config = configparser.ConfigParser()
        self.config.read(os.path.join(os.environ.get('JUDEX_HOME'), 'conf.d/judex.conf'))
        self.db_connector = pymysql.connect(self.config['mysql']['host'], self.config['mysql']['user'], self.config['mysql']['password'], self.config['mysql']['dbname'])
        self.logger = logger.Log('LoadBalancer')

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
        print(sql)
        cursor.execute(sql)
        self.db_connector.commit()
        return result

    def check_next_submission(self):
        submission = self.get_next_submission()
        print('Checking {} submission'.format(submission[0]))

    def run(self):
        f = open(self.config['load_balancer']['main_pipe'], 'r')
        while True:
            line = f.readline()
            if line:
                self.logger.write('Command: ' + line)
            else:
                if self.has_submission():
                    self.check_next_submission()

if __name__ == "__main__":
    lb = LoadBalancer()
    lb.run()