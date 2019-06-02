#!/usr/bin/python3
import os
import time
import logger
import connector
import configparser
import sys

BASE_TESTER_SYNC_NON_BLOCKING_DELAY = 0.1

class BaseTester:

    def __init_config(self):
        ''' This function moved outside __init__ because it probably will be changed dramatically in further '''
        path = os.path.join(os.getenv('JUDEX_HOME'), 'conf.d', 'judex.conf')
        self.config = configparser.ConfigParser()
        self.config.read(path)

    def __init_fs(self):
        self.dir = os.path.join(self.config['testing']['testers_dir'], str(self.id))
        os.mkdir(self.dir)

    def __init__(self, tester_id):
        print('base init')
        self.id = tester_id
        self.logger = logger.Log('BaseTester')
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
        self.logger.write('Message processing. Message=<' + message + '>')
        print('Message processing. Message=<' + message + '>')
        argv = message.split()
        if argv[0] == 'test':
            if len(argv) < 3:
                self.logger.write('Error occured. Command test must have at least 2 arguments')
                return False
            self.test(argv[1], argv[2])
        elif argv[0] == 'stop':
            exit(0)
        else:
            self.logger.write('Unknown command: ' + argv[0])
            return False
        return True

    def test(self, submission_id, problem_id):
        ''' Every nested class must implement this fucntion to basically testing  submissions. '''
        raise NotImplementedError('test')

    def emit(self, message):
        self.connector.send_message(message + '\n')

def main():
    if len(sys.argv) >= 2:
        try:
            tester_id = int(sys.argv[1])
            tester = BaseTester(tester_id)
            tester.run()
        except:
            print('Failed')
    else:
        print('Expected int value in argument 1')

if __name__ == '__main__':
    main()
